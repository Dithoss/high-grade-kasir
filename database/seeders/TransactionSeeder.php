<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Fine;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Observers\TransactionObserver;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        Transaction::flushEventListeners();

        $this->seed();

        Transaction::observe(TransactionObserver::class);
    }

    private function seed(): void
    {
        $users = User::role('user')->get();

        if ($users->isEmpty()) {
            $this->command->warn('Tidak ada user dengan role "user". Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $books = Book::all();

        if ($books->isEmpty()) {
            $this->command->warn('Tidak ada buku. Jalankan BookSeeder terlebih dahulu.');
            return;
        }

        // ── 1. RETURNED — dikembalikan tepat waktu ────────────────────────────
        foreach ($users as $user) {
            $count = rand(2, 4);
            for ($i = 0; $i < $count; $i++) {
                $borrowedAt = Carbon::now()->subDays(rand(30, 120));
                $dueAt      = $borrowedAt->copy()->addDays(7);
                $returnedAt = $dueAt->copy()->subDays(rand(0, 2));

                $transaction = Transaction::create(
                    $this->basePayload($user->id, 'returned', $borrowedAt, $dueAt, [
                        'returned_at' => $returnedAt,
                        'is_extended' => false,
                    ])
                );

                $this->attachBooks($transaction, $books, rand(1, 2));
            }
        }

        // ── 2. OVERDUE — sudah lewat jatuh tempo, belum dikembalikan ──────────
        $overdueUsers = $users->random(min(3, $users->count()));
        foreach ($overdueUsers as $user) {
            $borrowedAt = Carbon::now()->subDays(rand(15, 30));
            $dueAt      = $borrowedAt->copy()->addDays(7);

            $transaction = Transaction::create(
                $this->basePayload($user->id, 'borrowed', $borrowedAt, $dueAt, [
                    'returned_at' => null,
                    'is_extended' => false,
                ])
            );

            $this->attachBooks($transaction, $books, rand(1, 2));

            $daysLate = abs((int) Carbon::now()->diffInDays($dueAt, false));

            Fine::create([
                'id'             => Str::uuid(),
                'transaction_id' => $transaction->id,
                'type'           => 'late',
                'late_days'      => max(1, $daysLate),
                'amount'         => max(1, $daysLate) * 1000,
                'status'         => 'unpaid',
                'note'           => "Keterlambatan {$daysLate} hari",
            ]);
        }

        // ── 3. ACTIVE — sedang dipinjam, belum jatuh tempo ───────────────────
        foreach ($users as $user) {
            $borrowedAt = Carbon::now()->subDays(rand(1, 5));
            $dueAt      = $borrowedAt->copy()->addDays(7);

            $transaction = Transaction::create(
                $this->basePayload($user->id, 'borrowed', $borrowedAt, $dueAt, [
                    'returned_at' => null,
                    'is_extended' => false,
                ])
            );

            $this->attachBooks($transaction, $books, rand(1, 3));
        }

        // ── 4. EXTENDED — perpanjangan sudah disetujui ────────────────────────
        $extendedUsers = $users->random(min(2, $users->count()));
        foreach ($extendedUsers as $user) {
            $borrowedAt           = Carbon::now()->subDays(rand(6, 10));
            $dueAt                = $borrowedAt->copy()->addDays(7);
            $extendedDueAt        = $dueAt->copy()->addDays(7);
            $extensionRequestedAt = $dueAt->copy()->subDays(1);
            $extensionApprovedAt  = $dueAt->copy()->subHours(12);

            $transaction = Transaction::create(
                $this->basePayload($user->id, 'borrowed', $borrowedAt, $dueAt, [
                    'returned_at'            => null,
                    'is_extended'            => true,
                    'extended_due_at'        => $extendedDueAt,
                    'extension_requested_at' => $extensionRequestedAt,
                    'extension_approved_at'  => $extensionApprovedAt,
                ])
            );

            $this->attachBooks($transaction, $books, rand(1, 2));
        }

        // ── 5. RETURNED WITH FINE — terlambat, denda sudah lunas ─────────────
        $lateReturnUsers = $users->random(min(2, $users->count()));
        foreach ($lateReturnUsers as $user) {
            $borrowedAt = Carbon::now()->subDays(rand(40, 90));
            $dueAt      = $borrowedAt->copy()->addDays(7);
            $returnedAt = $dueAt->copy()->addDays(rand(3, 10));
            $daysLate   = (int) $dueAt->diffInDays($returnedAt);

            $transaction = Transaction::create(
                $this->basePayload($user->id, 'returned', $borrowedAt, $dueAt, [
                    'returned_at' => $returnedAt,
                    'is_extended' => false,
                ])
            );

            $this->attachBooks($transaction, $books, 1);

            Fine::create([
                'id'             => Str::uuid(),
                'transaction_id' => $transaction->id,
                'type'           => 'late',
                'late_days'      => max(1, $daysLate),
                'amount'         => max(1, $daysLate) * 1000,
                'status'         => 'paid',
                'paid_at'        => $returnedAt->copy()->addDays(rand(0, 2)),
                'note'           => "Keterlambatan {$daysLate} hari, sudah lunas",
            ]);
        }

        $this->command->info('TransactionSeeder selesai.');
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function basePayload(
        string $userId,
        string $status,
        Carbon $borrowedAt,
        Carbon $dueAt,
        array  $extra = []
    ): array {
        $receiptNumber = $this->generateReceipt();
        $slug          = $this->generateSlug($receiptNumber);

        return array_merge([
            'id'             => Str::uuid(),
            'user_id'        => $userId,
            'status'         => $status,
            'borrowed_at'    => $borrowedAt,
            'due_at'         => $dueAt,
            'receipt_number' => $receiptNumber,
            'slug'           => $slug,
        ], $extra);
    }

    private function generateReceipt(): string
    {
        $date   = now()->format('Ymd');
        $random = Str::upper(Str::random(6));

        return "TRX-{$date}-{$random}";
    }

    private function generateSlug(string $receiptNumber): string
    {
        $slug  = Str::slug($receiptNumber);
        $slug .= '-' . date('YmdHis');
        $slug .= '-' . substr((string) microtime(true), -6);

        return $slug;
    }

    private function attachBooks(Transaction $transaction, $books, int $count): \Illuminate\Support\Collection
    {
        $selected = $books->random(min($count, $books->count()));

        if (! $selected instanceof \Illuminate\Support\Collection) {
            $selected = collect([$selected]);
        }

        $activeStatuses = ['borrowed', 'return_requested', 'extend_requested'];

        foreach ($selected as $book) {
            TransactionItem::create([
                'id'             => Str::uuid(),
                'transaction_id' => $transaction->id,
                'book_id'        => $book->id,
                'quantity'       => 1,
            ]);

            if (in_array($transaction->status, $activeStatuses) && $book->stock > 0) {
                $book->decrement('stock');
            }
        }

        return $selected;
    }
}