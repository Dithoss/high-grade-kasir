<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'user'];

        foreach ($roles as $roleName) {
            $user = User::firstOrCreate(
                ['email' => $roleName . '@gmail.com'],
                [
                    'id' => Str::uuid(),
                    'name' => ucfirst($roleName),
                    'password' => Hash::make('1234'),
                ]
            );

            if (! $user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }

            if (! $user->libraryCard()->exists()) {
                $user->libraryCard()->create([
                    'card_number' => 'CARD-' . strtoupper(Str::random(8)),
                    'expired_at' => now()->addYears(3),
                    'status' => 'active',
                ]);
            }
        }
    }
}