<?php

use App\Models\User;
use App\Models\Budget;
use function Pest\Laravel\{postJson, getJson, putJson, deleteJson};

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = auth()->login($this->user);
});

test('user can create a budget', function () {
    $budgetData = [
        'name' => 'Monthly Groceries',
        'amount' => 500.00,
        'period_type' => 'monthly',
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->addMonth()->format('Y-m-d'),
    ];

    $response = postJson('/api/auth/budgets', $budgetData, [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'amount',
                'period_type',
                'start_date',
                'end_date',
                'is_active',
                'spent_amount',
                'remaining_amount'
            ]
        ]);

    $this->assertDatabaseHas('budgets', [
        'user_id' => $this->user->id,
        'name' => $budgetData['name'],
        'amount' => $budgetData['amount'],
    ]);
});

test('user can view their budgets', function () {
    $budgets = Budget::factory(3)->create([
        'user_id' => $this->user->id
    ]);

    $response = getJson('/api/auth/budgets', [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('user can update their budget', function () {
    $budget = Budget::factory()->create([
        'user_id' => $this->user->id
    ]);

    $updateData = [
        'name' => 'Updated Budget Name',
        'amount' => 750.00
    ];

    $response = putJson("/api/auth/budgets/{$budget->id}", $updateData, [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', $updateData['name'])
        ->assertJsonPath('data.amount', $updateData['amount']);
});

test('user can deactivate their budget', function () {
    $budget = Budget::factory()->create([
        'user_id' => $this->user->id
    ]);

    $response = deleteJson("/api/auth/budgets/{$budget->id}", [], [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('budgets', [
        'id' => $budget->id,
        'is_active' => false
    ]);
});

test('user cannot access other users budgets', function () {
    $otherUser = User::factory()->create();
    $budget = Budget::factory()->create([
        'user_id' => $otherUser->id
    ]);

    $response = getJson("/api/auth/budgets/{$budget->id}", [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(403);
}); 