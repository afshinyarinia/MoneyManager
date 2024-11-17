<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RecurringTransactionReminder extends Notification
{
    use Queueable;

    public function __construct(
        protected Transaction $transaction,
        protected string $dueDate
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Recurring Transaction Due')
            ->line("Your {$this->transaction->recurring_frequency} {$this->transaction->type} of \${$this->transaction->amount} is due.")
            ->line("Description: {$this->transaction->description}")
            ->line("Due Date: {$this->dueDate}")
            ->action('View Transaction', url('/transactions/' . $this->transaction->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'description' => $this->transaction->description,
            'amount' => $this->transaction->amount,
            'type' => $this->transaction->type,
            'due_date' => $this->dueDate,
            'recurring_frequency' => $this->transaction->recurring_frequency,
        ];
    }
} 