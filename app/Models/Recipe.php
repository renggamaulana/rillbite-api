<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'title',
        'summary',
        'image',
        'ready_in_minutes',
        'servings',
        'health_score',
        'price_per_serving',
        'instructions',
        'categories',
        'vegetarian',
        'vegan',
        'gluten_free',
        'dairy_free',
    ];

    protected $casts = [
        'categories' => 'array',
        'vegetarian' => 'boolean',
        'vegan' => 'boolean',
        'gluten_free' => 'boolean',
        'dairy_free' => 'boolean',
        'price_per_serving' => 'decimal:2',
    ];

    // OPTIONAL: untuk filtering cepat
    public function scopeVegetarian($query)
    {
        return $query->where('vegetarian', true);
    }

    public function scopeVegan($query)
    {
        return $query->where('vegan', true);
    }

    public function scopeGlutenFree($query)
    {
        return $query->where('gluten_free', true);
    }

    public function scopeDairyFree($query)
    {
        return $query->where('dairy_free', true);
    }
}
