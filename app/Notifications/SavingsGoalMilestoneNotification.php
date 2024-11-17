<?php

namespace App\Notifications;

use App\Models\SavingsGoal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SavingsGoalMilestoneNotification extends Notification
{
    use Queueable;

    public array $via = ['mail', 'database'];

    public function __construct(
        protected SavingsGoal $savingsGoal,
        protected int $milestone
    ) {}

    public function via($notifiable): array
    {
        return $this->via;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Savings Goal Milestone Reached!')
            ->line("Congratulations! You've reached {$this->milestone}% of your savings goal '{$this->savingsGoal->name}'.")
            ->line("Current amount: $" . number_format($this->savingsGoal->current_amount, 2))
            ->line("Target amount: $" . number_format($this->savingsGoal->target_amount, 2))
            ->action('View Goal', url('/savings-goals/' . $this->savingsGoal->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'savings_goal_id' => $this->savingsGoal->id,
            'savings_goal_name' => $this->savingsGoal->name,
            'milestone_percentage' => $this->milestone,
            'current_amount' => $this->savingsGoal->current_amount,
            'target_amount' => $this->savingsGoal->target_amount,
            'type' => 'savings_milestone'
        ];
    }

    public function getSavingsGoal(): SavingsGoal
    {
        return $this->savingsGoal;
    }

    public function getMilestone(): int
    {
        return $this->milestone;
    }
} 