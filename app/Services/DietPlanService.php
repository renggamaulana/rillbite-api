<?php

namespace App\Services;

use App\Models\DietPlan;
use App\Models\User;

class DietPlanService
{
    public function getWeeklyPlan(User $user, int $weekNumber): array
    {
        $entries = DietPlan::with('recipe')
            ->where('user_id', $user->id)
            ->where('week_number', $weekNumber)
            ->get();

        $plan = [];

        foreach ($entries as $entry) {
            $plan[$entry->day_of_week][$entry->meal_type] = [
                'id'     => $entry->id,
                'recipe' => $this->formatRecipe($entry->recipe),
            ];
        }

        return ['week' => $weekNumber, 'plan' => $plan];
    }

    public function store(User $user, array $data): array
    {
        $weekNumber = $data['week_number'] ?? 1;

        // Remove existing entry for this slot (one recipe per slot)
        DietPlan::where('user_id', $user->id)
            ->where('day_of_week', $data['day_of_week'])
            ->where('meal_type', $data['meal_type'])
            ->where('week_number', $weekNumber)
            ->delete();

        $entry = DietPlan::create([
            'user_id'     => $user->id,
            'name'        => 'My Diet Plan',
            'day_of_week' => $data['day_of_week'],
            'meal_type'   => $data['meal_type'],
            'recipe_id'   => $data['recipe_id'],
            'week_number' => $weekNumber,
        ]);

        $entry->load('recipe');

        return [
            'id'        => $entry->id,
            'day'       => $entry->day_of_week,
            'meal_type' => $entry->meal_type,
            'recipe'    => $this->formatRecipe($entry->recipe),
        ];
    }

    public function destroy(User $user, int $id): bool
    {
        $entry = DietPlan::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return $entry->delete();
    }

    public function clear(User $user, int $weekNumber): int
    {
        return DietPlan::where('user_id', $user->id)
            ->where('week_number', $weekNumber)
            ->delete();
    }

    private function formatRecipe(?object $recipe): ?array
    {
        if (!$recipe) return null;

        return [
            'id'             => $recipe->id,
            'title'          => $recipe->title,
            'image'          => $recipe->image,
            'readyInMinutes' => $recipe->ready_in_minutes,
            'servings'       => $recipe->servings,
        ];
    }
}
