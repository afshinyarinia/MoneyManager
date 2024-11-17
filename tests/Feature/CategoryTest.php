<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use function Pest\Laravel\{postJson, getJson, putJson, deleteJson};

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = JWTAuth::fromUser($this->user);
});

test('user can create a category', function () {
    $categoryData = [
        'name' => 'Groceries',
        'type' => 'expense',
        'icon' => 'shopping-cart',
        'color' => '#FF5733',
    ];

    $response = postJson('/api/categories', $categoryData, [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'type',
                'icon',
                'color',
                'is_system'
            ]
        ]);

    $this->assertDatabaseHas('categories', [
        'user_id' => $this->user->id,
        'name' => $categoryData['name'],
        'type' => $categoryData['type'],
    ]);
});

test('user can view categories including system categories', function () {
    Category::factory()->system()->create(['name' => 'Salary']);
    Category::factory()->count(2)->create(['user_id' => $this->user->id]);

    $response = getJson('/api/categories', [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('user cannot update system category', function () {
    $category = Category::factory()->system()->create();

    $response = putJson("/api/categories/{$category->id}",
        ['name' => 'New Name'],
        ['Authorization' => 'Bearer ' . $this->token]
    );

    $response->assertStatus(403);
});

test('user cannot delete category with transactions', function () {
    $category = Category::factory()->create(['user_id' => $this->user->id]);
    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $category->id
    ]);

    $response = deleteJson("/api/categories/{$category->id}", [], [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(422);
});
