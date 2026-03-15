<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nutrition\UpsertNutritionRequest;
use App\Models\Recipe;
use App\Services\NutritionService;
use Illuminate\Http\JsonResponse;

class NutritionController extends Controller
{
    public function __construct(
        private NutritionService $nutritionService
    ) {}

    /**
     * GET /api/recipes/{id}/nutrition
     * Public – anyone can view nutrition data.
     */
    public function show(int $recipeId): JsonResponse
    {
        $recipe = Recipe::findOrFail($recipeId);

        $nutrition = $this->nutritionService->getByRecipe($recipe->id);

        if (!$nutrition) {
            return response()->json(['message' => 'Nutrition data not found for this recipe.'], 404);
        }

        return response()->json([
            'recipe_id' => $recipe->id,
            'data'      => $this->nutritionService->format($nutrition),
        ]);
    }

    /**
     * PUT /api/recipes/{id}/nutrition
     * Admin only – create or update nutrition for a recipe.
     */
    public function upsert(UpsertNutritionRequest $request, int $recipeId): JsonResponse
    {
        $recipe    = Recipe::findOrFail($recipeId);
        $nutrition = $this->nutritionService->upsert($recipe->id, $request->validated());

        return response()->json([
            'message'   => 'Nutrition saved successfully.',
            'recipe_id' => $recipe->id,
            'data'      => $this->nutritionService->format($nutrition),
        ]);
    }

    /**
     * DELETE /api/recipes/{id}/nutrition
     * Admin only – remove nutrition record.
     */
    public function destroy(int $recipeId): JsonResponse
    {
        Recipe::findOrFail($recipeId);

        $deleted = $this->nutritionService->deleteByRecipe($recipeId);

        if (!$deleted) {
            return response()->json(['message' => 'No nutrition data found to delete.'], 404);
        }

        return response()->json(['message' => 'Nutrition data deleted successfully.']);
    }
}
