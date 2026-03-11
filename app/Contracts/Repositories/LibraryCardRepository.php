<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interface\LibraryCardInterface;
use App\Helpers\QueryFilterHelper;
use App\Models\LibraryCard;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LibraryCardRepository implements LibraryCardInterface
{
    protected LibraryCard $model;

    public function __construct(LibraryCard $model)
    {
        $this->model = $model;
    }

    public function findByUserId(string $userId): ?LibraryCard
    {
        return $this->model->with('user')->where('user_id', $userId)->first();
    }

    public function findById(string $id): ?LibraryCard
    {
        return $this->model->with('user')->where('id', $id)->firstOrFail();
    }

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with('user');

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by search (card number or user name)
        if (!empty($filters['search'])) {
            $keyword = $filters['search'];
            $query->where(function ($q) use ($keyword) {
                $q->where('card_number', 'like', "%{$keyword}%")
                  ->orWhereHas('user', function ($q2) use ($keyword) {
                      $q2->where('name', 'like', "%{$keyword}%")
                         ->orWhere('email', 'like', "%{$keyword}%");
                  });
            });
        }

        // Filter expiring soon (within 30 days)
        if (!empty($filters['expiring_soon'])) {
            $query->where('expired_at', '<=', now()->addDays(30))
                  ->where('expired_at', '>=', now())
                  ->where('status', 'active');
        }

        $sortBy  = Arr::get($filters, 'sort_by', 'created_at');
        $sortDir = Arr::get($filters, 'sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) Arr::get($filters, 'per_page', 15);

        return $query->paginate($perPage);
    }

    public function create(array $data): LibraryCard
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): LibraryCard
    {
        $card = $this->findById($id);
        $card->update($data);
        return $card->fresh('user');
    }

    public function updateStatus(string $id, string $status): LibraryCard
    {
        $card = $this->findById($id);
        $card->update(['status' => $status]);
        return $card->fresh('user');
    }

    public function regenerateCard(string $id): LibraryCard
    {
        $card = $this->findById($id);
        $card->update([
            'card_number' => 'CARD-' . strtoupper(Str::random(8)),
            'status'      => 'active',
            'expired_at'  => now()->addYears(3),
        ]);
        return $card->fresh('user');
    }
}