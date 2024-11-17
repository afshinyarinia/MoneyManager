<?php

use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;
use App\Notifications\RecurringTransactionReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use function Pest\Laravel\artisan;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create(['user_id' => $this->user->id]);
});

test('reminders are sent for transactions due within 24 hours', function () {
    Notification::fake();

    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => true,
        'recurring_frequency' => 'monthly',
        'transaction_date' => now()->subMonth(),
    ]);

    artisan('reminders:recurring-transactions');

    Notification::assertSentTo(
        $this->user,
        RecurringTransactionReminder::class,
        function ($notification) use ($transaction) {
            return $notification->getTransaction()->id === $transaction->id;
        }
    );
});

test('reminders are not sent for non-recurring transactions', function () {
    Notification::fake();

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => false,
        'transaction_date' => now()->subMonth(),
    ]);

    artisan('reminders:recurring-transactions');

    Notification::assertNothingSent();
});

test('reminders respect user notification settings', function () {
    Notification::fake();

    $this->user->notificationSetting()->update([
        'recurring_transaction_reminder' => false
    ]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'is_recurring' => true,
        'recurring_frequency' => 'monthly',
        'transaction_date' => now()->subMonth(),
    ]);

    artisan('reminders:recurring-transactions');

    Notification::assertNothingSent();
});

test('reminders are sent with correct due date for different frequencies', function () {
    $frequencies = [
        'daily' => now()->subDay(),
        'weekly' => now()->subWeek(),
        'monthly' => now()->subMonth(),
        'yearly' => now()->subYear(),
    ];

    foreach ($frequencies as $frequency => $date) {
        Notification::fake();

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'is_recurring' => true,
            'recurring_frequency' => $frequency,
            'transaction_date' => $date,
        ]);

        artisan('reminders:recurring-transactions');

        Notification::assertSentTo(
            $this->user,
            RecurringTransactionReminder::class,
            function ($notification) use ($transaction) {
                return $notification->getTransaction()->id === $transaction->id;
            }
        );
    }
});

test('old notifications are cleaned up correctly', function () {
    // Create some old read notifications
    $this->user->notifications()->create([
        'id' => uuid_create(),
        'type' => 'Test',
        'data' => [],
        'read_at' => now()->subDays(40),
        'created_at' => now()->subDays(40),
    ]);

    // Create a recent read notification
    $this->user->notifications()->create([
        'id' => uuid_create(),
        'type' => 'Test',
        'data' => [],
        'read_at' => now()->subDays(10),
        'created_at' => now()->subDays(10),
    ]);

    // Create an old unread notification
    $this->user->notifications()->create([
        'id' => uuid_create(),
        'type' => 'Test',
        'data' => [],
        'created_at' => now()->subDays(40),
    ]);

    artisan('notifications:cleanup --days=30');

    $this->assertDatabaseCount('notifications', 2);
}); 