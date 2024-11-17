<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use function Pest\Laravel\{postJson, getJson, putJson, deleteJson};

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = JWTAuth::fromUser($this->user);
    $this->category = Category::factory()->create(['user_id' => $this->user->id]);
});

test('user can create a transaction', function () {
    $transactionData = [
        'category_id' => $this->category->id,
        'amount' => 150.00,
        'type' => 'expense',
        'description' => 'Grocery shopping',
        'transaction_date' => now()->format('Y-m-d'),
        'is_recurring' => false,
    ];

    $response = postJson('/api/transactions', $transactionData, [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'amount',
                'type',
                'description',
                'transaction_date',
                'is_recurring',
                'category'
            ]
        ]);

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'amount' => $transactionData['amount'],
        'type' => $transactionData['type'],
    ]);
});

test('user can create a recurring transaction', function () {
    $transactionData = [
        'category_id' => $this->category->id,
        'amount' => 1000.00,
        'type' => 'income',
        'description' => 'Monthly Salary',
        'transaction_date' => now()->format('Y-m-d'),
        'is_recurring' => true,
        'recurring_frequency' => 'monthly',
    ];

    $response = postJson('/api/transactions', $transactionData, [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.is_recurring', true)
        ->assertJsonPath('data.recurring_frequency', 'monthly');
});

test('user can view their transactions', function () {
    Transaction::factory(3)->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id
    ]);

    $response = getJson('/api/transactions', [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('user can filter transactions by date range', function () {
    // Create old transaction
    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'transaction_date' => now()->subMonths(2)
    ]);

    // Create recent transaction
    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'transaction_date' => now()
    ]);

    $response = getJson('/api/transactions?' . http_build_query([
        'date_from' => now()->subMonth()->format('Y-m-d'),
        'date_to' => now()->format('Y-m-d')
    ]), [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('user can view transaction summary', function () {
    // Create income transaction
    Transaction::factory()->income()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'amount' => 1000,
        'transaction_date' => now()
    ]);

    // Create expense transaction
    Transaction::factory()->expense()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'amount' => 500,
        'transaction_date' => now()
    ]);

    $response = getJson('/api/transactions/summary', [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'income' => 1000,
            'expense' => 500,
        ]);
});

test('user cannot access other users transactions', function () {
    $otherUser = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'category_id' => Category::factory()->create(['user_id' => $otherUser->id])
    ]);

    $response = getJson("/api/transactions/{$transaction->id}", [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(403);
});
