<?php

namespace App\Policies;

use App\Models\SavingsGoal;
use App\Models\User;

class SavingsGoalPolicy
{
    public function view(User $user, SavingsGoal $savingsGoal)
    {
        return $user->id === $savingsGoal->user_id;
    }

    public function update(User $user, SavingsGoal $savingsGoal)
    {
        return $user->id === $savingsGoal->user_id;
    }

    public function delete(User $user, SavingsGoal $savingsGoal)
    {
        return $user->id === $savingsGoal->user_id;
    }
} 