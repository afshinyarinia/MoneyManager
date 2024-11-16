<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function update(User $user, Category $category)
    {
        return $user->id === $category->user_id && !$category->is_system;
    }

    public function delete(User $user, Category $category)
    {
        return $user->id === $category->user_id && !$category->is_system;
    }
} 