<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    /**
     * Search recipes (Spoonacular compatible endpoint)
     * GET /api/recipes/complexSearch
     *
     * Query Parameters:
     * - query: string (search term)
     * - number: integer (limit results, default: 12)
     * - category: string (filter by category)
     * - addRecipeInformation: boolean (include full recipe info)
     */
    public function complexSearch(Request $request)
    {
        try {
            $query = $request->input('query', '');
            $number = $request->input('number', 12);
            $category = $request->input('category', null);
            $addRecipeInformation = $request->input('addRecipeInformation', false);

            // Build query with optional relationships
            $recipes = Recipe::with($addRecipeInformation ? ['ingredients', 'nutrition'] : [])
                ->when($query, function ($q) use ($query) {
                    $q->where(function($subQuery) use ($query) {
                        $subQuery->where('title', 'like', "%{$query}%")
                                 ->orWhere('summary', 'like', "%{$query}%");
                    });
                })
                ->when($category, function ($q) use ($category) {
                    $q->whereJsonContains('categories', $category);
                })
                ->when($request->has('vegetarian'), function ($q) use ($request) {
                    $q->where('vegetarian', $request->boolean('vegetarian'));
                })
                ->when($request->has('vegan'), function ($q) use ($request) {
                    $q->where('vegan', $request->boolean('vegan'));
                })
                ->when($request->has('gluten_free'), function ($q) use ($request) {
                    $q->where('gluten_free', $request->boolean('gluten_free'));
                })
                ->orderBy('health_score', 'desc')
                ->limit($number)
                ->get();

            // Format response to match Spoonacular API structure
            return response()->json([
                'results' => $recipes->map(function ($recipe) {
                    return [
                        'id' => $recipe->id,
                        'title' => $recipe->title,
                        'image' => $recipe->image,
                        'imageType' => 'jpg',
                        'readyInMinutes' => $recipe->ready_in_minutes,
                        'servings' => $recipe->servings,
                        'healthScore' => $recipe->health_score,
                        'pricePerServing' => $recipe->price_per_serving * 100,
                        'vegetarian' => $recipe->vegetarian,
                        'vegan' => $recipe->vegan,
                        'glutenFree' => $recipe->gluten_free,
                        'dairyFree' => $recipe->dairy_free,
                    ];
                }),
                'offset' => 0,
                'number' => $recipes->count(),
                'totalResults' => Recipe::count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching recipes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recipe detail by ID (Spoonacular compatible)
     * GET /api/recipes/{id}/information
     *
     * Returns complete recipe information including:
     * - Basic info (title, image, servings, etc)
     * - Extended ingredients
     * - Cooking instructions
     * - Nutrition information
     */
    public function information($id)
    {
        try {
            $recipe = Recipe::with(['ingredients', 'nutrition'])->find($id);

            if (!$recipe) {
                return response()->json([
                    'message' => 'Recipe not found',
                ], 404);
            }

            // Format response to match Spoonacular API structure
            return response()->json([
                'id' => $recipe->id,
                'title' => $recipe->title,
                'summary' => $recipe->summary,
                'image' => $recipe->image,
                'imageType' => 'jpg',
                'readyInMinutes' => $recipe->ready_in_minutes,
                'servings' => $recipe->servings,
                'healthScore' => $recipe->health_score,
                'pricePerServing' => $recipe->price_per_serving * 100, // Convert to cents
                'instructions' => $recipe->instructions,
                'vegetarian' => $recipe->vegetarian,
                'vegan' => $recipe->vegan,
                'glutenFree' => $recipe->gluten_free,
                'dairyFree' => $recipe->dairy_free,
                'sustainable' => false,
                'weightWatcherSmartPoints' => 0,
                'gaps' => 'no',
                'lowFodmap' => false,
                'ketogenic' => false,
                'whole30' => false,
                'sourceUrl' => '',
                'spoonacularSourceUrl' => '',
                'aggregateLikes' => rand(100, 500),
                'spoonacularScore' => $recipe->health_score,
                'creditsText' => 'Rillbite',
                'sourceName' => 'Rillbite',
                'extendedIngredients' => $recipe->ingredients->map(function ($ingredient) {
                    return [
                        'id' => $ingredient->id,
                        'aisle' => 'Produce',
                        'image' => '',
                        'consistency' => 'solid',
                        'name' => $ingredient->name,
                        'nameClean' => strtolower($ingredient->name),
                        'original' => $ingredient->original,
                        'originalName' => $ingredient->name,
                        'amount' => (float) $ingredient->amount,
                        'unit' => $ingredient->unit,
                        'meta' => [],
                        'measures' => [
                            'us' => [
                                'amount' => (float) $ingredient->amount,
                                'unitShort' => $ingredient->unit,
                                'unitLong' => $ingredient->unit,
                            ],
                            'metric' => [
                                'amount' => (float) $ingredient->amount,
                                'unitShort' => $ingredient->unit,
                                'unitLong' => $ingredient->unit,
                            ]
                        ]
                    ];
                }),
                'nutrition' => $recipe->nutrition ? [
                    'nutrients' => [
                        [
                            'name' => 'Calories',
                            'amount' => (float) $recipe->nutrition->calories,
                            'unit' => 'kcal',
                            'percentOfDailyNeeds' => round(($recipe->nutrition->calories / 2000) * 100, 2)
                        ],
                        [
                            'name' => 'Protein',
                            'amount' => (float) $recipe->nutrition->protein,
                            'unit' => 'g',
                            'percentOfDailyNeeds' => round(($recipe->nutrition->protein / 50) * 100, 2)
                        ],
                        [
                            'name' => 'Fat',
                            'amount' => (float) $recipe->nutrition->fat,
                            'unit' => 'g',
                            'percentOfDailyNeeds' => round(($recipe->nutrition->fat / 70) * 100, 2)
                        ],
                        [
                            'name' => 'Carbohydrates',
                            'amount' => (float) $recipe->nutrition->carbohydrates,
                            'unit' => 'g',
                            'percentOfDailyNeeds' => round(($recipe->nutrition->carbohydrates / 300) * 100, 2)
                        ],
                    ],
                    'properties' => [],
                    'flavonoids' => [],
                    'ingredients' => [],
                    'caloricBreakdown' => [
                        'percentProtein' => round(($recipe->nutrition->protein * 4 / $recipe->nutrition->calories) * 100, 2),
                        'percentFat' => round(($recipe->nutrition->fat * 9 / $recipe->nutrition->calories) * 100, 2),
                        'percentCarbs' => round(($recipe->nutrition->carbohydrates * 4 / $recipe->nutrition->calories) * 100, 2),
                    ],
                    'weightPerServing' => [
                        'amount' => 300,
                        'unit' => 'g'
                    ]
                ] : null,
                'cuisines' => [],
                'dishTypes' => [],
                'diets' => array_filter([
                    $recipe->vegetarian ? 'vegetarian' : null,
                    $recipe->vegan ? 'vegan' : null,
                    $recipe->gluten_free ? 'gluten free' : null,
                ]),
                'occasions' => [],
                'analyzedInstructions' => [],
                'originalId' => null,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching recipe detail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recipes by category
     * GET /api/recipes/category/{category}
     *
     * Returns all recipes that match the specified category
     */
    public function byCategory($category)
    {
        try {
            $recipes = Recipe::whereJsonContains('categories', $category)
                ->with(['nutrition'])
                ->orderBy('health_score', 'desc')
                ->limit(12)
                ->get();

            return response()->json([
                'category' => $category,
                'results' => $recipes->map(function ($recipe) {
                    return [
                        'id' => $recipe->id,
                        'title' => $recipe->title,
                        'image' => $recipe->image,
                        'readyInMinutes' => $recipe->ready_in_minutes,
                        'servings' => $recipe->servings,
                        'healthScore' => $recipe->health_score,
                    ];
                }),
                'total' => $recipes->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching recipes by category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get random recipes
     * GET /api/recipes/random
     *
     * Query Parameters:
     * - number: integer (number of random recipes, default: 6)
     * - tags: string (comma-separated tags for filtering)
     */
    public function random(Request $request)
    {
        try {
            $number = $request->input('number', 6);
            $tags = $request->input('tags', null);

            $query = Recipe::inRandomOrder();

            // Filter by tags if provided
            if ($tags) {
                $tagsArray = explode(',', $tags);
                $query->where(function($q) use ($tagsArray) {
                    foreach ($tagsArray as $tag) {
                        $q->orWhereJsonContains('categories', trim($tag));
                    }
                });
            }

            $recipes = $query->limit($number)->get();

            return response()->json([
                'recipes' => $recipes->map(function ($recipe) {
                    return [
                        'id' => $recipe->id,
                        'title' => $recipe->title,
                        'image' => $recipe->image,
                        'imageType' => 'jpg',
                        'readyInMinutes' => $recipe->ready_in_minutes,
                        'servings' => $recipe->servings,
                        'healthScore' => $recipe->health_score,
                        'vegetarian' => $recipe->vegetarian,
                        'vegan' => $recipe->vegan,
                        'glutenFree' => $recipe->gluten_free,
                        'dairyFree' => $recipe->dairy_free,
                        'summary' => $recipe->summary,
                    ];
                }),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching random recipes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available categories
     * GET /api/recipes/categories
     *
     * Returns a list of all unique categories from recipes
     */
    public function categories()
    {
        try {
            $allCategories = Recipe::pluck('categories')
                ->flatten()
                ->unique()
                ->sort()
                ->values();

            return response()->json([
                'categories' => $allCategories,
                'total' => $allCategories->count(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all recipes (with pagination)
     * GET /api/recipes
     *
     * Query Parameters:
     * - page: integer (page number, default: 1)
     * - per_page: integer (items per page, default: 12)
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 12);

            $recipes = Recipe::with(['nutrition'])
                ->orderBy('health_score', 'desc')
                ->paginate($perPage);

            return response()->json([
                'data' => $recipes->map(function ($recipe) {
                    return [
                        'id' => $recipe->id,
                        'title' => $recipe->title,
                        'image' => $recipe->image,
                        'readyInMinutes' => $recipe->ready_in_minutes,
                        'servings' => $recipe->servings,
                        'healthScore' => $recipe->health_score,
                        'vegetarian' => $recipe->vegetarian,
                        'vegan' => $recipe->vegan,
                    ];
                }),
                'current_page' => $recipes->currentPage(),
                'last_page' => $recipes->lastPage(),
                'per_page' => $recipes->perPage(),
                'total' => $recipes->total(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching recipes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search recipes by ingredients
     * GET /api/recipes/findByIngredients
     *
     * Query Parameters:
     * - ingredients: string (comma-separated ingredients)
     * - number: integer (limit results, default: 10)
     */
    public function findByIngredients(Request $request)
    {
        try {
            $ingredientsParam = $request->input('ingredients', '');
            $number = $request->input('number', 10);

            if (empty($ingredientsParam)) {
                return response()->json([
                    'message' => 'Please provide ingredients parameter'
                ], 400);
            }

            $ingredientsArray = array_map('trim', explode(',', $ingredientsParam));

            // Find recipes that contain any of the specified ingredients
            $recipes = Recipe::with(['ingredients', 'nutrition'])
                ->whereHas('ingredients', function ($query) use ($ingredientsArray) {
                    $query->where(function ($q) use ($ingredientsArray) {
                        foreach ($ingredientsArray as $ingredient) {
                            $q->orWhere('name', 'like', "%{$ingredient}%");
                        }
                    });
                })
                ->limit($number)
                ->get();

            return response()->json(
                $recipes->map(function ($recipe) use ($ingredientsArray) {
                    // Count how many ingredients match
                    $matchedIngredients = $recipe->ingredients->filter(function ($ingredient) use ($ingredientsArray) {
                        foreach ($ingredientsArray as $searchIngredient) {
                            if (stripos($ingredient->name, $searchIngredient) !== false) {
                                return true;
                            }
                        }
                        return false;
                    });

                    return [
                        'id' => $recipe->id,
                        'title' => $recipe->title,
                        'image' => $recipe->image,
                        'imageType' => 'jpg',
                        'usedIngredientCount' => $matchedIngredients->count(),
                        'missedIngredientCount' => $recipe->ingredients->count() - $matchedIngredients->count(),
                        'missedIngredients' => [],
                        'usedIngredients' => $matchedIngredients->map(function ($ingredient) {
                            return [
                                'id' => $ingredient->id,
                                'name' => $ingredient->name,
                                'amount' => $ingredient->amount,
                                'unit' => $ingredient->unit,
                                'original' => $ingredient->original,
                            ];
                        })->values(),
                        'unusedIngredients' => [],
                        'likes' => rand(100, 500),
                    ];
                })
            , 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error searching recipes by ingredients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get similar recipes
     * GET /api/recipes/{id}/similar
     *
     * Returns recipes similar to the specified recipe
     */
    public function similar($id)
    {
        try {
            $recipe = Recipe::find($id);

            if (!$recipe) {
                return response()->json([
                    'message' => 'Recipe not found'
                ], 404);
            }

            // Find similar recipes based on categories
            $similarRecipes = Recipe::where('id', '!=', $id)
                ->where(function ($query) use ($recipe) {
                    foreach ($recipe->categories as $category) {
                        $query->orWhereJsonContains('categories', $category);
                    }
                })
                ->limit(5)
                ->get();

            return response()->json(
                $similarRecipes->map(function ($recipe) {
                    return [
                        'id' => $recipe->id,
                        'title' => $recipe->title,
                        'imageType' => 'jpg',
                        'readyInMinutes' => $recipe->ready_in_minutes,
                        'servings' => $recipe->servings,
                        'sourceUrl' => '',
                    ];
                })
            , 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching similar recipes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
