<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'budget_exceeded_email' => ['boolean'],
            'budget_exceeded_database' => ['boolean'],
            'savings_milestone_email' => ['boolean'],
            'savings_milestone_database' => ['boolean'],
            'savings_milestone_percentage' => ['integer', 'min:1', 'max:100'],
            'recurring_transaction_reminder' => ['boolean'],
        ];
    }
} 