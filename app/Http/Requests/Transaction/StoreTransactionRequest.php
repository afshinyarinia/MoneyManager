<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', 'string', 'in:income,expense'],
            'description' => ['required', 'string', 'max:255'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'is_recurring' => ['boolean'],
            'recurring_frequency' => ['required_if:is_recurring,true', 'string', 'in:daily,weekly,monthly,yearly'],
        ];
    }
} 