<?php

namespace App\Notifications\Transaction;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Notifikasi saat buku berhasil dikembalikan oleh user.
 */
class ReturnedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $backoff = 60;

    /**
     * @param string $bookName    Nama buku
     * @param string $returnedAt  Tanggal dikembalikan (formatted string)
     */
    public function __construct(
        public readonly string $bookName,
        public readonly string $returnedAt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'      => '✅ Buku Dikembalikan',
            'message'    => "Buku \"{$this->bookName}\" berhasil dikembalikan pada {$this->returnedAt}. Terima kasih!",
            'icon'       => 'fa-check-circle',
            'icon_color' => 'green',
        ];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ReturnedNotification: retry gagal', [
            'book'  => $this->bookName,
            'error' => $exception->getMessage(),
        ]);
    }
}