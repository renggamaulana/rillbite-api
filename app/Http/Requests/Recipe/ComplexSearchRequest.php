<?php

namespace App\Http\Requests\Recipe;

use Illuminate\Foundation\Http\FormRequest;

class ComplexSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query'                  => 'nullable|string|max:255',
            'number'                 => 'nullable|integer|min:1|max:100',
            'category'               => 'nullable|string|max:100',
            'addRecipeInformation'   => 'nullable|boolean',
            'vegetarian'             => 'nullable|boolean',
            'vegan'                  => 'nullable|boolean',
            'gluten_free'            => 'nullable|boolean',
        ];
    }
}
