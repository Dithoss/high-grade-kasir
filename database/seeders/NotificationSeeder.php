<?php

namespace Database\Seeders;

use App\Models\Preorder;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::role('user')->with(['transactions', 'transactions.items.book'])->get();

        if ($users->isEmpty()) {
            $this->command->warn('Tidak ada user. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        foreach ($users as $user) {
            if ($user->email === 'user@gmail.com') {
                $this->seedAllTransactionNotifications($user);
            } else {
                $this->seedTransactionNotifications($user);
            }

            $this->seedPreorderNotifications($user);
        }

        $this->command->info('NotificationSeeder selesai.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Demo User — paksa semua skenario notifikasi
    // ─────────────────────────────────────────────────────────────────────────

    private function seedAllTransactionNotifications(User $user): void
    {
        // Ambil nama buku dari transaksi user, fallback ke nama dummy
        $transactions = Transaction::where('user_id', $user->id)
            ->with(['items.book'])
            ->get();

        $bookNames = $transactions
            ->flatMap(fn ($trx) => $trx->items->map(fn ($item) => $item->book?->name))
            ->filter()
            ->values();

        $pick = fn () => $bookNames->isNotEmpty()
            ? $bookNames->random()
            : 'Buku Contoh';

        // 1. returned
        $this->insertNotification($user->id, 'returned', [
            'book_name'   => $b = $pick(),
            'returned_at' => Carbon::now()->subDays(2)->toDateTimeString(),
            'message'     => "Buku \"{$b}\" berhasil dikembalikan. Terima kasih!",
        ], Carbon::now()->subDays(2));

        // 2. overdue
        $this->insertNotification($user->id, 'overdue', [
            'book_name' => $b = $pick(),
            'due_at'    => Carbon::now()->subDays(5)->toDateTimeString(),
            'days_late' => 5,
            'message'   => "Peminjaman \"{$b}\" terlambat 5 hari. Segera kembalikan!",
        ], Carbon::now()->subDays(5));

        // 3. due_soon
        $this->insertNotification($user->id, 'due_soon', [
            'book_name' => $b = $pick(),
            'due_at'    => Carbon::now()->addDays(3)->toDateTimeString(),
            'days_left' => 3,
            'message'   => "Peminjaman \"{$b}\" jatuh tempo dalam 3 hari.",
        ], Carbon::now()->subHours(3));

        // 4. due_tomorrow
        $this->insertNotification($user->id, 'due_tomorrow', [
            'book_name' => $b = $pick(),
            'due_at'    => Carbon::now()->addDay()->toDateTimeString(),
            'days_left' => 1,
            'message'   => "Peminjaman \"{$b}\" jatuh tempo besok!",
        ], Carbon::now()->subHours(2));

        // 5. due_today
        $this->insertNotification($user->id, 'due_today', [
            'book_name' => $b = $pick(),
            'due_at'    => Carbon::now()->toDateTimeString(),
            'days_left' => 0,
            'message'   => "Peminjaman \"{$b}\" jatuh tempo hari ini!",
        ], Carbon::now()->subHour());

        // 6. preorder_ready
        $this->insertNotification($user->id, 'preorder_ready', [
            'book_name'  => $b = $pick(),
            'expired_at' => Carbon::now()->addDays(3)->toDateTimeString(),
            'message'    => "Buku \"{$b}\" sudah tersedia! Segera konfirmasi sebelum " . Carbon::now()->addDays(3)->format('d M Y') . '.',
        ], Carbon::now()->subHours(5));

        // 7. preorder_expired
        $this->insertNotification($user->id, 'preorder_expired', [
            'book_name' => $b = $pick(),
            'message'   => "Preorder buku \"{$b}\" telah kedaluwarsa karena tidak dikonfirmasi.",
        ], Carbon::now()->subDays(1));

        // 8. preorder_cancelled
        $this->insertNotification($user->id, 'preorder_cancelled', [
            'book_name' => $b = $pick(),
            'reason'    => 'lost',
            'message'   => "Preorder Anda untuk buku \"{$b}\" telah dibatalkan. Buku dilaporkan hilang oleh perpustakaan.",
        ], Carbon::now()->subDays(3));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // User biasa — notifikasi sesuai transaksi nyata
    // ─────────────────────────────────────────────────────────────────────────

    private function seedTransactionNotifications(User $user): void
    {
        $transactions = Transaction::where('user_id', $user->id)
            ->with(['items.book'])
            ->get();

        if ($transactions->isEmpty()) return;

        foreach ($transactions as $trx) {
            $bookName = $trx->items->first()?->book?->name ?? 'Buku';
            $today    = Carbon::now()->startOfDay();
            $due      = $trx->due_at?->copy()->startOfDay();

            if (! $due) continue;

            $diff = (int) $today->diffInDays($due, false);

            if (! is_null($trx->returned_at)) {
                $this->insertNotification($user->id, 'returned', [
                    'transaction_id' => $trx->id,
                    'book_name'      => $bookName,
                    'returned_at'    => $trx->returned_at->toDateTimeString(),
                    'message'        => "Buku \"{$bookName}\" berhasil dikembalikan. Terima kasih!",
                ], $trx->returned_at);

            } elseif ($diff < 0) {
                $daysLate = abs($diff);
                $this->insertNotification($user->id, 'overdue', [
                    'transaction_id' => $trx->id,
                    'book_name'      => $bookName,
                    'due_at'         => $trx->due_at->toDateTimeString(),
                    'days_late'      => $daysLate,
                    'message'        => "Peminjaman \"{$bookName}\" terlambat {$daysLate} hari. Segera kembalikan!",
                ], Carbon::now()->subHours(rand(1, 12)));

            } elseif ($diff === 3) {
                $this->insertNotification($user->id, 'due_soon', [
                    'transaction_id' => $trx->id,
                    'book_name'      => $bookName,
                    'due_at'         => $trx->due_at->toDateTimeString(),
                    'days_left'      => 3,
                    'message'        => "Peminjaman \"{$bookName}\" jatuh tempo dalam 3 hari.",
                ], Carbon::now()->subHours(rand(1, 6)));

            } elseif ($diff === 1) {
                $this->insertNotification($user->id, 'due_tomorrow', [
                    'transaction_id' => $trx->id,
                    'book_name'      => $bookName,
                    'due_at'         => $trx->due_at->toDateTimeString(),
                    'days_left'      => 1,
                    'message'        => "Peminjaman \"{$bookName}\" jatuh tempo besok!",
                ], Carbon::now()->subHours(rand(1, 3)));

            } elseif ($diff === 0) {
                $this->insertNotification($user->id, 'due_today', [
                    'transaction_id' => $trx->id,
                    'book_name'      => $bookName,
                    'due_at'         => $trx->due_at->toDateTimeString(),
                    'days_left'      => 0,
                    'message'        => "Peminjaman \"{$bookName}\" jatuh tempo hari ini!",
                ], Carbon::now()->subHours(rand(0, 2)));
            }
            // diff > 3: aktif, belum perlu notifikasi
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Preorder Notifications
    // ─────────────────────────────────────────────────────────────────────────

    private function seedPreorderNotifications(User $user): void
    {
        // Skip demo user — preorder sudah di-handle di seedAllTransactionNotifications
        if ($user->email === 'user@gmail.com') return;

        $preorders = Preorder::where('user_id', $user->id)
            ->with('book')
            ->get();

        if ($preorders->isEmpty()) return;

        foreach ($preorders as $preorder) {
            $bookName = $preorder->book?->name ?? 'Buku';

            match ($preorder->status) {
                'ready' => $this->insertNotification($user->id, 'preorder_ready', [
                    'preorder_id' => $preorder->id,
                    'book_name'   => $bookName,
                    'expired_at'  => $preorder->expired_at?->toDateTimeString(),
                    'message'     => "Buku \"{$bookName}\" sudah tersedia! Segera konfirmasi sebelum {$preorder->expired_at?->format('d M Y')}.",
                ], $preorder->notified_at ?? Carbon::now()->subHours(rand(1, 6))),

                'expired' => $this->insertNotification($user->id, 'preorder_expired', [
                    'preorder_id' => $preorder->id,
                    'book_name'   => $bookName,
                    'message'     => "Preorder buku \"{$bookName}\" telah kedaluwarsa karena tidak dikonfirmasi.",
                ], $preorder->expired_at ?? Carbon::now()->subDays(rand(1, 3))),

                default => null,
            };
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper — insert ke tabel notifications
    // ─────────────────────────────────────────────────────────────────────────

    private function insertNotification(
        string $userId,
        string $type,
        array  $data,
        Carbon $createdAt,
        bool   $markRead = false
    ): void {
        $readAt = ($markRead || rand(1, 10) <= 4)
            ? $createdAt->copy()->addMinutes(rand(5, 120))
            : null;

        \DB::table('notifications')->insert([
            'id'              => Str::uuid(),
            'type'            => $this->resolveClass($type),
            'notifiable_type' => User::class,
            'notifiable_id'   => $userId,
            'data'            => json_encode($data),
            'read_at'         => $readAt,
            'created_at'      => $createdAt,
            'updated_at'      => $createdAt,
        ]);
    }

    private function resolveClass(string $type): string
    {
        return match ($type) {
            'due_soon'           => \App\Notifications\Transaction\DueSoonNotification::class,
            'due_tomorrow'       => \App\Notifications\Transaction\DueTomorrowNotification::class,
            'due_today'          => \App\Notifications\Transaction\DueTodayNotification::class,
            'overdue'            => \App\Notifications\Transaction\OverdueNotification::class,
            'returned'           => \App\Notifications\Transaction\ReturnedNotification::class,
            'preorder_ready'     => \App\Notifications\PreorderReadyNotification::class,
            'preorder_expired'   => \App\Notifications\PreorderExpiredNotification::class,
            'preorder_cancelled' => \App\Notifications\PreorderBookCancelledNotification::class,
            default              => throw new \InvalidArgumentException("Unknown notification type: {$type}"),
        };
    }
}