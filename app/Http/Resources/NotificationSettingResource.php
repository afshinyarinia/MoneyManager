<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationSettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'budget_exceeded_email' => $this->budget_exceeded_email,
            'budget_exceeded_database' => $this->budget_exceeded_database,
            'savings_milestone_email' => $this->savings_milestone_email,
            'savings_milestone_database' => $this->savings_milestone_database,
            'savings_milestone_percentage' => $this->savings_milestone_percentage,
            'recurring_transaction_reminder' => $this->recurring_transaction_reminder,
        ];
    }
} 