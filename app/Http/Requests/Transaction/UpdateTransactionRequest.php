<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'type' => ['sometimes', 'required', 'string', 'in:income,expense'],
            'description' => ['sometimes', 'required', 'string', 'max:255'],
            'transaction_date' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'is_recurring' => ['sometimes', 'boolean'],
            'recurring_frequency' => ['required_if:is_recurring,true', 'string', 'in:daily,weekly,monthly,yearly'],
        ];
    }
} 