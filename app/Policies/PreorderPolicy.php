<?php

namespace App\Policies;

use App\Models\Preorder;
use App\Models\User;

class PreorderPolicy
{
    public function update(User $user, Preorder $preorder): bool
    {
        return $user->id === $preorder->user_id || $user->hasRole('admin');
    }

    public function cancel(User $user, Preorder $preorder): bool
    {
        return $user->id === $preorder->user_id || $user->hasRole('admin');
    }

    public function confirm(User $user, Preorder $preorder): bool
    {
        return $user->id === $preorder->user_id;
    }
}