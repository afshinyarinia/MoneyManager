<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\SavingsGoalMilestoneNotification;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'current_amount',
        'target_date',
        'is_completed'
    ];

    protected $casts = [
        'target_amount' => 'integer',
        'current_amount' => 'integer',
        'target_date' => 'date',
        'is_completed' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProgressPercentageAttribute()
    {
        return round(($this->current_amount / $this->target_amount) * 100, 2);
    }

    public function getRemainingAmountAttribute()
    {
        return $this->target_amount - $this->current_amount;
    }

    public function updateProgress($amount): void
    {
        $oldPercentage = $this->progress_percentage;
        $this->current_amount += $amount;

        if ($this->current_amount >= $this->target_amount) {
            $this->is_completed = true;
            $this->current_amount = $this->target_amount;
            $this->sendNotification(100);
        } else {
            $newPercentage = $this->progress_percentage;
            $milestoneStep = $this->user->notificationSetting->savings_milestone_percentage;

            // Check if we've crossed a milestone
            $oldMilestone = floor($oldPercentage / $milestoneStep) * $milestoneStep;
            $newMilestone = floor($newPercentage / $milestoneStep) * $milestoneStep;

            if ($newMilestone > $oldMilestone) {
                $this->sendNotification($newMilestone);
            }
        }

        $this->save();
    }

    protected function sendNotification($milestone): void
    {
        $settings = $this->user->notificationSetting;

        if ($settings->savings_milestone_database || $settings->savings_milestone_email) {
            $channels = [];
            if ($settings->savings_milestone_database) $channels[] = 'database';
            if ($settings->savings_milestone_email) $channels[] = 'mail';

            $notification = new SavingsGoalMilestoneNotification($this, $milestone);
            $notification->via = $channels;
            
            $this->user->notify($notification);
        }
    }
}
