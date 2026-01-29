<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function favoriteRecipes()
    {
        return $this->belongsToMany(Recipe::class, 'favorite_recipes')->withTimestamps();
    }

    public function dietPlans()
    {
        return $this->hasMany(DietPlan::class);
    }

    public function hasFavorited($recipeId)
    {
        return $this->favoriteRecipes()->where('recipe_id', $recipeId)->exists();
    }
}
