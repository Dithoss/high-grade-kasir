<?php

namespace App\Observers;

use App\Models\Book;
use App\Models\Preorder;
use App\Models\Transaction;
use App\Notifications\PreorderReadyNotification;
use Illuminate\Support\Str;

class TransactionObserver
{
    private function generateReceipt(Transaction $transaction): string
    {
        $date   = now()->format('Ymd');
        $random = Str::upper(Str::random(6));
        return "TRX-{$date}-{$random}";
    }

    public function generateUniqueSlug(Transaction $transaction): string
    {
        $slug  = Str::slug($transaction->receipt_number ?? Str::random(8));
        $slug .= '-' . date('YmdHis');
        return $slug;
    }

    public function creating(Transaction $transaction): void
    {
        if (empty($transaction->receipt_number)) {
            $transaction->receipt_number = $this->generateReceipt($transaction);
        }
        if (empty($transaction->slug)) {
            $transaction->slug = $this->generateUniqueSlug($transaction);
        }
    }

    public function updated(Transaction $transaction): void
    {
        if (! $transaction->wasChanged('status')) {
            return;
        }

        $oldStatus = $transaction->getOriginal('status');
        $newStatus = $transaction->status;

        // ── Stok BERKURANG saat pending_approval → borrowed (admin approve) ──
        if ($oldStatus === 'pending_approval' && $newStatus === 'borrowed') {
            $this->decrementStock($transaction);
            return;
        }

        // ── Stok KEMBALI saat buku dikembalikan ──────────────────────────────
        $returnStatuses = ['returned', 'damaged', 'lost'];
        $activeStatuses = ['borrowed', 'return_requested', 'extend_requested'];

        $wasActive     = in_array($oldStatus, $activeStatuses);
        $isNowReturned = in_array($newStatus, $returnStatuses);

        if ($wasActive && $isNowReturned) {
            $this->incrementStock($transaction);
            $this->notifyPreorderQueue($transaction);
        }

        // ── Stok dikembalikan jika pending_approval → rejected ───────────────
        // (stok tidak pernah dikurangi saat pending, jadi tidak perlu increment)
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function decrementStock(Transaction $transaction): void
    {
        $transaction->loadMissing('items');

        foreach ($transaction->items as $item) {
            Book::where('id', $item->book_id)
                ->decrement('stock', $item->quantity ?? 1);

            \Log::info("Stock -{$item->quantity} untuk buku {$item->book_id} (approve transaksi {$transaction->id})");
        }
    }

    private function incrementStock(Transaction $transaction): void
    {
        $transaction->loadMissing('items');

        foreach ($transaction->items as $item) {
            Book::where('id', $item->book_id)
                ->increment('stock', $item->quantity ?? 1);

            \Log::info("Stock +{$item->quantity} untuk buku {$item->book_id} (transaksi {$transaction->id})");
        }
    }

    private function notifyPreorderQueue(Transaction $transaction): void
    {
        $transaction->loadMissing('items');

        foreach ($transaction->items as $item) {
            $book = Book::find($item->book_id);
            if (! $book || $book->stock <= 0) continue;

            $nextPreorder = Preorder::where('book_id', $item->book_id)
                ->where('status', 'pending')
                ->orderBy('created_at')
                ->with('user')
                ->first();

            if (! $nextPreorder) continue;

            $nextPreorder->update([
                'status'      => 'ready',
                'notified_at' => now(),
            ]);

            if ($nextPreorder->user) {
                $nextPreorder->user->notify(new PreorderReadyNotification($nextPreorder));
            }

            \Log::info("Preorder {$nextPreorder->id} diset ready untuk buku {$item->book_id}");
        }
    }
}