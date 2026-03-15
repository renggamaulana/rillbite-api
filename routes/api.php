<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\NutritionController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\DietPlanController;
use App\Http\Controllers\Api\UserRecipeController;
use Illuminate\Http\Request;

Route::prefix('')->group(function () {

    // =========================================================================
    // Public – Auth
    // =========================================================================
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);
    });

    // =========================================================================
    // Public – Recipes
    // =========================================================================
    Route::prefix('recipes')->group(function () {
        Route::get('/complexSearch',      [RecipeController::class, 'complexSearch']);
        Route::get('/random',             [RecipeController::class, 'random']);
        Route::get('/category/{category}',[RecipeController::class, 'byCategory']);

        // Detail & nutrition (public read)
        Route::get('/{id}/information',  [RecipeController::class, 'information']);
        Route::get('/{id}/nutrition',    [NutritionController::class, 'show']);
    });

    // =========================================================================
    // Protected routes
    // =========================================================================
    Route::middleware('auth:sanctum')->group(function () {

        // Auth management
        Route::prefix('auth')->group(function () {
            Route::post('/logout',          [AuthController::class, 'logout']);
            Route::get('/user',             [AuthController::class, 'user']);
            Route::put('/update-profile',   [AuthController::class, 'updateProfile']);
        });

        // Shorthand /user
        Route::get('/user', fn(Request $request) => $request->user());

        // Admin – Manage recipes (CRUD + nutrition)
        Route::middleware('admin')->group(function () {
            Route::prefix('user-recipes')->group(function () {
                Route::get('/',       [UserRecipeController::class, 'index']);
                Route::post('/',      [UserRecipeController::class, 'store']);
                Route::get('/{id}',   [UserRecipeController::class, 'show']);
                Route::put('/{id}',   [UserRecipeController::class, 'update']);
                Route::delete('/{id}',[UserRecipeController::class, 'destroy']);
            });

            // Admin – Nutrition management
            Route::prefix('recipes')->group(function () {
                Route::put('/{id}/nutrition',    [NutritionController::class, 'upsert']);
                Route::delete('/{id}/nutrition', [NutritionController::class, 'destroy']);
            });
        });

        // Favorites
        Route::prefix('favorites')->group(function () {
            Route::get('/',                     [FavoriteController::class, 'index']);
            Route::get('/check/{recipeId}',     [FavoriteController::class, 'check']);
            Route::post('/{recipeId}',          [FavoriteController::class, 'store']);
            Route::delete('/{recipeId}',        [FavoriteController::class, 'destroy']);
            Route::post('/toggle/{recipeId}',   [FavoriteController::class, 'toggle']);
        });

        // Diet Plans
        Route::prefix('diet-plans')->group(function () {
            Route::get('/',              [DietPlanController::class, 'index']);
            Route::post('/',             [DietPlanController::class, 'store']);
            Route::delete('/clear',      [DietPlanController::class, 'clear']);
            Route::delete('/{id}',       [DietPlanController::class, 'destroy']);
            Route::get('/nutrition/{day}',[DietPlanController::class, 'dayNutrition']);
        });
    });
});
