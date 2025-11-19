<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recipe;
use App\Models\Nutrition;
use App\Models\Ingredient;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        // Contoh data recipe
        $recipe = Recipe::create([
            'title'            => 'Grilled Chicken Salad',
            'summary'          => 'Healthy grilled chicken salad with fresh vegetables and light dressing.',
            'image'            => 'grilled-chicken-salad.jpg',
            'ready_in_minutes' => 20,
            'servings'         => 2,
            'health_score'     => 85,
            'price_per_serving'=> 3.50,
            'instructions'     => "1. Grill the chicken.\n2. Prepare vegetables.\n3. Mix together with dressing.",
            'categories'       => ['healthy', 'chicken', 'salad'],
            'vegetarian'       => false,
            'vegan'            => false,
            'gluten_free'      => true,
            'dairy_free'       => true,
        ]);

        // Nutrition
        Nutrition::create([
            'recipe_id'     => $recipe->id,
            'calories'      => 350,
            'protein'       => 30,
            'fat'           => 10,
            'carbohydrates' => 25,
        ]);

        // Ingredients
        $ingredients = [
            [
                'name'    => 'Chicken Breast',
                'original'=> '200g chicken breast, grilled',
                'amount'  => 200,
                'unit'    => 'g',
            ],
            [
                'name'    => 'Lettuce',
                'original'=> '1 cup chopped lettuce',
                'amount'  => 1,
                'unit'    => 'cup',
            ],
            [
                'name'    => 'Tomato',
                'original'=> '1 sliced tomato',
                'amount'  => 1,
                'unit'    => 'piece',
            ],
            [
                'name'    => 'Olive Oil',
                'original'=> '1 tbsp olive oil',
                'amount'  => 1,
                'unit'    => 'tbsp',
            ],
        ];

        foreach ($ingredients as $item) {
            Ingredient::create([
                'recipe_id' => $recipe->id,
                'name'      => $item['name'],
                'original'  => $item['original'],
                'amount'    => $item['amount'],
                'unit'      => $item['unit'],
            ]);
        }
    }
}
