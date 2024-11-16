<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'type' => $this->faker->randomElement(['income', 'expense']),
            'description' => $this->faker->sentence,
            'transaction_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'is_recurring' => false,
            'recurring_frequency' => null,
        ];
    }

    public function recurring()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_recurring' => true,
                'recurring_frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly', 'yearly']),
            ];
        });
    }

    public function expense()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'expense',
            ];
        });
    }

    public function income()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'income',
            ];
        });
    }
} 