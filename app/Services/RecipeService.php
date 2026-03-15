<?php

namespace App\Services;

use App\Models\Recipe;

class RecipeService
{
    public function __construct(
        private NutritionService $nutritionService
    ) {}

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Search recipes with filters.
     */
    public function complexSearch(array $filters): array
    {
        $query    = $filters['query']  ?? '';
        $number   = $filters['number'] ?? 12;
        $category = $filters['category'] ?? null;
        $withInfo = filter_var($filters['addRecipeInformation'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $relations = $withInfo ? ['ingredients', 'nutrition'] : [];

        $recipes = Recipe::with($relations)
            ->when($query, fn($q) => $q->where(function ($sub) use ($query) {
                $sub->where('title',   'like', "%{$query}%")
                    ->orWhere('summary', 'like', "%{$query}%");
            }))
            ->when($category, fn($q) => $q->whereJsonContains('categories', $category))
            ->when(isset($filters['vegetarian']),  fn($q) => $q->where('vegetarian',  filter_var($filters['vegetarian'],  FILTER_VALIDATE_BOOLEAN)))
            ->when(isset($filters['vegan']),        fn($q) => $q->where('vegan',       filter_var($filters['vegan'],       FILTER_VALIDATE_BOOLEAN)))
            ->when(isset($filters['gluten_free']),  fn($q) => $q->where('gluten_free', filter_var($filters['gluten_free'], FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('health_score', 'desc')
            ->limit($number)
            ->get();

        return [
            'results'      => $recipes->map(fn($r) => $this->formatSummary($r))->values(),
            'offset'       => 0,
            'number'       => $recipes->count(),
            'totalResults' => Recipe::count(),
        ];
    }

    /**
     * Get full recipe detail by ID.
     */
    public function getInformation(int $id): ?array
    {
        $recipe = Recipe::with(['ingredients', 'nutrition'])->find($id);

        if (!$recipe) {
            return null;
        }

        return $this->formatDetail($recipe);
    }

    /**
     * Get recipes by category.
     */
    public function byCategory(string $category): array
    {
        $recipes = Recipe::whereJsonContains('categories', $category)
            ->with(['nutrition'])
            ->orderBy('health_score', 'desc')
            ->limit(12)
            ->get();

        return [
            'category' => $category,
            'results'  => $recipes->map(fn($r) => [
                'id'             => $r->id,
                'title'          => $r->title,
                'image'          => $r->image,
                'readyInMinutes' => $r->ready_in_minutes,
                'servings'       => $r->servings,
                'healthScore'    => $r->health_score,
                'nutrition'      => $this->nutritionService->format($r->nutrition),
            ])->values(),
            'total' => $recipes->count(),
        ];
    }

    /**
     * Get random recipes.
     */
    public function random(array $filters): array
    {
        $number = $filters['number'] ?? 6;
        $tags   = $filters['tags']   ?? null;

        $recipes = Recipe::with(['nutrition'])
            ->inRandomOrder()
            ->when($tags, function ($q) use ($tags) {
                $tagsArray = array_map('trim', explode(',', $tags));
                $q->where(function ($inner) use ($tagsArray) {
                    foreach ($tagsArray as $tag) {
                        $inner->orWhereJsonContains('categories', $tag);
                    }
                });
            })
            ->limit($number)
            ->get();

        return [
            'recipes' => $recipes->map(fn($r) => [
                'id'             => $r->id,
                'title'          => $r->title,
                'image'          => $r->image,
                'imageType'      => 'jpg',
                'readyInMinutes' => $r->ready_in_minutes,
                'servings'       => $r->servings,
                'healthScore'    => $r->health_score,
                'vegetarian'     => $r->vegetarian,
                'vegan'          => $r->vegan,
                'glutenFree'     => $r->gluten_free,
                'dairyFree'      => $r->dairy_free,
                'summary'        => $r->summary,
                'nutrition'      => $this->nutritionService->format($r->nutrition),
            ])->values(),
        ];
    }

    // =========================================================================
    // Private formatters
    // =========================================================================

    private function formatSummary(Recipe $recipe): array
    {
        return [
            'id'              => $recipe->id,
            'title'           => $recipe->title,
            'image'           => $recipe->image,
            'imageType'       => 'jpg',
            'readyInMinutes'  => $recipe->ready_in_minutes,
            'servings'        => $recipe->servings,
            'healthScore'     => $recipe->health_score,
            'pricePerServing' => $recipe->price_per_serving * 100,
            'vegetarian'      => $recipe->vegetarian,
            'vegan'           => $recipe->vegan,
            'glutenFree'      => $recipe->gluten_free,
            'dairyFree'       => $recipe->dairy_free,
        ];
    }

    private function formatDetail(Recipe $recipe): array
    {
        return [
            'id'                   => $recipe->id,
            'title'                => $recipe->title,
            'summary'              => $recipe->summary,
            'image'                => $recipe->image,
            'imageType'            => 'jpg',
            'readyInMinutes'       => $recipe->ready_in_minutes,
            'servings'             => $recipe->servings,
            'healthScore'          => $recipe->health_score,
            'pricePerServing'      => $recipe->price_per_serving * 100,
            'instructions'         => $recipe->instructions,
            'vegetarian'           => $recipe->vegetarian,
            'vegan'                => $recipe->vegan,
            'glutenFree'           => $recipe->gluten_free,
            'dairyFree'            => $recipe->dairy_free,
            'sustainable'          => false,
            'gaps'                 => 'no',
            'lowFodmap'            => false,
            'ketogenic'            => false,
            'whole30'              => false,
            'sourceUrl'            => '',
            'spoonacularSourceUrl' => '',
            'aggregateLikes'       => rand(100, 500),
            'spoonacularScore'     => $recipe->health_score,
            'creditsText'          => 'Rillbite',
            'sourceName'           => 'Rillbite',
            'extendedIngredients'  => $recipe->ingredients
                                        ->map(fn($i) => $this->formatIngredient($i))
                                        ->values(),
            'nutrition'            => $this->nutritionService->format($recipe->nutrition),
            'cuisines'             => [],
            'dishTypes'            => [],
            'diets'                => array_values(array_filter([
                $recipe->vegetarian  ? 'vegetarian' : null,
                $recipe->vegan       ? 'vegan'       : null,
                $recipe->gluten_free ? 'gluten free' : null,
            ])),
            'occasions'            => [],
            'analyzedInstructions' => [],
            'originalId'           => null,
        ];
    }

    private function formatIngredient($ingredient): array
    {
        return [
            'id'           => $ingredient->id,
            'aisle'        => 'Produce',
            'image'        => '',
            'consistency'  => 'solid',
            'name'         => $ingredient->name,
            'nameClean'    => strtolower($ingredient->name),
            'original'     => $ingredient->original,
            'originalName' => $ingredient->name,
            'amount'       => (float) $ingredient->amount,
            'unit'         => $ingredient->unit,
            'meta'         => [],
            'measures'     => [
                'us'     => ['amount' => (float) $ingredient->amount, 'unitShort' => $ingredient->unit, 'unitLong' => $ingredient->unit],
                'metric' => ['amount' => (float) $ingredient->amount, 'unitShort' => $ingredient->unit, 'unitLong' => $ingredient->unit],
            ],
        ];
    }
}
