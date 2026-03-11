<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $user->libraryCard()->create([
            'card_number' => 'CARD-' . strtoupper(Str::random(8)),
            'expired_at' => now()->addYears(3),
            'status' => 'active',
        ]);
    }
}