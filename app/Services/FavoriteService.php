<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\User;

class FavoriteService
{
    public function getAll(User $user): array
    {
        $favorites = $user->favoriteRecipes()
            ->with(['ingredients', 'nutrition'])
            ->get();

        return [
            'favorites' => $favorites->map(fn($recipe) => [
                'id'             => $recipe->id,
                'title'          => $recipe->title,
                'image'          => $recipe->image,
                'readyInMinutes' => $recipe->ready_in_minutes,
                'servings'       => $recipe->servings,
                'healthScore'    => $recipe->health_score,
                'favorited_at'   => $recipe->pivot->created_at,
            ])->values(),
        ];
    }

    public function add(User $user, int $recipeId): array
    {
        if (!Recipe::find($recipeId)) {
            return ['found' => false];
        }

        if ($user->hasFavorited($recipeId)) {
            return ['found' => true, 'already' => true, 'is_favorited' => true];
        }

        $user->favoriteRecipes()->attach($recipeId);

        return ['found' => true, 'already' => false, 'is_favorited' => true];
    }

    public function remove(User $user, int $recipeId): array
    {
        if (!$user->hasFavorited($recipeId)) {
            return ['found' => false];
        }

        $user->favoriteRecipes()->detach($recipeId);

        return ['found' => true, 'is_favorited' => false];
    }

    public function check(User $user, int $recipeId): bool
    {
        return $user->hasFavorited($recipeId);
    }

    public function toggle(User $user, int $recipeId): array
    {
        if (!Recipe::find($recipeId)) {
            return ['found' => false];
        }

        $isFavorited = $user->hasFavorited($recipeId);

        if ($isFavorited) {
            $user->favoriteRecipes()->detach($recipeId);
        } else {
            $user->favoriteRecipes()->attach($recipeId);
        }

        return [
            'found'       => true,
            'is_favorited' => !$isFavorited,
            'message'     => $isFavorited
                ? 'Recipe removed from favorites'
                : 'Recipe added to favorites',
        ];
    }
}
