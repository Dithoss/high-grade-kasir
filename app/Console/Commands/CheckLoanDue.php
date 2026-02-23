<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Notifications\Transaction\DueSoonNotification;
use App\Notifications\Transaction\DueTomorrowNotification; // ← TAMBAH
use App\Notifications\Transaction\DueTodayNotification;
use App\Notifications\Transaction\OverdueNotification;
use Illuminate\Console\Command;

class CheckLoanDue extends Command
{
    protected $signature = 'library:check-due';
    protected $description = 'Cek jatuh tempo peminjaman buku dan kirim notifikasi';

    public function handle()
    {
        $notificationsSent = [
            'due_soon' => 0,
            'due_tomorrow' => 0, // ← TAMBAH
            'due_today' => 0,
            'overdue' => 0,
        ];

        $transactions = Transaction::whereNull('returned_at')
            ->with(['user', 'items.book'])
            ->get();

        $this->info("📚 Memeriksa {$transactions->count()} transaksi aktif...\n");

        foreach ($transactions as $trx) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("🔍 Transaksi ID: {$trx->id}");
            
            if (!$trx->due_at) {
                $this->warn("⚠ TRX {$trx->id} dilewati (due_at NULL)");
                continue;
            }

            // Hitung selisih hari
            $today = now()->startOfDay();
            $due   = $trx->due_at->copy()->startOfDay();
            $diff  = (int) $today->diffInDays($due, false);
            
            // Debug info detail
            $this->line("📅 Hari ini: {$today->format('Y-m-d')}");
            $this->line("📅 Jatuh tempo: {$due->format('Y-m-d')}");
            $this->line("📊 Selisih hari: {$diff}");
            $this->line("👤 User: {$trx->user->name}");
            $this->line("📕 Buku: " . ($trx->items->first()?->book?->name ?? 'N/A'));

            // Cek apakah sudah ada notifikasi hari ini untuk transaksi ini
            $notificationSentToday = $trx->user->notifications()
                ->whereDate('created_at', $today)
                ->where('data->transaction_id', $trx->id)
                ->exists();

            if ($notificationSentToday) {
                $this->line("ℹ️  Notifikasi sudah dikirim hari ini - SKIP");
                continue;
            }

            // Debug kondisi
            $this->line("\n🎯 Evaluasi kondisi:");
            $this->line("   $diff === 3? " . ($diff === 3 ? 'YES ✓' : 'NO ✗'));
            $this->line("   $diff === 1? " . ($diff === 1 ? 'YES ✓' : 'NO ✗'));
            $this->line("   $diff === 0? " . ($diff === 0 ? 'YES ✓' : 'NO ✗'));
            $this->line("   $diff < 0? " . ($diff < 0 ? 'YES ✓' : 'NO ✗'));

            // Kirim notifikasi sesuai kondisi
            match (true) {
                $diff === 3 => $this->sendDueSoonNotification($trx, $notificationsSent),
                $diff === 1 => $this->sendDueTomorrowNotification($trx, $notificationsSent),
                $diff === 0 => $this->sendDueTodayNotification($trx, $notificationsSent),
                $diff < 0   => $this->sendOverdueNotification($trx, $notificationsSent),
                default     => $this->line("⚪ Tidak masuk kondisi apapun (diff = $diff)"),
            };

            // Update status jadi late jika terlambat
            if ($diff < 0 && $trx->status !== 'late') {
                $trx->update(['status' => 'late']);
                $this->warn("⚠ Status TRX {$trx->id} diubah menjadi 'late'");
            }
        }

        // Tampilkan ringkasan
        $this->newLine();
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 Ringkasan Notifikasi:");
        $this->table(
            ['Tipe', 'Jumlah'],
            [
                ['Due Soon (3 hari)', $notificationsSent['due_soon']],
                ['Due Tomorrow (1 hari)', $notificationsSent['due_tomorrow']], // ← TAMBAH
                ['Due Today', $notificationsSent['due_today']],
                ['Overdue', $notificationsSent['overdue']],
            ]
        );

        return Command::SUCCESS;
    }

    private function sendDueSoonNotification(Transaction $trx, array &$counter): void
    {
        $bookName = $trx->items->first()?->book?->name ?? 'Buku';
        
        $trx->user->notify(new DueSoonNotification($trx));
        $counter['due_soon']++;
        
        $this->line("✅ Notifikasi 'due_soon' → {$trx->user->name} untuk \"{$bookName}\"");
    }

    // ← TAMBAH METHOD BARU
    private function sendDueTomorrowNotification(Transaction $trx, array &$counter): void
    {
        $bookName = $trx->items->first()?->book?->name ?? 'Buku';
        
        $trx->user->notify(new DueTomorrowNotification($trx));
        $counter['due_tomorrow']++;
        
        $this->line("✅ Notifikasi 'due_tomorrow' → {$trx->user->name} untuk \"{$bookName}\"");
    }

    private function sendDueTodayNotification(Transaction $trx, array &$counter): void
    {
        $bookName = $trx->items->first()?->book?->name ?? 'Buku';
        
        $trx->user->notify(new DueTodayNotification($trx));
        $counter['due_today']++;
        
        $this->line("✅ Notifikasi 'due_today' → {$trx->user->name} untuk \"{$bookName}\"");
    }

    private function sendOverdueNotification(Transaction $trx, array &$counter): void
    {
        $bookName = $trx->items->first()?->book?->name ?? 'Buku';
        
        $trx->user->notify(new OverdueNotification($trx));
        $counter['overdue']++;
        
        $this->warn("✅ Notifikasi 'overdue' → {$trx->user->name} untuk \"{$bookName}\"");
    }
}