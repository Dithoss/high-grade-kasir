<?php

namespace App\Observers;

use App\Models\Book;
use App\Models\Preorder;
use App\Notifications\PreorderBookCancelledNotification;
use App\Notifications\PreorderReadyNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookObserver
{
    public function generateUniqueSlug(Book $Book): string
    {
        $slug = Str::slug($Book->name);
        $slug .= '-' . date('YmdHis');
        return $slug;
    }
    public function creating(Book $Book)
    {
        if (empty($Book->slug)) {
            $Book->slug = $this->generateUniqueSlug($Book);
        }
        if (empty($Book->barcode)){
            $Book->barcode = $this->generateBarcode($Book);
        }
    }

    public function updating(Book $Book)
    {
        if ($Book->isDirty('name') || empty($Book->slug)) {
            $Book->slug = $this->generateUniqueSlug($Book);
        }
    }
    protected function generateBarcode(): string
    {
        return 'BK-' . random_int(10000000, 99999999);
    }
    /**
     * EC-11: Stok buku naik → notify sejumlah antrian yang bisa dilayani.
     *
     * Contoh: stok naik dari 0 ke 3 → 3 antrian pertama set 'ready' + notif.
     * Contoh: stok naik dari 2 ke 5 → 3 slot baru → 3 antrian berikutnya notif.
     */
    public function updated(Book $book): void
    {
        $oldStock = (int) $book->getOriginal('stock');
        $newStock = (int) $book->stock;

        if ($newStock <= $oldStock || $newStock <= 0) {
            return;
        }

        $newSlots = $newStock - max(0, $oldStock);

        DB::transaction(function () use ($book, $newSlots) {
            $waitingQueue = Preorder::with('user')
                ->where('book_id', $book->id)
                ->where('status', 'waiting')
                ->orderBy('queue_position')
                ->limit($newSlots)
                ->lockForUpdate()
                ->get();

            if ($waitingQueue->isEmpty()) {
                return;
            }

            foreach ($waitingQueue as $preorder) {
                $preorder->update([
                    'status'      => 'ready',
                    'notified_at' => now(),
                    'expired_at'  => now()->addDays(2),
                ]);

                // EC-14: ShouldQueue → retry otomatis jika gagal
                if ($preorder->user) {
                    $preorder->user->notify(new PreorderReadyNotification($preorder));
                }
            }

            \Log::info("BookObserver@updated: {$waitingQueue->count()} preorder set ready — book #{$book->id} stok {$oldStock}→{$book->stock}");
        });
    }

    /**
     * EC-10 + EC-17: Buku di-soft-delete → cancel semua antrian aktif + notif massal.
     */
    public function deleted(Book $book): void
    {
        $this->cancelAllPreorders($book, 'deleted');
    }

    /**
     * EC-17: Buku force-deleted (hilang permanen) → cancel massal + notif.
     */
    public function forceDeleted(Book $book): void
    {
        $this->cancelAllPreorders($book, 'lost');
    }

    // ─────────────────────────────────────────────────────────────
    private function cancelAllPreorders(Book $book, string $reason): void
    {
        DB::transaction(function () use ($book, $reason) {
            $actives = Preorder::with('user')
                ->where('book_id', $book->id)
                ->whereIn('status', ['waiting', 'ready'])
                ->get();

            if ($actives->isEmpty()) {
                return;
            }

            // Batch cancel lebih efisien daripada loop update
            Preorder::where('book_id', $book->id)
                ->whereIn('status', ['waiting', 'ready'])
                ->update(['status' => 'cancelled']);

            // EC-14: Notif via queue — tidak blocking meski 100+ user
            foreach ($actives as $preorder) {
                if ($preorder->user) {
                    $preorder->user->notify(
                        new PreorderBookCancelledNotification($book->name, $reason)
                    );
                }
            }

            \Log::info("BookObserver@{$reason}: {$actives->count()} preorder cancelled — book #{$book->id} ({$book->name})");
        });
    }
}
