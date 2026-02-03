<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Notifications\Transaction\DueSoonNotification;
use App\Notifications\Transaction\DueTodayNotification;
use App\Notifications\Transaction\OverdueNotification;
use Illuminate\Console\Command;

class CheckLoanDue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'library:check-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek jatuh tempo peminjaman buku dan kirim notifikasi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $transactions = Transaction::whereNull('returned_at')
    ->with(['user', 'items.book'])
    ->get();

foreach ($transactions as $trx) {

    if (!$trx->due_at) {
        $this->warn("TRX {$trx->id} dilewati (due_at NULL)");
        continue;
    }

    $today = now()->startOfDay();
    $due   = $trx->due_at->startOfDay();
    $diff  = $today->diffInDays($due, false);

    $notificationSentToday = $trx->user->notifications()
        ->whereDate('created_at', $today)
        ->where('data->transaction_id', $trx->id)
        ->exists();

    if ($notificationSentToday) {
        continue;
    }

    match (true) {
        $diff === 3 => $this->sendDueSoonNotification($trx, $notificationsSent),
        $diff === 0 => $this->sendDueTodayNotification($trx, $notificationsSent),
        $diff < 0   => $this->sendOverdueNotification($trx, $notificationsSent),
        default     => null,
    };

    if ($diff < 0 && $trx->status !== 'late') {
        $trx->update(['status' => 'late']);
    }
}

    }

    private function sendDueSoonNotification(Transaction $trx, array &$counter): void
    {
        $trx->user->notify(new DueSoonNotification($trx));
        $counter['due_soon']++;
        $this->line("✓ Notifikasi 'due_soon' dikirim ke {$trx->user->name} untuk buku '{$trx->book->name}'");
    }

    private function sendDueTodayNotification(Transaction $trx, array &$counter): void
    {
        $trx->user->notify(new DueTodayNotification($trx));
        $counter['due_today']++;
        $this->line("✓ Notifikasi 'due_today' dikirim ke {$trx->user->name} untuk buku '{$trx->book->name}'");
    }

    private function sendOverdueNotification(Transaction $trx, array &$counter): void
    {
        $trx->user->notify(new OverdueNotification($trx));
        $counter['overdue']++;
        $this->warn("⚠ Notifikasi 'overdue' dikirim ke {$trx->user->name} untuk buku '{$trx->book->name}'");
    }
}