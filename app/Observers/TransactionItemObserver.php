<?php

namespace App\Observers;

use App\Models\TransactionItem;
use App\Models\Book;
use Illuminate\Support\Facades\Log;

class TransactionItemObserver
{
    /**
     * Saat item transaksi dibuat → kurangi stok buku
     * Ini dipanggil setiap kali satu baris TransactionItem disimpan
     */
    public function created(TransactionItem $item): void
    {
        $qty = $item->quantity ?? 1;

        Book::where('id', $item->book_id)
            ->where('stock', '>=', $qty) // jangan sampai minus
            ->decrement('stock', $qty);

        Log::info("Stock -{$qty} untuk buku {$item->book_id} (item transaksi {$item->id})");
    }

    /**
     * Jika item dihapus sebelum transaksi selesai → kembalikan stok
     */
    public function deleted(TransactionItem $item): void
    {
        // Hanya kembalikan stok jika transaksi belum returned/damaged/lost
        $transaction = $item->transaction;
        if (! $transaction) return;

        $finalStatuses = ['returned', 'damaged', 'lost'];
        if (in_array($transaction->status, $finalStatuses)) return;

        $qty = $item->quantity ?? 1;

        Book::where('id', $item->book_id)
            ->increment('stock', $qty);

        Log::info("Stock +{$qty} (item dihapus) untuk buku {$item->book_id}");
    }
}