<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interface\PreorderInterface;
use App\Models\Preorder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PreorderReadyNotification;

class PreorderRepository implements PreorderInterface
{
    public function getByUser(string $userId, array $filters = [])
    {
        $query = Preorder::with(['book.category'])
            ->where('user_id', $userId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByRaw("FIELD(status, 'ready', 'waiting', 'confirmed', 'cancelled', 'expired')")
                     ->orderBy('queue_position')
                     ->orderBy('created_at', 'desc')
                     ->paginate(10);
    }

    public function getByBook(string $bookId, array $filters = [])
    {
        $query = Preorder::with(['user'])
            ->where('book_id', $bookId);

        if (!empty($filters['status'])) {
            $query->whereIn('status', (array) $filters['status']);
        } else {
            $query->whereIn('status', ['waiting', 'ready']);
        }

        return $query->orderBy('queue_position')->get();
    }

    public function findById(string $id)
    {
        $preorder = Preorder::with(['book.category', 'user'])->find($id);

        if (!$preorder) {
            throw new ModelNotFoundException("Preorder tidak ditemukan.");
        }

        return $preorder;
    }

    public function findActiveByUserAndBook(string $userId, string $bookId)
    {
        return Preorder::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->whereIn('status', ['waiting', 'ready'])
            ->first();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Cek duplikat
            $existing = $this->findActiveByUserAndBook($data['user_id'], $data['book_id']);
            if ($existing) {
                throw new \RuntimeException('Anda sudah memiliki preorder aktif untuk buku ini.');
            }

            // Hitung posisi antrian
            $queuePosition = $this->getQueuePosition($data['book_id']) + 1;

            $preorder = Preorder::create(array_merge($data, [
                'queue_position' => $queuePosition,
                'status'         => 'waiting',
            ]));

            return $preorder;
        });
    }

    public function update(string $id, array $data)
    {
        $preorder = $this->findById($id);
        $preorder->update($data);
        return $preorder->fresh();
    }

    public function cancel(string $id)
    {
        return DB::transaction(function () use ($id) {
            $preorder = $this->findById($id);

            if (!$preorder->isActive()) {
                throw new \RuntimeException('Hanya preorder aktif yang dapat dibatalkan.');
            }

            $cancelledPosition = $preorder->queue_position;
            $bookId            = $preorder->book_id;

            $preorder->update(['status' => 'cancelled']);

            // Geser posisi antrian lainnya
            Preorder::where('book_id', $bookId)
                ->whereIn('status', ['waiting', 'ready'])
                ->where('queue_position', '>', $cancelledPosition)
                ->decrement('queue_position');

            return $preorder;
        });
    }

    public function getQueuePosition(string $bookId): int
    {
        return Preorder::where('book_id', $bookId)
            ->whereIn('status', ['waiting', 'ready'])
            ->count();
    }

    /**
     * Notifikasi pengguna pertama dalam antrian bahwa buku sudah tersedia.
     * Dipanggil dari TransactionController saat buku dikembalikan.
     */
    public function notifyReady(string $bookId): void
    {
        DB::transaction(function () use ($bookId) {
            $nextPreorder = Preorder::with('user')
                ->where('book_id', $bookId)
                ->where('status', 'waiting')
                ->orderBy('queue_position')
                ->lockForUpdate()
                ->first();

            if (!$nextPreorder) {
                return;
            }

            $nextPreorder->update([
                'status'       => 'ready',
                'notified_at'  => now(),
                'expired_at'   => now()->addDays(2), // 2 hari untuk konfirmasi
            ]);

            // Kirim notifikasi in-app
            if ($nextPreorder->user) {
                $nextPreorder->user->notify(
                    new PreorderReadyNotification($nextPreorder)
                );
            }
        });
    }

    public function getAll(array $filters = [])
    {
        $query = Preorder::with(['book.category', 'user']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['book_id'])) {
            $query->where('book_id', $filters['book_id']);
        }

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('book', fn($q) => $q->where('name', 'like', "%{$s}%"));
        }

        return $query->orderByRaw("FIELD(status, 'ready', 'waiting', 'confirmed', 'cancelled', 'expired')")
                     ->orderBy('queue_position')
                     ->paginate(15);
    }
}