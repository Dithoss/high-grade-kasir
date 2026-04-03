<?php
namespace App\Contracts\Repositories;

use App\Contracts\Interface\BookInterface;
use App\Helpers\QueryFilterHelper;
use App\Models\Book;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Pagination\LengthAwarePaginator;

class BookRepository implements BookInterface
{
    protected Book $model ;
    
    public function __construct(Book $model)
    {
        $this->model = $model;
    }
    public function findById(mixed $id): ?Book
    {
        return $this->model->where('id', $id )->firstOrFail();
    }
    public function store(array $data): Book
    {
        return $this->model->create($data);
    }
    public function update(mixed $id, array $data): Book
    {
        $Book = $this->findById($id);
        $Book->update($data);
        return $Book->fresh();
    }

    public function delete(mixed $id): bool
    {
        $Book = $this->findById($id);
        return $Book->delete();
    }
    
public function forceDelete(mixed $id): bool
{
    $book = $this->model->withTrashed()->findOrFail($id);

    $activeTransactions = \App\Models\TransactionItem::where('book_id', $book->id)
        ->whereHas('transaction', function ($q) {
            $q->whereIn('status', ['pending_approval', 'borrowed', 'return_requested']);
        })
        ->exists();

    if ($activeTransactions) {
        throw new \Exception('Buku tidak dapat dihapus permanen karena masih memiliki transaksi aktif.');
    }

    return $book->forceDelete();
}

    public function restore(mixed $id): bool
    {
        return $this->model->onlyTrashed()->findOrFail($id)->restore();
    }

    public function trash(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->onlyTrashed();
        $searchColumns = ['name'];
        
        QueryFilterHelper::applyFilters($query, $filters, $searchColumns);
        QueryFilterHelper::applySorting($query, $filters, 'deleted_at', 'desc');
        
        $perPage = (int) Arr::get($filters, 'per_page', 15);
        
        return $query->paginate($perPage);
    }
    public function getWithFilters(array $filters = [])
    {
         $query = Book::query()->with('category');

        QueryFilterHelper::applyFilters(
            $query,
            $filters,
            ['name', 'writer', 'category.name']
        );

        QueryFilterHelper::applySorting($query, $filters);

        return $query->paginate(10)->withQueryString();
    
    }
    public function paginate(int $pagination = 10):LengthAwarePaginator
    {
        return $this->model->orderBy('created_at', 'desc')->paginate($pagination);
    }
    public function findBySlug(string|int $slug): Book
    {
        $model = $this->model
            ->where('slug', $slug)
            ->first();

        if (!$model) {
            throw new ModelNotFoundException('Book not found');
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
    
}