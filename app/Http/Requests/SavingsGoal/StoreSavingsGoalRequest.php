<?php

namespace App\Http\Requests\SavingsGoal;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavingsGoalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:0.01'],
            'initial_amount' => ['nullable', 'numeric', 'min:0', 'lt:target_amount'],
            'target_date' => ['required', 'date', 'after:today'],
        ];
    }
} 