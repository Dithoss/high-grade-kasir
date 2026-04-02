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
        // ── 1. Akun tetap admin & user ────────────────────────────────────────
        $fixed = [
            ['role' => 'admin', 'name' => 'Admin Perpustakaan', 'email' => 'admin@gmail.com'],
            ['role' => 'user',  'name' => 'User Demo',          'email' => 'user@gmail.com'],
        ];

        foreach ($fixed as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'id'       => Str::uuid(),
                    'name'     => $data['name'],
                    'password' => Hash::make('1234'),
                ]
            );

            if (! $user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }

            $this->ensureLibraryCard($user);
        }

        // ── 2. Dummy anggota (role: user) ─────────────────────────────────────
        $members = [
            ['name' => 'Budi Santoso',     'email' => 'budi@gmail.com'],
            ['name' => 'Siti Rahayu',      'email' => 'siti@gmail.com'],
            ['name' => 'Ahmad Fauzi',      'email' => 'ahmad@gmail.com'],
            ['name' => 'Dewi Lestari',     'email' => 'dewi@gmail.com'],
            ['name' => 'Rizky Pratama',    'email' => 'rizky@gmail.com'],
            ['name' => 'Nurhaliza',        'email' => 'nurhaliza@gmail.com'],
            ['name' => 'Eko Wijaya',       'email' => 'eko@gmail.com'],
            ['name' => 'Fitri Handayani',  'email' => 'fitri@gmail.com'],
        ];

        foreach ($members as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'id'       => Str::uuid(),
                    'name'     => $data['name'],
                    'password' => Hash::make('1234'),
                ]
            );

            if (! $user->hasRole('user')) {
                $user->assignRole('user');
            }

            $this->ensureLibraryCard($user);
        }

        $this->command->info('UserSeeder selesai. Total: ' . User::count() . ' user.');
    }

    /**
     * Buat library card jika belum ada.
     */
    private function ensureLibraryCard(User $user): void
    {
        if (! $user->libraryCard()->exists()) {
            $user->libraryCard()->create([
                'card_number' => 'CARD-' . strtoupper(Str::random(8)),
                'expired_at'  => now()->addYears(3),
                'status'      => 'active',
            ]);
        }
    }
}