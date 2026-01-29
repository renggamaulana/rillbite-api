<?php

// app/Http/Controllers/Api/FavoriteController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Get user's favorite recipes
     * GET /api/favorites
     */
    public function index(Request $request)
    {
        try {
            $favorites = $request->user()
                ->favoriteRecipes()
                ->with(['ingredients', 'nutrition'])
                ->get();

            return response()->json([
                'favorites' => $favorites->map(function ($recipe) {
                    return [
                        'id' => $recipe->id,
                        'title' => $recipe->title,
                        'image' => $recipe->image,
                        'readyInMinutes' => $recipe->ready_in_minutes,
                        'servings' => $recipe->servings,
                        'healthScore' => $recipe->health_score,
                        'favorited_at' => $recipe->pivot->created_at,
                    ];
                }),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add recipe to favorites
     * POST /api/favorites/{recipeId}
     */
    public function store(Request $request, $recipeId)
    {
        try {
            $recipe = Recipe::find($recipeId);

            if (!$recipe) {
                return response()->json(['message' => 'Recipe not found'], 404);
            }

            $user = $request->user();

            // Check if already favorited
            if ($user->hasFavorited($recipeId)) {
                return response()->json([
                    'message' => 'Recipe already in favorites',
                    'is_favorited' => true,
                ], 200);
            }

            $user->favoriteRecipes()->attach($recipeId);

            return response()->json([
                'message' => 'Recipe added to favorites',
                'is_favorited' => true,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add to favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove recipe from favorites
     * DELETE /api/favorites/{recipeId}
     */
    public function destroy(Request $request, $recipeId)
    {
        try {
            $user = $request->user();

            if (!$user->hasFavorited($recipeId)) {
                return response()->json([
                    'message' => 'Recipe not in favorites',
                ], 404);
            }

            $user->favoriteRecipes()->detach($recipeId);

            return response()->json([
                'message' => 'Recipe removed from favorites',
                'is_favorited' => false,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove from favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if recipe is favorited
     * GET /api/favorites/check/{recipeId}
     */
    public function check(Request $request, $recipeId)
    {
        try {
            $isFavorited = $request->user()->hasFavorited($recipeId);

            return response()->json([
                'is_favorited' => $isFavorited,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to check favorite status',
                'error' => $e->getMessage(),
                'is_favorited' => false,
            ], 500);
        }
    }

    /**
     * Toggle favorite status
     * POST /api/favorites/toggle/{recipeId}
     */
    public function toggle(Request $request, $recipeId)
    {
        try {
            $recipe = Recipe::find($recipeId);

            if (!$recipe) {
                return response()->json(['message' => 'Recipe not found'], 404);
            }

            $user = $request->user();
            $isFavorited = $user->hasFavorited($recipeId);

            if ($isFavorited) {
                $user->favoriteRecipes()->detach($recipeId);
                $message = 'Recipe removed from favorites';
            } else {
                $user->favoriteRecipes()->attach($recipeId);
                $message = 'Recipe added to favorites';
            }

            return response()->json([
                'message' => $message,
                'is_favorited' => !$isFavorited,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle favorite',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
