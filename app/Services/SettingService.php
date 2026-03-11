<?php

namespace App\Services;

use App\Models\SystemSetting;

class SettingService
{
   
    public function maxBooksPerUser(): int
    {
        return SystemSetting::getInt('borrowing.max_books_per_user', 3);
    }

    /** Durasi peminjaman default (dalam hari) */
    public function defaultBorrowDays(): int
    {
        return SystemSetting::getInt('borrowing.default_borrow_days', 7);
    }

    /** Apakah perpanjangan diizinkan? */
    public function isExtensionAllowed(): bool
    {
        return SystemSetting::getBool('borrowing.allow_extension', true);
    }

    /** Maksimal berapa kali user boleh memperpanjang per transaksi */
    public function maxExtensionCount(): int
    {
        return SystemSetting::getInt('borrowing.max_extension_count', 1);
    }

    /** Durasi perpanjangan (dalam hari) */
    public function extensionDays(): int
    {
        return SystemSetting::getInt('borrowing.extension_days', 7);
    }
/** Apakah peminjaman memerlukan persetujuan admin? */
    public function requireAdminApproval(): bool
    {
        return SystemSetting::getBool('borrowing.require_admin_approval', false);
    }
    /**
     * Apakah user dengan denda aktif boleh meminjam?
     * Jika false → user harus lunasi denda dulu
     */
    public function allowBorrowIfHasUnpaidFine(): bool
    {
        return SystemSetting::getBool('borrowing.allow_borrow_with_unpaid_fine', false);
    }

    /** Minimal hari sebelum jatuh tempo untuk mengajukan perpanjangan */
    public function extensionMinDaysBeforeDue(): int
    {
        return SystemSetting::getInt('borrowing.extension_min_days_before_due', 1);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 💰 ATURAN DENDA (Fine Rules)
    // ═══════════════════════════════════════════════════════════════════════════

    /** Denda per hari keterlambatan (rupiah) */
    public function finePerDayLate(): int
    {
        return SystemSetting::getInt('fine.per_day_late', 1000);
    }

    /** Denda buku hilang (rupiah) */
    public function fineLostBook(): int
    {
        return SystemSetting::getInt('fine.lost_book', 150000);
    }

    /** Denda buku rusak (rupiah) */
    public function fineDamagedBook(): int
    {
        return SystemSetting::getInt('fine.damaged_book', 50000);
    }

    /** Maksimal akumulasi denda keterlambatan (rupiah, 0 = tidak terbatas) */
    public function fineMaxLate(): int
    {
        return SystemSetting::getInt('fine.max_late_fine', 0);
    }

    /** Apakah pembayaran online (Tripay) diaktifkan? */
    public function isOnlinePaymentEnabled(): bool
    {
        return SystemSetting::getBool('fine.online_payment_enabled', true);
    }

    /** Apakah pembayaran tunai (cash/offline) diaktifkan? */
    public function isCashPaymentEnabled(): bool
    {
        return SystemSetting::getBool('fine.cash_payment_enabled', true);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 📋 PREORDER / ANTRIAN
    // ═══════════════════════════════════════════════════════════════════════════

    /** Apakah fitur preorder diaktifkan? */
    public function isPreorderEnabled(): bool
    {
        return SystemSetting::getBool('preorder.enabled', true);
    }

    /** Berapa jam user punya waktu untuk konfirmasi preorder setelah notifikasi */
    public function preorderConfirmationHours(): int
    {
        return SystemSetting::getInt('preorder.confirmation_hours', 24);
    }

    /** Maksimal preorder aktif per user (0 = tidak terbatas) */
    public function maxActivePreordersPerUser(): int
    {
        return SystemSetting::getInt('preorder.max_active_per_user', 3);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 🔔 NOTIFIKASI
    // ═══════════════════════════════════════════════════════════════════════════

    /** Apakah notifikasi email diaktifkan? */
    public function isEmailNotificationEnabled(): bool
    {
        return SystemSetting::getBool('notification.email_enabled', true);
    }

    /** Berapa hari sebelum jatuh tempo kirim reminder? */
    public function reminderDaysBeforeDue(): int
    {
        return SystemSetting::getInt('notification.reminder_days_before_due', 2);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // 🏛️ INFORMASI PERPUSTAKAAN
    // ═══════════════════════════════════════════════════════════════════════════

    public function libraryName(): string
    {
        return SystemSetting::get('library.name', 'Perpustakaan');
    }

    public function libraryAddress(): string
    {
        return SystemSetting::get('library.address', '');
    }

    public function libraryPhone(): string
    {
        return SystemSetting::get('library.phone', '');
    }

    public function libraryEmail(): string
    {
        return SystemSetting::get('library.email', '');
    }

    public function libraryOpenHours(): string
    {
        return SystemSetting::get('library.open_hours', '08:00 - 16:00');
    }

    public function libraryLogoPath(): ?string
    {
        return SystemSetting::get('library.logo_path');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // ⚙️ SISTEM
    // ═══════════════════════════════════════════════════════════════════════════

    /** Mode maintenance (true = semua user non-admin tidak bisa akses) */
    public function isMaintenanceMode(): bool
    {
        return SystemSetting::getBool('system.maintenance_mode', false);
    }

    /** Pesan maintenance yang ditampilkan ke user */
    public function maintenanceMessage(): string
    {
        return SystemSetting::get('system.maintenance_message', 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.');
    }

    /** Apakah registrasi user baru diizinkan? */
    public function isRegistrationOpen(): bool
    {
        return SystemSetting::getBool('system.registration_open', true);
    }

    /** Maksimal upload foto profil (dalam KB) */
    public function maxProfilePhotoKb(): int
    {
        return SystemSetting::getInt('system.max_profile_photo_kb', 2048);
    }
}