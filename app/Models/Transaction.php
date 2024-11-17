<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\BudgetExceededNotification;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'type',
        'description',
        'transaction_date',
        'is_recurring',
        'recurring_frequency'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'is_recurring' => 'boolean',
        'amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::created(function ($transaction) {
            if ($transaction->type === 'expense') {
                $transaction->checkBudgetExceeded();
            }
        });
    }

    protected function checkBudgetExceeded()
    {
        $activeBudgets = Budget::where('user_id', $this->user_id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $this->transaction_date);
            })
            ->where('start_date', '<=', $this->transaction_date)
            ->get();

        foreach ($activeBudgets as $budget) {
            $spentAmount = $budget->spent_amount;
            
            if ($spentAmount > $budget->amount) {
                $settings = $this->user->notificationSetting;
                
                if ($settings->budget_exceeded_database || $settings->budget_exceeded_email) {
                    $channels = [];
                    if ($settings->budget_exceeded_database) $channels[] = 'database';
                    if ($settings->budget_exceeded_email) $channels[] = 'mail';
                    
                    $this->user->notify(
                        (new BudgetExceededNotification($budget, $spentAmount))
                            ->via($channels)
                    );
                }
            }
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeInDateRange(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeOfType(Builder $query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecurring(Builder $query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeForCategory(Builder $query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public static function getMonthlyTotal($type, $month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return static::ofType($type)
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('amount');
    }
} 