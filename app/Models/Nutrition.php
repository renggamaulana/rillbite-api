<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nutrition extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'calories',
        'protein',
        'fat',
        'carbohydrates',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
