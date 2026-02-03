<?php

namespace App\Notifications\Transaction;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DueSoonNotification extends Notification
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
            'type' => 'due_soon',
            'title' => 'Pengingat: Batas Pengembalian Segera',
            'message' => 'Buku "' . $this->transaction->book->name . '" akan jatuh tempo dalam 3 hari.',
            'transaction_id' => $this->transaction->id,
            'book_name' => $this->transaction->book->name,
            'due_at' => $this->transaction->due_at->toDateString(),
            'icon' => 'fa-clock',
            'icon_color' => 'amber',
        ];
    }
}