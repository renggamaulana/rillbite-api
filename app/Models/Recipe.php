<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

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
    ];

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function nutrition()
    {
        return $this->hasOne(Nutrition::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorite_recipes')->withTimestamps();
    }
}
