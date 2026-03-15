<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Services\NutritionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserRecipeController extends Controller
{
    public function __construct(
        private NutritionService $nutritionService
    ) {}

    // =========================================================================
    // Rules
    // =========================================================================

    private function recipeRules(bool $isUpdate = false): array
    {
        $sometimes = $isUpdate ? 'sometimes|' : '';

        return [
            'title'            => "{$sometimes}required|string|max:255",
            'summary'          => 'nullable|string',
            'image'            => 'nullable|url|max:500',
            'ready_in_minutes' => 'nullable|integer|min:0',
            'servings'         => 'nullable|integer|min:1',
            'health_score'     => 'nullable|numeric|min:0|max:100',
            'price_per_serving'=> 'nullable|numeric|min:0',
            'instructions'     => 'nullable|string',
            'categories'       => 'nullable|array',
            'categories.*'     => 'string|max:100',
            'vegetarian'       => 'boolean',
            'vegan'            => 'boolean',
            'gluten_free'      => 'boolean',
            'dairy_free'       => 'boolean',

            // Nutrition (optional block)
            'nutrition'                  => 'nullable|array',
            'nutrition.calories'         => 'required_with:nutrition|numeric|min:0',
            'nutrition.protein'          => 'required_with:nutrition|numeric|min:0',
            'nutrition.fat'              => 'required_with:nutrition|numeric|min:0',
            'nutrition.carbohydrates'    => 'required_with:nutrition|numeric|min:0',
        ];
    }

    // =========================================================================
    // CRUD
    // =========================================================================

    /**
     * GET /api/user-recipes
     */
    public function index(): JsonResponse
    {
        $recipes = Recipe::with(['nutrition'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($r) => $this->formatRecipe($r));

        return response()->json(['data' => $recipes]);
    }

    /**
     * POST /api/user-recipes
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->recipeRules());

        $recipe = DB::transaction(function () use ($validated) {
            $recipe = Recipe::create($this->extractRecipeData($validated));

            if (!empty($validated['nutrition'])) {
                $this->nutritionService->upsert($recipe->id, $validated['nutrition']);
            }

            return $recipe->load('nutrition');
        });

        return response()->json([
            'message' => 'Recipe created successfully.',
            'data'    => $this->formatRecipe($recipe),
        ], 201);
    }

    /**
     * GET /api/user-recipes/{id}
     */
    public function show(int $id): JsonResponse
    {
        $recipe = Recipe::with(['ingredients', 'nutrition'])->findOrFail($id);

        return response()->json(['data' => $this->formatRecipe($recipe, detailed: true)]);
    }

    /**
     * PUT /api/user-recipes/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $recipe    = Recipe::findOrFail($id);
        $validated = $request->validate($this->recipeRules(isUpdate: true));

        $recipe = DB::transaction(function () use ($recipe, $validated) {
            $recipe->update($this->extractRecipeData($validated));

            if (array_key_exists('nutrition', $validated)) {
                if ($validated['nutrition'] !== null) {
                    $this->nutritionService->upsert($recipe->id, $validated['nutrition']);
                } else {
                    $this->nutritionService->deleteByRecipe($recipe->id);
                }
            }

            return $recipe->load('nutrition');
        });

        return response()->json([
            'message' => 'Recipe updated successfully.',
            'data'    => $this->formatRecipe($recipe),
        ]);
    }

    /**
     * DELETE /api/user-recipes/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $recipe = Recipe::findOrFail($id);

        DB::transaction(function () use ($recipe) {
            // Nutrition cascades via DB (cascadeOnDelete), but explicit for clarity
            $this->nutritionService->deleteByRecipe($recipe->id);
            $recipe->delete();
        });

        return response()->json(['message' => 'Recipe deleted successfully.']);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private function extractRecipeData(array $validated): array
    {
        return collect($validated)
            ->except(['nutrition'])
            ->toArray();
    }

    private function formatRecipe(Recipe $recipe, bool $detailed = false): array
    {
        $base = [
            'id'               => $recipe->id,
            'title'            => $recipe->title,
            'image'            => $recipe->image,
            'readyInMinutes'   => $recipe->ready_in_minutes,
            'servings'         => $recipe->servings,
            'healthScore'      => $recipe->health_score,
            'pricePerServing'  => $recipe->price_per_serving,
            'vegetarian'       => $recipe->vegetarian,
            'vegan'            => $recipe->vegan,
            'glutenFree'       => $recipe->gluten_free,
            'dairyFree'        => $recipe->dairy_free,
            'categories'       => $recipe->categories ?? [],
            'nutrition'        => $this->nutritionService->format($recipe->nutrition),
            'created_at'       => $recipe->created_at,
            'updated_at'       => $recipe->updated_at,
        ];

        if ($detailed) {
            $base['summary']      = $recipe->summary;
            $base['instructions'] = $recipe->instructions;
        }

        return $base;
    }
}
