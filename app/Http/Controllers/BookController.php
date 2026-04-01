<?php

namespace App\Http\Controllers;

use App\Contracts\Interface\BookInterface;
use App\Contracts\Interface\CategoryInterface;
use App\Contracts\Repositories\AlgorithmRepository;
use App\Http\Handlers\BookHandler;
use App\Http\Requests\Book\MassBookActionRequest;
use App\Http\Requests\Book\StoreBook;
use App\Http\Requests\Book\UpdateBook;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function __construct(
        protected BookInterface $repo,
        protected BookHandler $handler,
        protected CategoryInterface $Crepo,
        protected AlgorithmRepository $algorithm

    ) {}

    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'stock_min', 'stock_max', 'sort_by', 'sort_dir','category_id'
        ]);

        $book = $this->repo->getWithFilters($filters);
        if (auth()->check() && auth()->user()->hasRole('user')) {
            return view('books.index-user', compact('book'));
        }


        return view('books.index', compact('book'));
    }

    public function create()
    {
        $categories = $this->Crepo->getWithFilters();
        return view('books.create', compact('categories'));
    }

    public function store(StoreBook $request)
    {
        $this->handler->create($request->validated());

        return redirect()
            ->route('books.index')
            ->with('success', __('Berhasil Ditambahkan'));
    }
    public function show(string $slug)
    {
        try {
            $book = $this->repo->findBySlug($slug);
            
            if (auth()->check()) {
                $this->algorithm->logView(auth()->id(), $book->id);
            }

            $relatedBooks = $this->algorithm->relatedByCategory($book, 6);

            return view('books.show', compact('book', 'relatedBooks'));
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    public function related(Book $book)
    {
        $relatedBooks = $this->algorithm->relatedByCategory($book);

        return response()->json($relatedBooks);
    }

    public function edit(string $id)
    {
        try {
            $book = $this->repo->findById($id);

            return view('books.edit', compact('book'));
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    public function update(UpdateBook $request, string $id)
    {
        try {
            $this->handler->update($id, $request->validated());

            return redirect()
                ->route('books.index')
                ->with('success', __('alert.update_success'));
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->repo->delete($id);

            return redirect()
            ->route('books.index')
            ->with('success', __('Berhasil Dihapus'));
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    public function trash()
    {
        $book = $this->repo->trash([]);
        return view('books.trash', compact('book'));
    }
    public function emptyTrash()
    {
        $count = Book::onlyTrashed()->count();

        if ($count === 0) {
            return redirect()->route('books.trash')
                ->with('info', 'Sampah sudah kosong.');
        }

        Book::onlyTrashed()->forceDelete();

        return redirect()->route('books.trash')
            ->with('success', "Berhasil menghapus {$count} buku secara permanen.");
    }

    public function massRestore(MassBookActionRequest $request)
    {
        $count = Book::onlyTrashed()
            ->whereIn('id', $request->ids)
            ->restore();

        return redirect()->route('books.trash')
            ->with('success', "Berhasil memulihkan {$count} buku.");
    }

    public function massForceDelete(MassBookActionRequest $request)
    {
        $count = Book::onlyTrashed()
            ->whereIn('id', $request->ids)
            ->count();

        Book::onlyTrashed()
            ->whereIn('id', $request->ids)
            ->forceDelete();

        return redirect()->route('books.trash')
            ->with('success', "Berhasil menghapus {$count} buku secara permanen.");
}
    public function forceDelete(string $id)
    {
        try {
            $this->repo->forceDelete($id);

            return redirect()
                ->route('books.trash')
                ->with('success', 'Buku berhasil dihapus permanen.');
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }
    public function restore(string $id)
    {
        $this->repo->restore($id);

        return back()->with('success', __('alert.user_restore_success'));
    }
    public function massDelete(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['uuid', 'exists:books,id'],
        ])['ids'];

        DB::transaction(function () use ($ids) {
            Book::whereIn('id', $ids)->delete(); // soft delete
        });

        return back()->with('success', 'User berhasil dihapus.');
    }
    public function catalog(Request $request)
    {
        $query = Book::with('category')->latest();

        // Filter: search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('writer', 'like', "%{$search}%");
            });
        }

        // Filter: category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter: availability
        if ($request->filled('available')) {
            if ($request->available === '1') {
                $query->where('stock', '>', 0);
            } else {
                $query->where('stock', 0);
            }
        }

        // Sort
        switch ($request->get('sort', 'latest')) {
            case 'oldest':
                $query->oldest();
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->latest();
        }

        $books           = $query->paginate(24);
        $categories      = Category::orderBy('name')->get();
        $totalBooks      = Book::count();
        $availableBooks  = Book::where('stock', '>', 0)->count();
        $totalCategories = Category::count();

        return view('books.catalog', compact(
            'books',
            'categories',
            'totalBooks',
            'availableBooks',
            'totalCategories'
        ));
    }
    public function barcodeLookup(Request $request)
    {
        $barcode = $request->query('barcode');

        if (!$barcode) {
            return response()->json(['error' => 'Barcode diperlukan'], 422);
        }

        $book = \App\Models\Book::with('category')
            ->where('barcode', $barcode)
            ->whereNull('deleted_at')
            ->first();

        if (!$book) {
            return response()->json(['error' => 'Buku tidak ditemukan'], 404);
        }

        return response()->json([
            'id'       => $book->id,
            'name'     => $book->name,
            'barcode'  => $book->barcode,
            'writer'   => $book->writer,
            'stock'    => $book->stock,
            'image'    => $book->image,
            'slug'     => $book->slug,
            'category' => $book->category ? [
                'id'   => $book->category->id,
                'name' => $book->category->name,
            ] : null,
        ]);
    }
}
