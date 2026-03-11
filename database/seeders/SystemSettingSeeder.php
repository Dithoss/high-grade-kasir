<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    /**
     * Semua default setting sistem.
     * Dikelompokkan berdasarkan prefix key untuk kemudahan navigasi.
     */
    public function run(): void
    {
        $defaults = [

            // ── 📚 ATURAN PEMINJAMAN ────────────────────────────────────────────
            'borrowing.max_books_per_user'              => '3',
            'borrowing.default_borrow_days'             => '7',
            'borrowing.allow_extension'                 => '1',
            'borrowing.max_extension_count'             => '1',
            'borrowing.extension_days'                  => '7',
            'borrowing.allow_borrow_with_unpaid_fine'   => '0',
            'borrowing.extension_min_days_before_due'   => '1',

            // ── 💰 ATURAN DENDA ─────────────────────────────────────────────────
            'fine.per_day_late'                         => '1000',
            'fine.lost_book'                            => '150000',
            'fine.damaged_book'                         => '50000',
            'fine.max_late_fine'                        => '0',
            'fine.online_payment_enabled'               => '1',
            'fine.cash_payment_enabled'                 => '1',

            // ── 📋 PREORDER / ANTRIAN ───────────────────────────────────────────
            'preorder.enabled'                          => '1',
            'preorder.confirmation_hours'               => '24',
            'preorder.max_active_per_user'              => '3',

            // ── 🔔 NOTIFIKASI ───────────────────────────────────────────────────
            'notification.email_enabled'                => '1',
            'notification.reminder_days_before_due'     => '2',

            // ── 🏛️ INFORMASI PERPUSTAKAAN ───────────────────────────────────────
            'library.name'                              => 'Perpustakaan Digital',
            'library.address'                           => 'Jl. Contoh No. 1, Kota',
            'library.phone'                             => '(021) 000-0000',
            'library.email'                             => 'perpustakaan@example.com',
            'library.open_hours'                        => 'Senin–Jumat, 08:00–16:00',
            'library.logo_path'                         => null,

            // ── ⚙️ SISTEM ───────────────────────────────────────────────────────
            'system.maintenance_mode'                   => '0',
            'system.maintenance_message'                => 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.',
            'system.registration_open'                  => '1',
            'system.max_profile_photo_kb'               => '2048',
        ];

        foreach ($defaults as $key => $value) {
            SystemSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $this->command->info('✅ SystemSetting seeded: ' . count($defaults) . ' settings.');
    }
}