<?php

namespace App\Http\Controllers;

use App\Contracts\Interface\CategoryInterface;
use App\Http\Handlers\CategoryHandler;
use App\Http\Requests\Category\StoreCategory;
use App\Http\Requests\Category\UpdateCategory;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryInterface $repo,
        protected CategoryHandler $handler,
    ) {}

    // ─────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'stock_min', 'stock_max', 'sort_by', 'sort_dir', 'category_id',
        ]);

        $categories = $this->repo->getWithFilters($filters);

        return view('categories.index', compact('categories'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────────────

    public function create()
    {
        return view('categories.create');
    }

    // ─────────────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────────────

    public function store(StoreCategory $request)
    {
        // Gabungkan validated data dengan file gambar (tidak masuk validated() otomatis)
        $data = array_merge(
            $request->validated(),
            ['image' => $request->file('image')] // null jika tidak ada
        );

        $this->handler->create($data);

        return redirect()
            ->route('categories.index')
            ->with('success', __('Berhasil Ditambahkan'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────────────

    public function show(string $id)
    {
        try {
            $categories = $this->repo->findById($id);
            return view('categories.show', compact('categories'));
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────────────

    public function edit(string $id)
    {
        $category   = $this->repo->findById($id);
        $categories = Category::all();

        return view('categories.edit', compact('category', 'categories'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────────────

    public function update(UpdateCategory $request, string $id)
    {
        try {
            // Gabungkan validated data + file + remove_image flag
            $data = array_merge(
                $request->validated(),
                [
                    'image'        => $request->file('image'),           // null jika tidak upload
                    'remove_image' => $request->boolean('remove_image'), // checkbox hapus gambar
                ]
            );

            $this->handler->update($id, $data);

            return redirect()
                ->route('categories.index')
                ->with('success', __('Berhasil Memperbarui'));
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────────────

    public function destroy(string $id)
    {
        try {
            // Gunakan handler agar gambar ikut dihapus dari storage
            $this->repo->delete($id);

            return back()->with('success', __('Berhasil Menghapus'));
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // TRASH & RESTORE
    // ─────────────────────────────────────────────────────────────────────

    public function trash()
    {
        $categories = $this->repo->trash([])->paginate(10);
        return view('category.trash', compact('categories'));
    }

    public function restore(string $id)
    {
        $this->repo->restore($id);
        return back()->with('success', __('alert.user_restore_success'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // MASS DELETE
    // ─────────────────────────────────────────────────────────────────────

    public function massDelete(Request $request)
    {
        $ids = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['uuid', 'exists:categories,id'],
        ])['ids'];

        // Gunakan handler agar gambar ikut dihapus dari storage
        $deleted = $this->handler->massDelete($ids);

        return back()->with('success', "{$deleted} kategori berhasil dihapus.");
    }
}