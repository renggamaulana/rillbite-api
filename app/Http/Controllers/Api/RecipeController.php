<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recipe\ComplexSearchRequest;
use App\Http\Requests\Recipe\RandomRecipeRequest;
use App\Services\RecipeService;

class RecipeController extends Controller
{
    public function __construct(
        private RecipeService $recipeService
    ) {}

    /**
     * GET /api/recipes/complexSearch
     */
    public function complexSearch(ComplexSearchRequest $request)
    {
        $result = $this->recipeService->complexSearch($request->validated());

        return response()->json($result, 200);
    }

    /**
     * GET /api/recipes/{id}/information
     */
    public function information(int $id)
    {
        $recipe = $this->recipeService->getInformation($id);

        if (!$recipe) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }

        return response()->json($recipe, 200);
    }

    /**
     * GET /api/recipes/category/{category}
     */
    public function byCategory(string $category)
    {
        $result = $this->recipeService->byCategory($category);

        return response()->json($result, 200);
    }

    /**
     * GET /api/recipes/random
     */
    public function random(RandomRecipeRequest $request)
    {
        $result = $this->recipeService->random($request->validated());

        return response()->json($result, 200);
    }
}
