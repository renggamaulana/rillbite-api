<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserRecipeController extends Controller
{
    /**
     * Display a listing of recipes (admin only)
     */
    public function index()
    {
        $recipes = Recipe::with(['ingredients', 'nutrition'])->latest()->paginate(20);
        return response()->json($recipes);
    }

    /**
     * Store a newly created recipe (admin only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'image' => 'nullable|url',
            'ready_in_minutes' => 'nullable|integer|min:0',
            'servings' => 'nullable|integer|min:1',
            'health_score' => 'nullable|numeric|min:0|max:100',
            'price_per_serving' => 'nullable|numeric|min:0',
            'instructions' => 'nullable|string',
            'categories' => 'nullable|array',
            'vegetarian' => 'boolean',
            'vegan' => 'boolean',
            'gluten_free' => 'boolean',
            'dairy_free' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $recipe = Recipe::create($request->all());

        return response()->json([
            'message' => 'Recipe created successfully',
            'recipe' => $recipe->load(['ingredients', 'nutrition'])
        ], 201);
    }

    /**
     * Display the specified recipe
     */
    public function show($id)
    {
        $recipe = Recipe::with(['ingredients', 'nutrition'])->find($id);

        if (!$recipe) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }

        return response()->json($recipe);
    }

    /**
     * Update the specified recipe (admin only)
     */
    public function update(Request $request, $id)
    {
        $recipe = Recipe::find($id);

        if (!$recipe) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'summary' => 'nullable|string',
            'image' => 'nullable|url',
            'ready_in_minutes' => 'nullable|integer|min:0',
            'servings' => 'nullable|integer|min:1',
            'health_score' => 'nullable|numeric|min:0|max:100',
            'price_per_serving' => 'nullable|numeric|min:0',
            'instructions' => 'nullable|string',
            'categories' => 'nullable|array',
            'vegetarian' => 'boolean',
            'vegan' => 'boolean',
            'gluten_free' => 'boolean',
            'dairy_free' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $recipe->update($request->all());

        return response()->json([
            'message' => 'Recipe updated successfully',
            'recipe' => $recipe->load(['ingredients', 'nutrition'])
        ]);
    }

    /**
     * Remove the specified recipe (admin only)
     */
    public function destroy($id)
    {
        $recipe = Recipe::find($id);

        if (!$recipe) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }

        $recipe->delete();

        return response()->json(['message' => 'Recipe deleted successfully']);
    }
}
