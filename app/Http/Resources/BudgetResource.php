<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'period_type' => $this->period_type,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'is_active' => $this->is_active,
            'spent_amount' => $this->spent_amount,
            'remaining_amount' => $this->remaining_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 