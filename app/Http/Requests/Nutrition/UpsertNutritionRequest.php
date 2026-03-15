<?php

namespace App\Http\Requests\Nutrition;

use Illuminate\Foundation\Http\FormRequest;

class UpsertNutritionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by 'admin' middleware on the route
        return true;
    }

    public function rules(): array
    {
        return [
            'calories'      => ['required', 'numeric', 'min:0', 'max:99999'],
            'protein'       => ['required', 'numeric', 'min:0', 'max:9999'],
            'fat'           => ['required', 'numeric', 'min:0', 'max:9999'],
            'carbohydrates' => ['required', 'numeric', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'calories.required'      => 'Calories field is required.',
            'protein.required'       => 'Protein field is required.',
            'fat.required'           => 'Fat field is required.',
            'carbohydrates.required' => 'Carbohydrates field is required.',
        ];
    }
}
