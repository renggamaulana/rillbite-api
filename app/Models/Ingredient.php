<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'name',
        'original',
        'amount',
        'unit',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
