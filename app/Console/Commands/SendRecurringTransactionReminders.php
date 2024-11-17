<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Notifications\RecurringTransactionReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendRecurringTransactionReminders extends Command
{
    protected $signature = 'reminders:recurring-transactions';
    protected $description = 'Send reminders for upcoming recurring transactions';

    public function handle()
    {
        $this->info('Checking for upcoming recurring transactions...');

        $recurringTransactions = Transaction::with(['user', 'user.notificationSetting'])
            ->where('is_recurring', true)
            ->get();

        foreach ($recurringTransactions as $transaction) {
            if (!$transaction->user->notificationSetting->recurring_transaction_reminder) {
                continue;
            }

            $nextDueDate = $this->calculateNextDueDate($transaction);
            
            // If the next due date is within the next 24 hours
            if ($nextDueDate->diffInHours(now()) <= 24) {
                $transaction->user->notify(
                    new RecurringTransactionReminder($transaction, $nextDueDate->format('Y-m-d'))
                );
                $this->info("Reminder sent for transaction ID: {$transaction->id}");
            }
        }

        $this->info('Finished sending recurring transaction reminders.');
    }

    protected function calculateNextDueDate(Transaction $transaction): Carbon
    {
        $lastDate = $transaction->transaction_date;
        $now = now();

        while ($lastDate <= $now) {
            $lastDate = match ($transaction->recurring_frequency) {
                'daily' => $lastDate->addDay(),
                'weekly' => $lastDate->addWeek(),
                'monthly' => $lastDate->addMonth(),
                'yearly' => $lastDate->addYear(),
            };
        }

        return $lastDate;
    }
} 