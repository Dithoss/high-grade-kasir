<?php
namespace App\Contracts\Repositories;

use App\Contracts\Interface\TransactionInterface;
use App\Helpers\QueryFilterHelper;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionRepository implements TransactionInterface
{
    protected Transaction $model;

    public function __construct(
        Transaction $model,
        protected SettingService $settings  
    ) {
        $this->model = $model;
    }
    public function findById(string $id): Transaction
    {
        return Transaction::with(['user', 'items.book'])
            ->where('id', $id)
            ->firstOrFail();
    }

    public function store(array $data): Transaction
    {
        $borrowedAt = $data['borrowed_at'] ?? now()->toDateString();

        $dueAt = $data['due_at']
            ?? Carbon::parse($borrowedAt)
                ->addDays($this->settings->defaultBorrowDays())
                ->toDateString();

        $transaction = Transaction::create([
            'user_id'        => $data['user_id'] ?? auth()->id(),
            'borrowed_at'    => $borrowedAt,
            'due_at'         => $dueAt,
            'status' => $data['status'] ?? 'borrowed',
            'receipt_number' => 'TRX-' . now()->format('Ymd') . '-' . strtoupper(str()->random(6)),
        ]);

        foreach ($data['items'] as $item) {
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'book_id'        => $item['book_id'],
                'quantity'       => $item['quantity'],
            ]);
        }

        return $transaction->load(['user', 'items.book']);
    }

    public function update(string $id, array $data): Transaction
    {
        $transaction = $this->findById($id);
        $transaction->update($data);

        return $transaction->fresh(['user', 'items.book']);
    }

    public function paginate(): LengthAwarePaginator
    {
        return Transaction::with(['user', 'items.book'])
            ->latest()
            ->paginate(10);
    }

    public function delete(mixed $id): bool
    {
        $Transaction = $this->findById($id);
        return $Transaction->delete();
    }
    
    public function forceDelete(mixed $id): bool
    {
        return $this->model->withTrashed()->findOrFail($id)->forceDelete();
    }

    public function restore(mixed $id): bool
    {
        return $this->model->onlyTrashed()->findOrFail($id)->restore();
    }

     public function trash(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->onlyTrashed()->with(['user', 'items.book']);
        
        // Search in user name or book title
        if ($search = Arr::get($filters, 'search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($query) => $query->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('items.book', fn($query) => $query->where('name', 'like', "%{$search}%"));
            });
        }
        
        QueryFilterHelper::applySorting($query, $filters, 'deleted_at', 'desc');
        
        $perPage = (int) Arr::get($filters, 'per_page', 15);
        
        return $query->paginate($perPage);
    }

    public function getWithFilters(array $filters = [], string|int $userId = null)
    {
        $query = Transaction::query()
            ->with(['user', 'items.book.category']);

        // Filter by user (untuk user biasa hanya lihat transaksinya sendiri)
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Search — cari di receipt_number, nama peminjam, atau nama buku
        if ($search = Arr::get($filters, 'search')) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%")
                                                    ->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('items.book', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by status — gunakan kolom status langsung
        if ($status = Arr::get($filters, 'status')) {
            if ($status === 'overdue') {
                // Status khusus: dipinjam tapi sudah lewat jatuh tempo
                $query->where('status', 'borrowed')
                    ->whereDate('due_at', '<', now());
            } else {
                // borrowed | return_requested | returned | damaged | lost
                $query->where('status', $status);
            }
        }

        // Filter by book
        if ($bookId = Arr::get($filters, 'book_id')) {
            $query->whereHas('items', fn($q) => $q->where('book_id', $bookId));
        }

        // Filter by category
        if ($categoryId = Arr::get($filters, 'category_id')) {
            $query->whereHas('items.book', fn($q) => $q->where('category_id', $categoryId));
        }

        // Date range
        if ($dateFrom = Arr::get($filters, 'date_from')) {
            $query->whereDate('borrowed_at', '>=', $dateFrom);
        }

        if ($dateTo = Arr::get($filters, 'date_to')) {
            $query->whereDate('borrowed_at', '<=', $dateTo);
        }

        // Sorting
        $sortBy  = Arr::get($filters, 'sort_by', 'created_at');
        $sortDir = Arr::get($filters, 'sort_dir', 'desc');

        $allowedSorts = ['borrowed_at', 'due_at', 'created_at', 'status'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        return $query->paginate(10)->withQueryString();
    }

    public function findBySlug(string|int $slug): Transaction
    {
        $query = $this->model->newQuery();
        $model = $query->where('slug', $slug)->first();
        if (!$model) {
            throw new ModelNotFoundException('Transaction not found');
        }

        return $model;
    }
    public function searchTrashed(string $keyword, int $perPage = 15)
    {
        return $this->model->onlyTrashed()
        ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }
    public function requestReturn(string $id): Transaction
    {
        $transaction = $this->findById($id);

        if ($transaction->status !== 'borrowed') {
            abort(400, 'Invalid transaction status');
        }

        $transaction->update([
            'status' => 'return_requested',
        ]);

        return $transaction;
    }
    public function confirmReturn(Transaction $transaction): void
    {
        $transaction->update([
            'status'      => 'returned',
            'returned_at' => now(),
        ]);
    }
    
public function requestExtend(Transaction $transaction): Transaction
{
    if (!$transaction->canBeExtended()) {
        abort(403, 'Transaksi tidak memenuhi syarat perpanjangan');
    }

    $transaction->update([
        'extension_requested_at' => now(),
    ]);

    return $transaction->fresh();
}

    public function approveExtend(Transaction $transaction): Transaction
    {
        if (!$transaction->extension_requested_at || $transaction->is_extended) {
            abort(400, 'Perpanjangan tidak valid');
        }

        $newDueDate = Carbon::parse($transaction->due_at)
            ->addDays($this->settings->extensionDays())
            ->toDateString();

        $transaction->update([
            'due_at'                => $newDueDate,
            'extended_due_at'       => $newDueDate,
            'is_extended'           => true,
            'extension_approved_at' => now(),
        ]);

        return $transaction->fresh();
    }
}