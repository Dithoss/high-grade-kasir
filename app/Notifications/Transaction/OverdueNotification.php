<?php

namespace App\Notifications\Transaction;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OverdueNotification extends Notification
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
        $daysOverdue = now()->diffInDays($this->transaction->due_at);
        
        return [
            'type' => 'overdue',
            'title' => 'Pengembalian Terlambat',
            'message' => 'Buku "' . $this->transaction->book->name . '" terlambat ' . $daysOverdue . ' hari. Segera kembalikan!',
            'transaction_id' => $this->transaction->id,
            'book_name' => $this->transaction->book->name,
            'due_at' => $this->transaction->due_at->toDateString(),
            'days_overdue' => $daysOverdue,
            'icon' => 'fa-exclamation-triangle',
            'icon_color' => 'red',
        ];
    }
}