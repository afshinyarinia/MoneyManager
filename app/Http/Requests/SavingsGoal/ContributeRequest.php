<?php

namespace App\Http\Requests\SavingsGoal;

use Illuminate\Foundation\Http\FormRequest;

class ContributeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $savingsGoal = $this->route('savings_goal');
        $maxContribution = $savingsGoal->remaining_amount;

        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                "max:$maxContribution"
            ],
        ];
    }

    public function messages()
    {
        return [
            'amount.max' => 'The contribution amount cannot exceed the remaining amount needed for this goal.',
        ];
    }
} 