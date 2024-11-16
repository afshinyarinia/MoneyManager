<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'amount',
        'period_type',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSpentAmountAttribute()
    {
        return Transaction::where('user_id', $this->user_id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date ?? now()])
            ->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->spent_amount;
    }
} 