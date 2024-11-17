<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category' => new CategoryResource($this->category),
            'amount' => $this->amount,
            'type' => $this->type,
            'description' => $this->description,
            'transaction_date' => $this->transaction_date->format('Y-m-d'),
            'is_recurring' => $this->is_recurring,
            'recurring_frequency' => $this->when($this->is_recurring, $this->recurring_frequency),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
