<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->word,
            'type' => $this->faker->randomElement(['income', 'expense']),
            'icon' => 'icon-' . $this->faker->word,
            'color' => '#' . $this->faker->hexColor(),
            'is_system' => false,
        ];
    }

    public function system()
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => null,
                'is_system' => true,
            ];
        });
    }
} 