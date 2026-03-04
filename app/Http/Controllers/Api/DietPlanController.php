<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DietPlan;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class DietPlanController extends Controller
{
    /**
     * GET /api/diet-plans
     * Return the user's diet plan for a given week, structured by day and meal type.
     */
    public function index(Request $request): JsonResponse
    {
        $weekNumber = $request->query('week', 1);
        $user = Auth::user();

        $entries = DietPlan::with('recipe')
            ->where('user_id', $user->id)
            ->where('week_number', $weekNumber)
            ->get();

        // Build a nested plan: plan[day][mealType] = { id, recipe }
        $plan = [];

        foreach ($entries as $entry) {
            $day      = $entry->day_of_week;
            $mealType = $entry->meal_type;

            $plan[$day][$mealType] = [
                'id'     => $entry->id,
                'recipe' => $entry->recipe ? [
                    'id'             => $entry->recipe->id,
                    'title'          => $entry->recipe->title,
                    'image'          => $entry->recipe->image,
                    'readyInMinutes' => $entry->recipe->ready_in_minutes,
                    'servings'       => $entry->recipe->servings,
                ] : null,
            ];
        }

        return response()->json([
            'week'   => $weekNumber,
            'plan'   => $plan,
        ]);
    }

    /**
     * POST /api/diet-plans
     * Add (or replace) a recipe in a specific day+mealType slot.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipe_id'   => 'required|integer|exists:recipes,id',
            'day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'meal_type'   => 'required|string|in:breakfast,lunch,dinner',
            'week_number' => 'sometimes|integer|min:1',
        ]);

        $user       = Auth::user();
        $weekNumber = $validated['week_number'] ?? 1;

        // Remove any existing entry for this slot (one recipe per slot)
        DietPlan::where('user_id', $user->id)
            ->where('day_of_week', $validated['day_of_week'])
            ->where('meal_type', $validated['meal_type'])
            ->where('week_number', $weekNumber)
            ->delete();

        $entry = DietPlan::create([
            'user_id'     => $user->id,
            'name'        => 'My Diet Plan',
            'day_of_week' => $validated['day_of_week'],
            'meal_type'   => $validated['meal_type'],
            'recipe_id'   => $validated['recipe_id'],
            'week_number' => $weekNumber,
        ]);

        $entry->load('recipe');

        return response()->json([
            'message' => 'Recipe added to diet plan.',
            'data'    => [
                'id'        => $entry->id,
                'day'       => $entry->day_of_week,
                'meal_type' => $entry->meal_type,
                'recipe'    => $entry->recipe ? [
                    'id'             => $entry->recipe->id,
                    'title'          => $entry->recipe->title,
                    'image'          => $entry->recipe->image,
                    'readyInMinutes' => $entry->recipe->ready_in_minutes,
                    'servings'       => $entry->recipe->servings,
                ] : null,
            ],
        ], 201);
    }

    /**
     * DELETE /api/diet-plans/{id}
     * Remove a single meal slot by its diet_plan entry ID.
     */
    public function destroy(int $id): JsonResponse
    {
        $user  = Auth::user();
        $entry = DietPlan::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $entry->delete();

        return response()->json([
            'message' => 'Meal removed from diet plan.',
        ]);
    }

    /**
     * DELETE /api/diet-plans/clear
     * Clear all entries for a given week (defaults to week 1).
     */
    public function clear(Request $request): JsonResponse
    {
        $weekNumber = $request->query('week', 1);
        $user       = Auth::user();

        $deleted = DietPlan::where('user_id', $user->id)
            ->where('week_number', $weekNumber)
            ->delete();

        return response()->json([
            'message' => "Cleared {$deleted} meal(s) from week {$weekNumber}.",
            'deleted' => $deleted,
        ]);
    }
}
