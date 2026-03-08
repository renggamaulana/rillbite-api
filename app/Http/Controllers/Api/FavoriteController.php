<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(
        private FavoriteService $favoriteService
    ) {}

    /** GET /api/favorites */
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->favoriteService->getAll($request->user())
        );
    }

    /** POST /api/favorites/{recipeId} */
    public function store(Request $request, int $recipeId): JsonResponse
    {
        $result = $this->favoriteService->add($request->user(), $recipeId);

        if (!$result['found']) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }

        return response()->json([
            'message'      => $result['already'] ? 'Recipe already in favorites' : 'Recipe added to favorites',
            'is_favorited' => $result['is_favorited'],
        ], $result['already'] ? 200 : 201);
    }

    /** DELETE /api/favorites/{recipeId} */
    public function destroy(Request $request, int $recipeId): JsonResponse
    {
        $result = $this->favoriteService->remove($request->user(), $recipeId);

        if (!$result['found']) {
            return response()->json(['message' => 'Recipe not in favorites'], 404);
        }

        return response()->json([
            'message'      => 'Recipe removed from favorites',
            'is_favorited' => $result['is_favorited'],
        ]);
    }

    /** GET /api/favorites/check/{recipeId} */
    public function check(Request $request, int $recipeId): JsonResponse
    {
        return response()->json([
            'is_favorited' => $this->favoriteService->check($request->user(), $recipeId),
        ]);
    }

    /** POST /api/favorites/toggle/{recipeId} */
    public function toggle(Request $request, int $recipeId): JsonResponse
    {
        $result = $this->favoriteService->toggle($request->user(), $recipeId);

        if (!$result['found']) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }

        return response()->json([
            'message'      => $result['message'],
            'is_favorited' => $result['is_favorited'],
        ]);
    }
}
