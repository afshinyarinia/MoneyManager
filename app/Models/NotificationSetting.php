<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'budget_exceeded_email',
        'budget_exceeded_database',
        'savings_milestone_email',
        'savings_milestone_database',
        'savings_milestone_percentage',
        'recurring_transaction_reminder',
    ];

    protected $casts = [
        'budget_exceeded_email' => 'boolean',
        'budget_exceeded_database' => 'boolean',
        'savings_milestone_email' => 'boolean',
        'savings_milestone_database' => 'boolean',
        'savings_milestone_percentage' => 'integer',
        'recurring_transaction_reminder' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 