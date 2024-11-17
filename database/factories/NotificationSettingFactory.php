<?php

namespace Database\Factories;

use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationSettingFactory extends Factory
{
    protected $model = NotificationSetting::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'budget_exceeded_email' => true,
            'budget_exceeded_database' => true,
            'savings_milestone_email' => true,
            'savings_milestone_database' => true,
            'savings_milestone_percentage' => 25,
            'recurring_transaction_reminder' => true,
        ];
    }

    public function emailsDisabled()
    {
        return $this->state(function (array $attributes) {
            return [
                'budget_exceeded_email' => false,
                'savings_milestone_email' => false,
            ];
        });
    }
} 