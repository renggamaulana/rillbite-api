<?php

namespace App\Http\Requests\Recipe;

use Illuminate\Foundation\Http\FormRequest;

class RandomRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => 'nullable|integer|min:1|max:50',
            'tags'   => 'nullable|string|max:255',
        ];
    }
}
