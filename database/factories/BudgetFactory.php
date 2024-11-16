<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'amount' => $this->faker->randomFloat(2, 100, 1000),
            'period_type' => $this->faker->randomElement(['monthly', 'weekly']),
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'is_active' => true,
        ];
    }
} 