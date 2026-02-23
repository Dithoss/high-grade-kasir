<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * EC-17: Notifikasi massal saat buku hilang / dihapus admin.
 *
 * Dispatch via queue sehingga tidak blocking request admin
 * meski ada ratusan user dalam antrian.
 */
class PreorderBookCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $backoff = 60;

    /**
     * @param string $bookName  Nama buku
     * @param string $reason    'lost' | 'deleted' | 'damaged'
     */
    public function __construct(
        public readonly string $bookName,
        public readonly string $reason = 'lost'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $detail = match ($this->reason) {
            'lost'    => 'Buku dilaporkan hilang oleh perpustakaan.',
            'deleted' => 'Buku dihapus dari koleksi perpustakaan.',
            'damaged' => 'Buku mengalami kerusakan berat dan tidak dapat dipinjam.',
            default   => 'Buku tidak tersedia.',
        };

        return [
            'title'     => '❌ Preorder Dibatalkan',
            'message'   => "Preorder Anda untuk buku \"{$this->bookName}\" telah dibatalkan. {$detail}",
            'icon'      => 'fa-times-circle',
            'icon_color'=> 'red',
        ];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('PreorderBookCancelledNotification: retry gagal', [
            'book'  => $this->bookName,
            'error' => $exception->getMessage(),
        ]);
    }
}