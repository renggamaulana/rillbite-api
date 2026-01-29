<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\Nutrition;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        $recipes = $this->getRecipesData();

        foreach ($recipes as $recipeData) {
            $ingredients = $recipeData['ingredients'];
            $nutrition = $recipeData['nutrition'];
            unset($recipeData['ingredients'], $recipeData['nutrition']);

            $recipe = Recipe::create($recipeData);

            foreach ($ingredients as $ingredientData) {
                Ingredient::create([
                    'recipe_id' => $recipe->id,
                    ...$ingredientData
                ]);
            }

            Nutrition::create([
                'recipe_id' => $recipe->id,
                ...$nutrition
            ]);
        }
    }

    private function getRecipesData(): array
    {
        return [
            // Recipe 1
            [
                'title' => 'Grilled Chicken Salad with Avocado',
                'summary' => 'A healthy and delicious grilled chicken salad packed with fresh vegetables and creamy avocado.',
                'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800',
                'ready_in_minutes' => 25,
                'servings' => 2,
                'health_score' => 95,
                'price_per_serving' => 5.50,
                'categories' => ['healthy', 'chicken', 'low-carb', 'gluten-free'],
                'gluten_free' => true,
                'instructions' => '<ol><li>Season chicken breast with salt, pepper, and olive oil.</li><li>Grill chicken for 6-8 minutes per side until fully cooked.</li><li>While chicken is cooking, prepare salad by mixing lettuce, tomatoes, cucumber, and avocado.</li><li>Slice grilled chicken and place on top of salad.</li><li>Drizzle with lemon juice and olive oil.</li><li>Serve immediately and enjoy!</li></ol>',
                'ingredients' => [
                    ['name' => 'Chicken Breast', 'original' => '2 chicken breasts', 'amount' => 2, 'unit' => 'pieces'],
                    ['name' => 'Mixed Lettuce', 'original' => '4 cups mixed lettuce', 'amount' => 4, 'unit' => 'cups'],
                    ['name' => 'Avocado', 'original' => '1 ripe avocado, sliced', 'amount' => 1, 'unit' => 'piece'],
                    ['name' => 'Cherry Tomatoes', 'original' => '1 cup cherry tomatoes, halved', 'amount' => 1, 'unit' => 'cup'],
                ],
                'nutrition' => ['calories' => 450, 'protein' => 35, 'fat' => 28, 'carbohydrates' => 15],
            ],

            // Recipe 2
            [
                'title' => 'Vegetarian Buddha Bowl',
                'summary' => 'A colorful and nutritious bowl filled with quinoa, roasted vegetables, and tahini dressing.',
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800',
                'ready_in_minutes' => 35,
                'servings' => 2,
                'health_score' => 98,
                'price_per_serving' => 4.75,
                'categories' => ['healthy', 'vegetarian', 'vegan', 'gluten-free'],
                'vegetarian' => true,
                'vegan' => true,
                'gluten_free' => true,
                'instructions' => '<ol><li>Cook quinoa according to package instructions.</li><li>Preheat oven to 400°F.</li><li>Toss sweet potato, broccoli, and chickpeas with olive oil.</li><li>Roast for 25-30 minutes.</li><li>Assemble bowls with quinoa and vegetables.</li><li>Drizzle with tahini dressing.</li></ol>',
                'ingredients' => [
                    ['name' => 'Quinoa', 'original' => '1 cup quinoa', 'amount' => 1, 'unit' => 'cup'],
                    ['name' => 'Sweet Potato', 'original' => '1 large sweet potato, cubed', 'amount' => 1, 'unit' => 'piece'],
                    ['name' => 'Chickpeas', 'original' => '1 can chickpeas', 'amount' => 1, 'unit' => 'can'],
                ],
                'nutrition' => ['calories' => 520, 'protein' => 18, 'fat' => 22, 'carbohydrates' => 65],
            ],

            // Recipe 3
            [
                'title' => 'Spaghetti Carbonara',
                'summary' => 'Classic Italian pasta with creamy egg sauce and crispy bacon.',
                'image' => 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=800',
                'ready_in_minutes' => 20,
                'servings' => 4,
                'health_score' => 65,
                'price_per_serving' => 3.25,
                'categories' => ['pasta', 'italian'],
                'instructions' => '<ol><li>Cook spaghetti in salted water.</li><li>Fry bacon until crispy.</li><li>Mix eggs with Parmesan.</li><li>Combine hot pasta with bacon.</li><li>Add egg mixture off heat.</li><li>Serve with black pepper.</li></ol>',
                'ingredients' => [
                    ['name' => 'Spaghetti', 'original' => '400g spaghetti', 'amount' => 400, 'unit' => 'grams'],
                    ['name' => 'Bacon', 'original' => '200g bacon, diced', 'amount' => 200, 'unit' => 'grams'],
                    ['name' => 'Eggs', 'original' => '4 large eggs', 'amount' => 4, 'unit' => 'pieces'],
                ],
                'nutrition' => ['calories' => 680, 'protein' => 32, 'fat' => 28, 'carbohydrates' => 72],
            ],

            // Recipe 4
            [
                'title' => 'Asian Stir-Fry Noodles',
                'summary' => 'Quick and easy stir-fried noodles with colorful vegetables.',
                'image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=800',
                'ready_in_minutes' => 15,
                'servings' => 3,
                'health_score' => 75,
                'price_per_serving' => 4.00,
                'categories' => ['noodle', 'asian', 'vegetarian'],
                'vegetarian' => true,
                'instructions' => '<ol><li>Cook noodles according to package.</li><li>Heat oil in wok.</li><li>Stir-fry vegetables.</li><li>Add noodles and sauce.</li><li>Toss and serve hot.</li></ol>',
                'ingredients' => [
                    ['name' => 'Noodles', 'original' => '300g egg noodles', 'amount' => 300, 'unit' => 'grams'],
                    ['name' => 'Bell Peppers', 'original' => '2 bell peppers, sliced', 'amount' => 2, 'unit' => 'pieces'],
                    ['name' => 'Soy Sauce', 'original' => '3 tablespoons soy sauce', 'amount' => 3, 'unit' => 'tablespoons'],
                ],
                'nutrition' => ['calories' => 420, 'protein' => 12, 'fat' => 15, 'carbohydrates' => 58],
            ],

            // Recipe 5
            [
                'title' => 'Grilled Salmon with Lemon Butter',
                'summary' => 'Perfectly grilled salmon fillets with tangy lemon butter sauce.',
                'image' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=800',
                'ready_in_minutes' => 20,
                'servings' => 2,
                'health_score' => 92,
                'price_per_serving' => 8.50,
                'categories' => ['fish', 'healthy', 'low-carb', 'gluten-free'],
                'gluten_free' => true,
                'instructions' => '<ol><li>Season salmon with salt and pepper.</li><li>Grill for 4-5 minutes per side.</li><li>Melt butter with lemon juice.</li><li>Serve salmon with lemon butter.</li></ol>',
                'ingredients' => [
                    ['name' => 'Salmon Fillets', 'original' => '2 salmon fillets', 'amount' => 2, 'unit' => 'pieces'],
                    ['name' => 'Butter', 'original' => '4 tablespoons butter', 'amount' => 4, 'unit' => 'tablespoons'],
                    ['name' => 'Lemon', 'original' => '2 lemons', 'amount' => 2, 'unit' => 'pieces'],
                ],
                'nutrition' => ['calories' => 480, 'protein' => 42, 'fat' => 32, 'carbohydrates' => 4],
            ],

            // Recipe 6
            [
                'title' => 'Keto Cauliflower Pizza',
                'summary' => 'Low-carb pizza with cauliflower crust, perfect for keto diet.',
                'image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800',
                'ready_in_minutes' => 45,
                'servings' => 4,
                'health_score' => 85,
                'price_per_serving' => 5.00,
                'categories' => ['keto', 'low-carb', 'gluten-free', 'vegetarian'],
                'vegetarian' => true,
                'gluten_free' => true,
                'instructions' => '<ol><li>Rice cauliflower in food processor.</li><li>Mix with eggs and cheese.</li><li>Bake crust for 20 minutes.</li><li>Add toppings and bake again.</li></ol>',
                'ingredients' => [
                    ['name' => 'Cauliflower', 'original' => '1 large head cauliflower', 'amount' => 1, 'unit' => 'head'],
                    ['name' => 'Mozzarella', 'original' => '2 cups mozzarella', 'amount' => 2, 'unit' => 'cups'],
                    ['name' => 'Eggs', 'original' => '2 eggs', 'amount' => 2, 'unit' => 'pieces'],
                ],
                'nutrition' => ['calories' => 280, 'protein' => 22, 'fat' => 18, 'carbohydrates' => 12],
            ],

            // Recipe 7
            [
                'title' => 'Vegan Chickpea Curry',
                'summary' => 'Hearty and flavorful curry with chickpeas and coconut milk.',
                'image' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?w=800',
                'ready_in_minutes' => 30,
                'servings' => 4,
                'health_score' => 90,
                'price_per_serving' => 3.50,
                'categories' => ['vegan', 'vegetarian', 'gluten-free', 'healthy'],
                'vegan' => true,
                'vegetarian' => true,
                'gluten_free' => true,
                'instructions' => '<ol><li>Sauté onions and garlic.</li><li>Add spices and tomatoes.</li><li>Add chickpeas and coconut milk.</li><li>Simmer for 15 minutes.</li><li>Serve with rice.</li></ol>',
                'ingredients' => [
                    ['name' => 'Chickpeas', 'original' => '2 cans chickpeas', 'amount' => 2, 'unit' => 'cans'],
                    ['name' => 'Coconut Milk', 'original' => '1 can coconut milk', 'amount' => 1, 'unit' => 'can'],
                    ['name' => 'Curry Powder', 'original' => '2 tablespoons curry powder', 'amount' => 2, 'unit' => 'tablespoons'],
                ],
                'nutrition' => ['calories' => 380, 'protein' => 14, 'fat' => 20, 'carbohydrates' => 42],
            ],

            // Recipe 8
            [
                'title' => 'Greek Yogurt Breakfast Parfait',
                'summary' => 'Healthy breakfast with Greek yogurt, granola, and berries.',
                'image' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=800',
                'ready_in_minutes' => 10,
                'servings' => 2,
                'health_score' => 88,
                'price_per_serving' => 4.25,
                'categories' => ['healthy', 'vegetarian', 'breakfast'],
                'vegetarian' => true,
                'instructions' => '<ol><li>Layer yogurt in glasses.</li><li>Add berries.</li><li>Sprinkle granola.</li><li>Drizzle honey.</li><li>Serve immediately.</li></ol>',
                'ingredients' => [
                    ['name' => 'Greek Yogurt', 'original' => '2 cups Greek yogurt', 'amount' => 2, 'unit' => 'cups'],
                    ['name' => 'Mixed Berries', 'original' => '1 cup mixed berries', 'amount' => 1, 'unit' => 'cup'],
                    ['name' => 'Granola', 'original' => '1/2 cup granola', 'amount' => 0.5, 'unit' => 'cup'],
                ],
                'nutrition' => ['calories' => 320, 'protein' => 18, 'fat' => 8, 'carbohydrates' => 45],
            ],

            // Recipe 9
            [
                'title' => 'Teriyaki Chicken Bowl',
                'summary' => 'Japanese-inspired bowl with teriyaki glazed chicken and rice.',
                'image' => 'https://images.unsplash.com/photo-1603360946369-dc9bb56dff83?w=800',
                'ready_in_minutes' => 30,
                'servings' => 3,
                'health_score' => 82,
                'price_per_serving' => 5.75,
                'categories' => ['healthy', 'chicken', 'asian'],
                'instructions' => '<ol><li>Cook rice.</li><li>Cook chicken with teriyaki sauce.</li><li>Steam vegetables.</li><li>Assemble bowls.</li><li>Garnish with sesame seeds.</li></ol>',
                'ingredients' => [
                    ['name' => 'Chicken Breast', 'original' => '450g chicken breast', 'amount' => 450, 'unit' => 'grams'],
                    ['name' => 'White Rice', 'original' => '1.5 cups rice', 'amount' => 1.5, 'unit' => 'cups'],
                    ['name' => 'Soy Sauce', 'original' => '1/4 cup soy sauce', 'amount' => 0.25, 'unit' => 'cup'],
                ],
                'nutrition' => ['calories' => 540, 'protein' => 38, 'fat' => 12, 'carbohydrates' => 68],
            ],

            // Recipe 10
            [
                'title' => 'Caprese Pasta Salad',
                'summary' => 'Fresh pasta salad with mozzarella, tomatoes, and basil.',
                'image' => 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=800',
                'ready_in_minutes' => 20,
                'servings' => 4,
                'health_score' => 78,
                'price_per_serving' => 4.50,
                'categories' => ['pasta', 'vegetarian', 'italian'],
                'vegetarian' => true,
                'instructions' => '<ol><li>Cook pasta and cool.</li><li>Mix with tomatoes and mozzarella.</li><li>Add basil leaves.</li><li>Dress with olive oil and balsamic.</li><li>Refrigerate before serving.</li></ol>',
                'ingredients' => [
                    ['name' => 'Pasta', 'original' => '350g fusilli pasta', 'amount' => 350, 'unit' => 'grams'],
                    ['name' => 'Mozzarella', 'original' => '250g fresh mozzarella', 'amount' => 250, 'unit' => 'grams'],
                    ['name' => 'Cherry Tomatoes', 'original' => '2 cups cherry tomatoes', 'amount' => 2, 'unit' => 'cups'],
                ],
                'nutrition' => ['calories' => 420, 'protein' => 18, 'fat' => 20, 'carbohydrates' => 45],
            ],

            // Recipe 11
            [
                'title' => 'Beef Tacos with Guacamole',
                'summary' => 'Mexican-style tacos with seasoned beef and fresh guacamole.',
                'image' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=800',
                'ready_in_minutes' => 25,
                'servings' => 4,
                'health_score' => 72,
                'price_per_serving' => 5.25,
                'categories' => ['mexican', 'beef'],
                'instructions' => '<ol><li>Cook ground beef with taco seasoning.</li><li>Mash avocados for guacamole.</li><li>Warm taco shells.</li><li>Fill with beef, lettuce, cheese.</li><li>Top with guacamole and serve.</li></ol>',
                'ingredients' => [
                    ['name' => 'Ground Beef', 'original' => '500g ground beef', 'amount' => 500, 'unit' => 'grams'],
                    ['name' => 'Avocados', 'original' => '2 ripe avocados', 'amount' => 2, 'unit' => 'pieces'],
                    ['name' => 'Taco Shells', 'original' => '8 taco shells', 'amount' => 8, 'unit' => 'pieces'],
                ],
                'nutrition' => ['calories' => 520, 'protein' => 28, 'fat' => 32, 'carbohydrates' => 35],
            ],

            // Recipe 12
            [
                'title' => 'Shrimp Pad Thai',
                'summary' => 'Authentic Thai noodles with shrimp, peanuts, and tamarind sauce.',
                'image' => 'https://images.unsplash.com/photo-1559314809-0d155014e29e?w=800',
                'ready_in_minutes' => 30,
                'servings' => 3,
                'health_score' => 80,
                'price_per_serving' => 7.00,
                'categories' => ['noodle', 'asian', 'fish'],
                'instructions' => '<ol><li>Soak rice noodles.</li><li>Cook shrimp.</li><li>Stir-fry noodles with sauce.</li><li>Add bean sprouts and peanuts.</li><li>Serve with lime wedges.</li></ol>',
                'ingredients' => [
                    ['name' => 'Rice Noodles', 'original' => '250g rice noodles', 'amount' => 250, 'unit' => 'grams'],
                    ['name' => 'Shrimp', 'original' => '300g shrimp, peeled', 'amount' => 300, 'unit' => 'grams'],
                    ['name' => 'Peanuts', 'original' => '1/2 cup crushed peanuts', 'amount' => 0.5, 'unit' => 'cup'],
                ],
                'nutrition' => ['calories' => 480, 'protein' => 25, 'fat' => 18, 'carbohydrates' => 55],
            ],

            // Recipe 13
            [
                'title' => 'Mushroom Risotto',
                'summary' => 'Creamy Italian rice dish with mixed mushrooms and Parmesan.',
                'image' => 'https://images.unsplash.com/photo-1476124369491-f01e80c6d313?w=800',
                'ready_in_minutes' => 40,
                'servings' => 4,
                'health_score' => 75,
                'price_per_serving' => 4.75,
                'categories' => ['italian', 'vegetarian'],
                'vegetarian' => true,
                'instructions' => '<ol><li>Sauté mushrooms and onions.</li><li>Toast risotto rice.</li><li>Gradually add broth, stirring constantly.</li><li>Add Parmesan and butter.</li><li>Serve hot with fresh herbs.</li></ol>',
                'ingredients' => [
                    ['name' => 'Arborio Rice', 'original' => '1.5 cups arborio rice', 'amount' => 1.5, 'unit' => 'cups'],
                    ['name' => 'Mixed Mushrooms', 'original' => '300g mixed mushrooms', 'amount' => 300, 'unit' => 'grams'],
                    ['name' => 'Parmesan', 'original' => '1/2 cup grated Parmesan', 'amount' => 0.5, 'unit' => 'cup'],
                ],
                'nutrition' => ['calories' => 380, 'protein' => 12, 'fat' => 14, 'carbohydrates' => 52],
            ],

            // Recipe 14
            [
                'title' => 'Chicken Caesar Wrap',
                'summary' => 'Classic Caesar salad wrapped in a soft tortilla with grilled chicken.',
                'image' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=800',
                'ready_in_minutes' => 15,
                'servings' => 2,
                'health_score' => 70,
                'price_per_serving' => 4.50,
                'categories' => ['chicken', 'healthy'],
                'instructions' => '<ol><li>Grill chicken strips.</li><li>Toss lettuce with Caesar dressing.</li><li>Warm tortillas.</li><li>Fill with lettuce, chicken, and Parmesan.</li><li>Roll and serve.</li></ol>',
                'ingredients' => [
                    ['name' => 'Chicken Breast', 'original' => '2 chicken breasts', 'amount' => 2, 'unit' => 'pieces'],
                    ['name' => 'Romaine Lettuce', 'original' => '4 cups romaine lettuce', 'amount' => 4, 'unit' => 'cups'],
                    ['name' => 'Tortillas', 'original' => '2 large tortillas', 'amount' => 2, 'unit' => 'pieces'],
                ],
                'nutrition' => ['calories' => 420, 'protein' => 32, 'fat' => 16, 'carbohydrates' => 38],
            ],

            // Recipe 15
            [
                'title' => 'Quinoa Stuffed Bell Peppers',
                'summary' => 'Colorful bell peppers stuffed with quinoa, vegetables, and cheese.',
                'image' => 'https://images.unsplash.com/photo-1552403058-f9f45d09deb1?w=800',
                'ready_in_minutes' => 45,
                'servings' => 4,
                'health_score' => 92,
                'price_per_serving' => 5.00,
                'categories' => ['healthy', 'vegetarian', 'gluten-free'],
                'vegetarian' => true,
                'gluten_free' => true,
                'instructions' => '<ol><li>Cook quinoa.</li><li>Cut tops off peppers and remove seeds.</li><li>Mix quinoa with vegetables and cheese.</li><li>Stuff peppers.</li><li>Bake at 375°F for 30 minutes.</li></ol>',
                'ingredients' => [
                    ['name' => 'Bell Peppers', 'original' => '4 large bell peppers', 'amount' => 4, 'unit' => 'pieces'],
                    ['name' => 'Quinoa', 'original' => '1 cup quinoa', 'amount' => 1, 'unit' => 'cup'],
                    ['name' => 'Feta Cheese', 'original' => '1/2 cup crumbled feta', 'amount' => 0.5, 'unit' => 'cup'],
                ],
                'nutrition' => ['calories' => 320, 'protein' => 14, 'fat' => 12, 'carbohydrates' => 42],
            ],

            // Recipe 16
            [
                'title' => 'Pork Fried Rice',
                'summary' => 'Chinese-style fried rice with pork, vegetables, and egg.',
                'image' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=800',
                'ready_in_minutes' => 20,
                'servings' => 4,
                'health_score' => 68,
                'price_per_serving' => 3.75,
                'categories' => ['asian', 'pork'],
                'instructions' => '<ol><li>Cook rice and let cool.</li><li>Stir-fry pork until cooked.</li><li>Push to side, scramble eggs.</li><li>Add rice and vegetables.</li><li>Season with soy sauce and serve.</li></ol>',
                'ingredients' => [
                    ['name' => 'Cooked Rice', 'original' => '4 cups cooked rice', 'amount' => 4, 'unit' => 'cups'],
                    ['name' => 'Pork', 'original' => '300g diced pork', 'amount' => 300, 'unit' => 'grams'],
                    ['name' => 'Eggs', 'original' => '3 eggs', 'amount' => 3, 'unit' => 'pieces'],
                ],
                'nutrition' => ['calories' => 450, 'protein' => 22, 'fat' => 18, 'carbohydrates' => 52],
            ],

            // Recipe 17
            [
                'title' => 'Lentil Soup',
                'summary' => 'Hearty and nutritious soup with lentils, vegetables, and herbs.',
                'image' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=800',
                'ready_in_minutes' => 35,
                'servings' => 6,
                'health_score' => 94,
                'price_per_serving' => 2.50,
                'categories' => ['healthy', 'vegan', 'vegetarian', 'gluten-free'],
                'vegan' => true,
                'vegetarian' => true,
                'gluten_free' => true,
                'instructions' => '<ol><li>Sauté onions, carrots, and celery.</li><li>Add lentils and broth.</li><li>Simmer for 25 minutes.</li><li>Season with herbs.</li><li>Serve hot with crusty bread.</li></ol>',
                'ingredients' => [
                    ['name' => 'Red Lentils', 'original' => '2 cups red lentils', 'amount' => 2, 'unit' => 'cups'],
                    ['name' => 'Vegetable Broth', 'original' => '6 cups vegetable broth', 'amount' => 6, 'unit' => 'cups'],
                    ['name' => 'Carrots', 'original' => '3 carrots, diced', 'amount' => 3, 'unit' => 'pieces'],
                ],
                'nutrition' => ['calories' => 220, 'protein' => 14, 'fat' => 2, 'carbohydrates' => 38],
            ],

            // Recipe 18
            [
                'title' => 'Baked Cod with Herbs',
                'summary' => 'Light and flaky cod baked with fresh herbs and lemon.',
                'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=800',
                'ready_in_minutes' => 25,
                'servings' => 2,
                'health_score' => 90,
                'price_per_serving' => 7.50,
                'categories' => ['fish', 'healthy', 'low-carb', 'gluten-free'],
                'gluten_free' => true,
                'instructions' => '<ol><li>Preheat oven to 400°F.</li><li>Place cod in baking dish.</li><li>Top with herbs, lemon, and olive oil.</li><li>Bake for 15-18 minutes.</li><li>Serve with steamed vegetables.</li></ol>',
                'ingredients' => [
                    ['name' => 'Cod Fillets', 'original' => '2 cod fillets', 'amount' => 2, 'unit' => 'pieces'],
                    ['name' => 'Fresh Herbs', 'original' => '2 tablespoons mixed herbs', 'amount' => 2, 'unit' => 'tablespoons'],
                    ['name' => 'Lemon', 'original' => '1 lemon, sliced', 'amount' => 1, 'unit' => 'piece'],
                ],
                'nutrition' => ['calories' => 180, 'protein' => 38, 'fat' => 4, 'carbohydrates' => 2],
            ],

            // Recipe 19
            [
                'title' => 'Spinach and Feta Omelette',
                'summary' => 'Fluffy omelette filled with fresh spinach and tangy feta cheese.',
                'image' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=800',
                'ready_in_minutes' => 10,
                'servings' => 1,
                'health_score' => 85,
                'price_per_serving' => 3.50,
                'categories' => ['healthy', 'vegetarian', 'breakfast', 'gluten-free'],
                'vegetarian' => true,
                'gluten_free' => true,
                'instructions' => '<ol><li>Whisk eggs with salt and pepper.</li><li>Sauté spinach until wilted.</li><li>Pour eggs into pan.</li><li>Add spinach and feta.</li><li>Fold and serve hot.</li></ol>',
                'ingredients' => [
                    ['name' => 'Eggs', 'original' => '3 large eggs', 'amount' => 3, 'unit' => 'pieces'],
                    ['name' => 'Fresh Spinach', 'original' => '2 cups fresh spinach', 'amount' => 2, 'unit' => 'cups'],
                    ['name' => 'Feta Cheese', 'original' => '1/4 cup crumbled feta', 'amount' => 0.25, 'unit' => 'cup'],
                ],
                'nutrition' => ['calories' => 280, 'protein' => 22, 'fat' => 20, 'carbohydrates' => 6],
            ],

            // Recipe 20
            [
                'title' => 'Moroccan Couscous',
                'summary' => 'Aromatic couscous with vegetables, chickpeas, and Moroccan spices.',
                'image' => 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?w=800',
                'ready_in_minutes' => 25,
                'servings' => 4,
                'health_score' => 88,
                'price_per_serving' => 4.25,
                'categories' => ['healthy', 'vegan', 'vegetarian'],
                'vegan' => true,
                'vegetarian' => true,
                'instructions' => '<ol><li>Cook couscous according to package.</li><li>Roast vegetables with Moroccan spices.</li><li>Mix couscous with vegetables and chickpeas.</li><li>Add raisins and almonds.</li><li>Serve warm or cold.</li></ol>',
                'ingredients' => [
                    ['name' => 'Couscous', 'original' => '1.5 cups couscous', 'amount' => 1.5, 'unit' => 'cups'],
                    ['name' => 'Chickpeas', 'original' => '1 can chickpeas', 'amount' => 1, 'unit' => 'can'],
                    ['name' => 'Mixed Vegetables', 'original' => '3 cups mixed vegetables', 'amount' => 3, 'unit' => 'cups'],
                ],
                'nutrition' => ['calories' => 340, 'protein' => 12, 'fat' => 8, 'carbohydrates' => 58],
            ],

            // Recipe 21
            [
                'title' => 'Thai Green Curry',
                'summary' => 'Spicy and aromatic Thai curry with coconut milk and vegetables.',
                'image' => 'https://images.unsplash.com/photo-1455619452474-d2be8b1e70cd?w=800',
                'ready_in_minutes' => 30,
                'servings' => 4,
                'health_score' => 86,
                'price_per_serving' => 5.50,
                'categories' => ['asian', 'chicken', 'gluten-free'],
                'gluten_free' => true,
                'instructions' => '<ol><li>Sauté green curry paste.</li><li>Add chicken and cook through.</li><li>Pour in coconut milk.</li><li>Add vegetables and simmer.</li><li>Serve with jasmine rice.</li></ol>',
                'ingredients' => [
                    ['name' => 'Chicken Breast', 'original' => '400g chicken breast, sliced', 'amount' => 400, 'unit' => 'grams'],
                    ['name' => 'Green Curry Paste', 'original' => '3 tablespoons green curry paste', 'amount' => 3, 'unit' => 'tablespoons'],
                    ['name' => 'Coconut Milk', 'original' => '1 can coconut milk', 'amount' => 1, 'unit' => 'can'],
                ],
                'nutrition' => ['calories' => 420, 'protein' => 28, 'fat' => 26, 'carbohydrates' => 22],
            ],

            // Recipe 22
            [
                'title' => 'Sweet Potato and Black Bean Burrito',
                'summary' => 'Hearty vegetarian burrito with roasted sweet potato and black beans.',
                'image' => 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=800',
                'ready_in_minutes' => 35,
                'servings' => 4,
                'health_score' => 91,
                'price_per_serving' => 4.00,
                'categories' => ['healthy', 'vegetarian', 'vegan'],
                'vegetarian' => true,
                'vegan' => true,
                'instructions' => '<ol><li>Roast sweet potato cubes.</li><li>Warm black beans with spices.</li><li>Warm tortillas.</li><li>Fill with sweet potato, beans, and toppings.</li><li>Roll and serve.</li></ol>',
                'ingredients' => [
                    ['name' => 'Sweet Potato', 'original' => '2 large sweet potatoes, cubed', 'amount' => 2, 'unit' => 'pieces'],
                    ['name' => 'Black Beans', 'original' => '2 cans black beans', 'amount' => 2, 'unit' => 'cans'],
                    ['name' => 'Tortillas', 'original' => '4 large tortillas', 'amount' => 4, 'unit' => 'pieces'],
                ],
                'nutrition' => ['calories' => 380, 'protein' => 14, 'fat' => 6, 'carbohydrates' => 68],
            ],
        ];
    }
}
