<?php

namespace App\Services;

use App\Models\Nutrition;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

class NutritionService
{
    // -------------------------------------------------------------------------
    // Daily reference values (FDA standard)
    // -------------------------------------------------------------------------
    private const DAILY_VALUES = [
        'calories'      => 2000,
        'protein'       => 50,
        'fat'           => 70,
        'carbohydrates' => 300,
    ];

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    /**
     * Upsert nutrition data for a recipe.
     * Returns the saved Nutrition model.
     */
    public function upsert(int $recipeId, array $data): Nutrition
    {
        return Nutrition::updateOrCreate(
            ['recipe_id' => $recipeId],
            $this->sanitize($data)
        );
    }

    /**
     * Get nutrition for a recipe, returns null if not found.
     */
    public function getByRecipe(int $recipeId): ?Nutrition
    {
        return Nutrition::where('recipe_id', $recipeId)->first();
    }

    /**
     * Delete nutrition record for a recipe.
     */
    public function deleteByRecipe(int $recipeId): bool
    {
        return (bool) Nutrition::where('recipe_id', $recipeId)->delete();
    }

    // -------------------------------------------------------------------------
    // Formatting
    // -------------------------------------------------------------------------

    /**
     * Format Nutrition model into the API response shape.
     * Returns null when $nutrition is null.
     */
    public function format(?Nutrition $nutrition): ?array
    {
        if (!$nutrition) {
            return null;
        }

        $cal   = (float) $nutrition->calories;
        $prot  = (float) $nutrition->protein;
        $fat   = (float) $nutrition->fat;
        $carbs = (float) $nutrition->carbohydrates;

        return [
            'nutrients' => [
                $this->nutrient('Calories',      $cal,  'kcal', self::DAILY_VALUES['calories']),
                $this->nutrient('Protein',        $prot, 'g',    self::DAILY_VALUES['protein']),
                $this->nutrient('Fat',            $fat,  'g',    self::DAILY_VALUES['fat']),
                $this->nutrient('Carbohydrates',  $carbs,'g',    self::DAILY_VALUES['carbohydrates']),
            ],
            'properties'       => [],
            'flavonoids'       => [],
            'ingredients'      => [],
            'caloricBreakdown' => $this->caloricBreakdown($cal, $prot, $fat, $carbs),
            'weightPerServing' => ['amount' => 300, 'unit' => 'g'],
        ];
    }

    /**
     * Bulk-format an array of recipes' nutrition keyed by recipe_id.
     * Useful when loading recipe lists with nutrition data.
     *
     * @param  int[]  $recipeIds
     * @return array<int, array>
     */
    public function formatBulk(array $recipeIds): array
    {
        $nutritions = Nutrition::whereIn('recipe_id', $recipeIds)
            ->get()
            ->keyBy('recipe_id');

        $result = [];
        foreach ($recipeIds as $id) {
            $result[$id] = $this->format($nutritions->get($id));
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // Seeding helpers (used by Artisan commands)
    // -------------------------------------------------------------------------

    /**
     * Generate a plausible nutrition estimate for a recipe that has none.
     * Uses health_score and servings as a rough heuristic.
     */
    public function generateEstimate(Recipe $recipe): array
    {
        $servings    = max(1, (int) $recipe->servings);
        $healthScore = (float) ($recipe->health_score ?? 50);

        // Base calories inversely proportional to health score
        $baseCal  = 800 - ($healthScore * 4);
        $calories = max(150, round($baseCal / $servings, 1));

        // Macro ratios vary slightly by dietary flags
        $proteinRatio = $recipe->vegan ? 0.12 : ($recipe->vegetarian ? 0.14 : 0.20);
        $fatRatio     = $recipe->low_fat ?? false ? 0.20 : ($healthScore > 70 ? 0.28 : 0.35);

        $protein = round(($calories * $proteinRatio) / 4, 1);  // 4 kcal/g
        $fat     = round(($calories * $fatRatio)     / 9, 1);  // 9 kcal/g
        $carbs   = round(($calories - ($protein * 4) - ($fat * 9)) / 4, 1);

        return [
            'calories'      => max(0, $calories),
            'protein'       => max(0, $protein),
            'fat'           => max(0, $fat),
            'carbohydrates' => max(0, $carbs),
        ];
    }

    /**
     * Seed nutrition for all recipes that currently lack it.
     * Returns the count of records created.
     */
    public function seedMissing(): int
    {
        $recipesWithout = Recipe::doesntHave('nutrition')->get();
        $count = 0;

        DB::transaction(function () use ($recipesWithout, &$count) {
            foreach ($recipesWithout as $recipe) {
                $estimate = $this->generateEstimate($recipe);
                Nutrition::create(array_merge(['recipe_id' => $recipe->id], $estimate));
                $count++;
            }
        });

        return $count;
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function sanitize(array $data): array
    {
        return [
            'calories'      => max(0, (float) ($data['calories']      ?? 0)),
            'protein'       => max(0, (float) ($data['protein']       ?? 0)),
            'fat'           => max(0, (float) ($data['fat']           ?? 0)),
            'carbohydrates' => max(0, (float) ($data['carbohydrates'] ?? 0)),
        ];
    }

    private function nutrient(string $name, float $amount, string $unit, float $daily): array
    {
        return [
            'name'               => $name,
            'amount'             => $amount,
            'unit'               => $unit,
            'percentOfDailyNeeds'=> $daily > 0 ? round(($amount / $daily) * 100, 2) : 0,
        ];
    }

    private function caloricBreakdown(float $cal, float $prot, float $fat, float $carbs): array
    {
        if ($cal <= 0) {
            return ['percentProtein' => 0, 'percentFat' => 0, 'percentCarbs' => 0];
        }

        return [
            'percentProtein' => round(($prot * 4 / $cal) * 100, 2),
            'percentFat'     => round(($fat  * 9 / $cal) * 100, 2),
            'percentCarbs'   => round(($carbs * 4 / $cal) * 100, 2),
        ];
    }
}
