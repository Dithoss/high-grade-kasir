<?php

namespace App\Console\Commands;

use App\Models\Preorder;
use App\Notifications\PreorderExpiredNotification;
use App\Notifications\PreorderReadyNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * EC-8: Auto-expire preorder yang melewati batas konfirmasi.
 *
 * Daftarkan di Kernel.php:
 *   $schedule->command('preorders:expire')->hourly()->withoutOverlapping();
 *
 * Cara kerja:
 *   1. Cari semua preorder status='ready' yang expired_at <= now()
 *   2. Set status → 'expired'
 *   3. Geser antrian di bawahnya naik
 *   4. Notify user berikutnya (jika stok tersedia)
 *   5. Kirim notif 'expired' ke user bersangkutan via queue (EC-14)
 */
class ExpirePreorders extends Command
{
    protected $signature   = 'preorders:expire {--dry-run : Tampilkan yang akan diexpire tanpa mengubah data}';
    protected $description = 'Auto-expire preorder yang tidak dikonfirmasi dalam batas waktu (EC-8)';

    public function handle(): int
    {
        $expiring = Preorder::with(['user', 'book'])
            ->where('status', 'ready')
            ->where('expired_at', '<=', now())
            ->get();

        if ($expiring->isEmpty()) {
            $this->info('[' . now()->format('Y-m-d H:i') . '] Tidak ada preorder yang expired.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn("DRY RUN — {$expiring->count()} preorder akan di-expire:");
            foreach ($expiring as $p) {
                $this->line("  - #{$p->id} | {$p->book?->name} | {$p->user?->name} | expired_at: {$p->expired_at}");
            }
            return self::SUCCESS;
        }

        $expiredCount = 0;
        $notifiedNext = 0;

        foreach ($expiring as $preorder) {
            DB::transaction(function () use ($preorder, &$expiredCount, &$notifiedNext) {
                $bookId            = $preorder->book_id;
                $cancelledPosition = $preorder->queue_position;

                // 1. Tandai expired
                $preorder->update(['status' => 'expired']);

                // 2. Geser posisi antrian menunggu
                Preorder::where('book_id', $bookId)
                    ->where('status', 'waiting')
                    ->where('queue_position', '>', $cancelledPosition)
                    ->decrement('queue_position');

                // 3. EC-14: Notif ke user yang expired — via queue, retry otomatis
                if ($preorder->user) {
                    $preorder->user->notify(new PreorderExpiredNotification($preorder));
                }

                // 4. Notify user berikutnya jika stok > 0
                $book = $preorder->book;
                if ($book && $book->stock > 0) {
                    $next = Preorder::with('user')
                        ->where('book_id', $bookId)
                        ->where('status', 'waiting')
                        ->orderBy('queue_position')
                        ->lockForUpdate()
                        ->first();

                    if ($next) {
                        $next->update([
                            'status'      => 'ready',
                            'notified_at' => now(),
                            'expired_at'  => now()->addDays(2),
                        ]);

                        if ($next->user) {
                            $next->user->notify(new PreorderReadyNotification($next));
                            $notifiedNext++;
                        }
                    }
                }

                $expiredCount++;
            });
        }

        $this->info("[" . now()->format('Y-m-d H:i') . "] Expired: {$expiredCount} | Next notified: {$notifiedNext}");
        Log::info("preorders:expire — expired={$expiredCount}, notified_next={$notifiedNext}");

        return self::SUCCESS;
    }
}