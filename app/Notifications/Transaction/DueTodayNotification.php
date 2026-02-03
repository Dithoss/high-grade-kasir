<?php

namespace App\Notifications\Transaction;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DueTodayNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Transaction $transaction
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'due_today',
            'title' => 'Pengembalian Jatuh Tempo Hari Ini',
            'message' => 'Buku "' . $this->transaction->book->name . '" harus dikembalikan hari ini.',
            'transaction_id' => $this->transaction->id,
            'book_name' => $this->transaction->book->name,
            'due_at' => $this->transaction->due_at->toDateString(),
            'icon' => 'fa-exclamation-circle',
            'icon_color' => 'orange',
        ];
    }
}