<?php

namespace App\Notifications;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BudgetExceededNotification extends Notification
{
    use Queueable;

    public array $via = ['mail', 'database'];

    public function __construct(
        protected Budget $budget,
        protected float $spentAmount
    ) {}

    public function via($notifiable): array
    {
        return $this->via;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Budget Limit Exceeded')
            ->line("Your budget '{$this->budget->name}' has been exceeded.")
            ->line("Budget limit: $" . number_format($this->budget->amount, 2))
            ->line("Current spending: $" . number_format($this->spentAmount, 2))
            ->action('View Budget', url('/budgets/' . $this->budget->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'budget_id' => $this->budget->id,
            'budget_name' => $this->budget->name,
            'budget_amount' => $this->budget->amount,
            'spent_amount' => $this->spentAmount,
            'type' => 'budget_exceeded'
        ];
    }

    public function getBudget(): Budget
    {
        return $this->budget;
    }

    public function getSpentAmount(): float
    {
        return $this->spentAmount;
    }
} 