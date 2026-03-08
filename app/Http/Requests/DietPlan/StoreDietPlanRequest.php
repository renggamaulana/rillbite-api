<?php

namespace App\Http\Requests\DietPlan;

use Illuminate\Foundation\Http\FormRequest;

class StoreDietPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipe_id'   => 'required|integer|exists:recipes,id',
            'day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'meal_type'   => 'required|string|in:breakfast,lunch,dinner',
            'week_number' => 'sometimes|integer|min:1',
        ];
    }
}
