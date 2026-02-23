<?php

namespace App\Notifications\Transaction;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DueTomorrowNotification extends Notification
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
        $bookName = $this->transaction->items->first()?->book?->name ?? 'Buku';
        
        return [
            'type' => 'due_tomorrow',
            'title' => 'Pengingat: Pengembalian Besok',
            'message' => "Buku \"{$bookName}\" harus dikembalikan besok.",
            'transaction_id' => $this->transaction->id,
            'book_name' => $bookName,
            'due_date' => $this->transaction->due_at?->format('d M Y'),
            'icon' => 'fa-calendar-check',
            'icon_color' => 'blue',
        ];
    }
}