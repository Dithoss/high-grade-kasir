<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Preorder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PreorderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::role('user')->get();

        if ($users->isEmpty()) {
            $this->command->warn('Tidak ada user dengan role "user". Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        // Ambil buku dengan stok 0 atau rendah (realistis untuk preorder)
        $booksLowStock = Book::where('stock', '<=', 2)->get();

        // Jika tidak ada buku stok rendah, ambil buku acak
        if ($booksLowStock->isEmpty()) {
            $booksLowStock = Book::inRandomOrder()->take(4)->get();
        }

        if ($booksLowStock->isEmpty()) {
            $this->command->warn('Tidak ada buku. Jalankan BookSeeder terlebih dahulu.');
            return;
        }

        foreach ($booksLowStock->take(4) as $book) {
            $this->seedPreordersForBook($book, $users);
        }

        $this->command->info('PreorderSeeder selesai. Total: ' . Preorder::count() . ' preorder.');
    }

    private function seedPreordersForBook($book, $users): void
    {
        // Ambil 3–4 user acak berbeda untuk preorder buku ini
        $count         = rand(3, min(4, $users->count()));
        $selectedUsers = $users->shuffle()->take($count);

        foreach ($selectedUsers as $position => $user) {
            // Cek apakah user sudah punya preorder aktif untuk buku ini
            $alreadyExists = Preorder::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->whereIn('status', ['waiting', 'ready'])
                ->exists();

            if ($alreadyExists) continue;

            $queuePosition = $position + 1;
            $status        = $this->resolveStatus($queuePosition);
            $notifiedAt    = null;
            $confirmedAt   = null;
            $expiredAt     = null;

            if ($status === 'ready') {
                $notifiedAt = Carbon::now()->subHours(rand(1, 12));
                $expiredAt  = Carbon::now()->addDays(2);
            }

            if ($status === 'confirmed') {
                $notifiedAt  = Carbon::now()->subDays(rand(1, 3));
                $confirmedAt = Carbon::now()->subHours(rand(1, 6));
                $expiredAt   = Carbon::now()->addDays(2);
            }

            if ($status === 'expired') {
                $notifiedAt = Carbon::now()->subDays(rand(3, 7));
                $expiredAt  = Carbon::now()->subDays(rand(1, 2));
            }

            Preorder::create([
                'id'                   => Str::uuid(),
                'user_id'              => $user->id,
                'book_id'              => $book->id,
                'status'               => $status,
                'queue_position'       => $queuePosition,
                'expected_borrow_date' => Carbon::now()->addDays(rand(3, 14))->toDateString(),
                'notified_at'          => $notifiedAt,
                'confirmed_at'         => $confirmedAt,
                'expired_at'           => $expiredAt,
                'notes'                => $this->randomNote(),
            ]);
        }
    }

    /**
     * Posisi 1 → ready (antrian pertama, buku tersedia untuk dia)
     * Posisi 2 → waiting
     * Posisi 3 → waiting
     * Posisi 4 → expired (simulasi yang sudah kedaluwarsa)
     */
    private function resolveStatus(int $position): string
    {
        return match ($position) {
            1       => 'ready',
            4       => 'expired',
            default => 'waiting',
        };
    }

    private function randomNote(): ?string
    {
        $notes = [
            'Sudah lama menunggu buku ini.',
            'Butuh untuk tugas akhir.',
            'Referensi penelitian.',
            'Direkomendasikan dosen.',
            null,
            null, // null lebih sering muncul
        ];

        return $notes[array_rand($notes)];
    }
}