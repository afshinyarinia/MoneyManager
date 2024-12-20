<?php

use App\Models\User;
use App\Models\SavingsGoal;
use App\Notifications\SavingsGoalMilestoneNotification;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Notification;
use function Pest\Laravel\{postJson, getJson, putJson, deleteJson};

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = JWTAuth::fromUser($this->user);
});

test('user can create a savings goal', function () {
    $goalData = [
        'name' => 'New Car',
        'target_amount' => 25000,
        'initial_amount' => 5000,
        'target_date' => now()->addYear()->format('Y-m-d'),
    ];

    $response = postJson('/api/savings-goals', $goalData, [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'target_amount',
                'current_amount',
                'remaining_amount',
                'progress_percentage',
                'target_date',
                'is_completed'
            ]
        ]);

    $this->assertDatabaseHas('savings_goals', [
        'user_id' => $this->user->id,
        'name' => $goalData['name'],
        'target_amount' => $goalData['target_amount'],
        'current_amount' => $goalData['initial_amount'],
    ]);
});

test('user can contribute to savings goal', function () {
    $goal = SavingsGoal::factory()->create([
        'user_id' => $this->user->id,
        'target_amount' => 1000,
        'current_amount' => 0,
    ]);

    Notification::fake();

    $response = postJson("/api/savings-goals/{$goal->id}/contribute", [
        'amount' => 500
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.current_amount', 500)
        ->assertJsonPath('data.progress_percentage', 50);

    $this->assertDatabaseHas('savings_goals', [
        'id' => $goal->id,
        'current_amount' => 500,
    ]);
});

test('user cannot contribute more than remaining amount', function () {
    $goal = SavingsGoal::factory()->create([
        'user_id' => $this->user->id,
        'target_amount' => 1000,
        'current_amount' => 800,
    ]);

    $response = postJson("/api/savings-goals/{$goal->id}/contribute", [
        'amount' => 300
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(422);
});

test('goal is marked as completed when target amount is reached', function () {
    Notification::fake();

    $goal = SavingsGoal::factory()->create([
        'user_id' => $this->user->id,
        'target_amount' => 1000,
        'current_amount' => 900,
        'is_completed' => false,
    ]);

    postJson("/api/savings-goals/{$goal->id}/contribute", [
        'amount' => 100
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $this->assertDatabaseHas('savings_goals', [
        'id' => $goal->id,
        'is_completed' => true,
        'current_amount' => 1000,
    ]);
});

test('user cannot contribute to completed goal', function () {
    $goal = SavingsGoal::factory()->create([
        'user_id' => $this->user->id,
        'target_amount' => 1000,
        'current_amount' => 1000,
        'is_completed' => true,
    ]);

    $response = postJson("/api/savings-goals/{$goal->id}/contribute", [
        'amount' => 100
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(422);
});

test('user cannot access other users savings goals', function () {
    $otherUser = User::factory()->create();
    $goal = SavingsGoal::factory()->create([
        'user_id' => $otherUser->id,
        'target_amount' => 1000,
        'current_amount' => 500,
    ]);

    $response = getJson("/api/savings-goals/{$goal->id}", [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(403);
});

test('notification is sent when savings goal reaches milestone', function () {
    Notification::fake();

    $goal = SavingsGoal::factory()->create([
        'user_id' => $this->user->id,
        'target_amount' => 1000,
        'current_amount' => 240, // 24%
    ]);

    postJson("/api/savings-goals/{$goal->id}/contribute", [
        'amount' => 100
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    Notification::assertSentTo(
        $this->user,
        SavingsGoalMilestoneNotification::class,
        function ($notification) use ($goal) {
            return $notification->getSavingsGoal()->id === $goal->id
                && $notification->getMilestone() === 25;
        }
    );
});

test('notification is sent when savings goal is completed', function () {
    Notification::fake();

    $goal = SavingsGoal::factory()->create([
        'user_id' => $this->user->id,
        'target_amount' => 1000,
        'current_amount' => 900,
    ]);

    postJson("/api/savings-goals/{$goal->id}/contribute", [
        'amount' => 100
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    Notification::assertSentTo(
        $this->user,
        SavingsGoalMilestoneNotification::class,
        function ($notification) use ($goal) {
            return $notification->getSavingsGoal()->id === $goal->id
                && $notification->getMilestone() === 100;
        }
    );
});

test('notification respects user notification settings', function () {
    Notification::fake();

    $this->user->notificationSetting()->update([
        'savings_milestone_email' => false,
        'savings_milestone_database' => false,
    ]);

    $goal = SavingsGoal::factory()->create([
        'user_id' => $this->user->id,
        'target_amount' => 1000,
        'current_amount' => 240, // 24%
    ]);

    postJson("/api/savings-goals/{$goal->id}/contribute", [
        'amount' => 100
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    Notification::assertNothingSent();
});
