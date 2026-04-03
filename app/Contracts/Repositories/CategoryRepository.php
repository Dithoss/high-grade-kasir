<?php
namespace App\Contracts\Repositories;

use App\Contracts\Interface\CategoryInterface;
use App\Helpers\QueryFilterHelper;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository implements CategoryInterface
{
    protected Category $model ;
    
    public function __construct(Category $model)
    {
        $this->model = $model;
    }
    public function findById(mixed $id): ?Category
    {
        return $this->model->where('id', $id )->firstOrFail();
    }
    public function store(array $data): Category
    {
        return $this->model->create($data);
    }

    public function update(mixed $id, array $data): Category
    {
        $Category = $this->findById($id);
        $Category->update($data);
        return $Category->fresh();
    }

    public function delete(mixed $id): bool
    {
        $Category = $this->findById($id);
        return $Category->delete();
    }
    
    public function forceDelete(mixed $id): bool
    {
        $category = $this->model->withTrashed()->findOrFail($id);

        // Cek apakah kategori masih dipakai buku (termasuk yang soft-deleted)
        $hasBooks = \App\Models\Book::withTrashed()
            ->where('category_id', $category->id)
            ->exists();

        if ($hasBooks) {
            throw new \Exception('Kategori tidak dapat dihapus permanen karena masih digunakan oleh buku.');
        }

        return $category->forceDelete();
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
        $query = Category::query(); // hapus ->with('category')

        QueryFilterHelper::applyFilters(
            $query,
            $filters,
            ['name'] 
        );

        QueryFilterHelper::applySorting($query, $filters);

        return $query->paginate(10)->withQueryString();
    }

    public function paginate(int $pagination = 10):LengthAwarePaginator
    {
        return $this->model->orderBy('created_at', 'desc')->paginate($pagination);
    }
    public function findBySlug(string|int $slug): Category
    {
        $query = $this->model->newQuery();
        $model = $query->where('slug', $slug)->first();
        if (!$model) {
            throw new ModelNotFoundException('Category not found');
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