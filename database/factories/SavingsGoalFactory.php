<?php

namespace Database\Factories;

use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavingsGoalFactory extends Factory
{
    protected $model = SavingsGoal::class;

    public function definition()
    {
        $targetAmount = $this->faker->randomFloat(2, 1000, 10000);
        $currentAmount = $this->faker->randomFloat(2, 0, $targetAmount);

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'target_amount' => $targetAmount,
            'current_amount' => $currentAmount,
            'target_date' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
            'is_completed' => $currentAmount >= $targetAmount,
        ];
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            $targetAmount = $this->faker->randomFloat(2, 1000, 10000);
            return [
                'target_amount' => $targetAmount,
                'current_amount' => $targetAmount,
                'is_completed' => true,
            ];
        });
    }
} 