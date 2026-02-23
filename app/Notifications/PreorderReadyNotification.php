<?php

namespace App\Notifications;

use App\Models\Preorder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * EC-14: Notifikasi preorder ready.
 *
 * ShouldQueue → masuk jobs table, retry 3x jika gagal.
 * Status preorder di-update SEBELUM dispatch → gagal notif aman (tidak rollback status).
 *
 * GANTI file lama App\Notifications\PreorderReadyNotification dengan ini.
 */
class PreorderReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(public readonly Preorder $preorder) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'       => '📚 Buku Siap Dipinjam!',
            'message'     => 'Buku "' . ($this->preorder->book?->name ?? 'unknown') . '" sudah tersedia. Konfirmasi dalam 2 hari atau giliran akan dilepas ke antrian berikutnya.',
            'icon'        => 'fa-book-open',
            'icon_color'  => 'green',
            'preorder_id' => $this->preorder->id,
            'book_slug'   => $this->preorder->book?->slug,
            'expired_at'  => $this->preorder->expired_at?->toDateTimeString(),
        ];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('PreorderReadyNotification: Semua retry gagal', [
            'preorder_id' => $this->preorder->id,
            'user_id'     => $this->preorder->user_id,
            'error'       => $exception->getMessage(),
        ]);
    }
}