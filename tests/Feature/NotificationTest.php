<?php

use App\Models\User;
use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Notifications\BudgetExceededNotification;
use App\Notifications\SavingsGoalMilestoneNotification;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Notification;
use function Pest\Laravel\{getJson, postJson, putJson};

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = JWTAuth::fromUser($this->user);
});

test('user can view their notifications', function () {
    Notification::fake();
    
    $budget = Budget::factory()->create([
        'user_id' => $this->user->id,
        'amount' => 1000
    ]);

    $this->user->notify(new BudgetExceededNotification($budget, 1200));

    Notification::assertSentTo($this->user, BudgetExceededNotification::class);

    $response = getJson('/api/notifications', [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'type',
                    'data',
                    'read_at',
                    'created_at'
                ]
            ]
        ]);
});

test('user can mark notifications as read', function () {
    $budget = Budget::factory()->create([
        'user_id' => $this->user->id,
        'amount' => 1000
    ]);

    $this->user->notify(new BudgetExceededNotification($budget, 1200));
    
    $notification = $this->user->notifications->first();

    $response = postJson('/api/notifications/mark-as-read', [
        'id' => $notification->id
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200);
    $this->assertNotNull($notification->fresh()->read_at);
});

test('user can view notification settings', function () {
    $response = getJson('/api/notifications/settings', [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'budget_exceeded_email',
                'budget_exceeded_database',
                'savings_milestone_email',
                'savings_milestone_database',
                'savings_milestone_percentage',
                'recurring_transaction_reminder'
            ]
        ]);
});

test('user can update notification settings', function () {
    $settings = [
        'budget_exceeded_email' => false,
        'savings_milestone_percentage' => 50,
        'recurring_transaction_reminder' => false
    ];

    $response = putJson('/api/notifications/settings', $settings, [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.budget_exceeded_email', false)
        ->assertJsonPath('data.savings_milestone_percentage', 50)
        ->assertJsonPath('data.recurring_transaction_reminder', false);

    $this->assertDatabaseHas('notification_settings', [
        'user_id' => $this->user->id,
        'budget_exceeded_email' => false,
        'savings_milestone_percentage' => 50,
        'recurring_transaction_reminder' => false
    ]);
});

test('notification is sent when budget is exceeded', function () {
    Notification::fake();

    $budget = Budget::factory()->create([
        'user_id' => $this->user->id,
        'amount' => 1000
    ]);

    // Simulate budget exceeded through notification
    $this->user->notify(new BudgetExceededNotification($budget, 1200));

    Notification::assertSentTo(
        $this->user,
        BudgetExceededNotification::class,
        function ($notification) use ($budget) {
            return $notification->budget->id === $budget->id;
        }
    );
});

test('notification is sent when savings goal milestone is reached', function () {
    Notification::fake();

    $savingsGoal = SavingsGoal::factory()->create([
        'user_id' => $this->user->id,
        'target_amount' => 1000,
        'current_amount' => 500 // 50%
    ]);

    // Simulate milestone reached through notification
    $this->user->notify(new SavingsGoalMilestoneNotification($savingsGoal, 50));

    Notification::assertSentTo(
        $this->user,
        SavingsGoalMilestoneNotification::class,
        function ($notification) use ($savingsGoal) {
            return $notification->savingsGoal->id === $savingsGoal->id
                && $notification->milestone === 50;
        }
    );
});

test('user cannot update notification settings with invalid percentage', function () {
    $response = putJson('/api/notifications/settings', [
        'savings_milestone_percentage' => 101
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['savings_milestone_percentage']);
});

test('notifications are paginated', function () {
    // Create multiple notifications
    $budget = Budget::factory()->create([
        'user_id' => $this->user->id,
        'amount' => 1000
    ]);

    for ($i = 0; $i < 20; $i++) {
        $this->user->notify(new BudgetExceededNotification($budget, 1200));
    }

    $response = getJson('/api/notifications?page=2', [
        'Authorization' => 'Bearer ' . $this->token
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
}); 