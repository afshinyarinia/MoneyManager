<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function notificationSetting()
    {
        return $this->hasOne(NotificationSetting::class);
    }

    protected static function booted()
    {
        static::created(function ($user) {
            $user->notificationSetting()->create([
                'budget_exceeded_email' => true,
                'budget_exceeded_database' => true,
                'savings_milestone_email' => true,
                'savings_milestone_database' => true,
                'savings_milestone_percentage' => 25,
                'recurring_transaction_reminder' => true,
            ]);
        });
    }
}
