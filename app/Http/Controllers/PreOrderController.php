<?php

namespace App\Http\Controllers;

use App\Contracts\Interface\PreorderInterface;
use App\Models\Book;
use App\Models\Fine;
use App\Models\Preorder;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PreorderController extends Controller
{
    public function __construct(
        protected PreorderInterface $repo,        
        protected SettingService $settings  // ← tambahkan ini
) {}

    /**
     * Halaman daftar preorder milik user
     */
    public function index(Request $request)
    {
        $filters   = $request->only(['status']);
        $preorders = $this->repo->getByUser(Auth::id(), $filters);

        return view('preorders.index', compact('preorders'));
    }

    /**
     * Simpan preorder baru (AJAX / redirect)
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_id'              => ['required', 'uuid', 'exists:books,id'],
            'expected_borrow_date' => ['required', 'date', 'after_or_equal:today'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ]);

        if (!$this->settings->isPreorderEnabled()) {
            return $this->respond($request, false, 'Fitur preorder sedang tidak tersedia.');
        }

        if (!$this->settings->allowBorrowIfHasUnpaidFine()) {
            $hasUnpaid = Fine::whereHas('transaction', fn($q) => $q->where('user_id', Auth::id()))
                ->where('status', 'unpaid')
                ->exists();

            if ($hasUnpaid) {
                return $this->respond($request, false, 'Lunasi denda Anda terlebih dahulu sebelum melakukan preorder.');
            }
        }

        $maxActive = $this->settings->maxActivePreordersPerUser();
        if ($maxActive > 0) {
            $activeCount = Preorder::where('user_id', Auth::id())
                ->whereIn('status', ['waiting', 'ready'])
                ->count();

            if ($activeCount >= $maxActive) {
                return $this->respond($request, false, "Anda sudah memiliki {$maxActive} preorder aktif. Batalkan salah satu untuk melanjutkan.");
            }
        }

        try {
            $book = Book::findOrFail($request->book_id);
            if ($book->stock > 0) {
                return $this->respond(
                    $request, false,
                    'Buku masih tersedia, Anda dapat langsung meminjam.',
                    route('books.show', $book->slug)
                );
            }

            $preorder = $this->repo->create([
                'user_id'              => Auth::id(),
                'book_id'              => $request->book_id,
                'expected_borrow_date' => $request->expected_borrow_date,
                'notes'                => $request->notes,
            ]);

            return $this->respond(
                $request, true,
                "Preorder berhasil! Anda berada di antrian ke-{$preorder->queue_position}.",
                route('preorders.index')
            );
        } catch (\RuntimeException $e) {
            return $this->respond($request, false, $e->getMessage());
        }
    }

    /**
     * Update tanggal / catatan preorder (AJAX popup)
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'expected_borrow_date' => ['required', 'date', 'after_or_equal:today'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $preorder = $this->repo->findById($id);

            // Hanya pemilik yang boleh edit
            $this->authorize('update', $preorder);

            if (!$preorder->isActive()) {
                return response()->json(['success' => false, 'message' => 'Preorder ini tidak dapat diedit.'], 422);
            }

            $this->repo->update($id, [
                'expected_borrow_date' => $request->expected_borrow_date,
                'notes'                => $request->notes,
            ]);

            return response()->json(['success' => true, 'message' => 'Preorder berhasil diperbarui.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Preorder tidak ditemukan.'], 404);
        }
    }

    /**
     * Batalkan preorder
     */
    public function cancel(string $id)
    {
        try {
            $preorder = $this->repo->findById($id);
            $this->authorize('cancel', $preorder);

            $this->repo->cancel($id);

            return redirect()->route('preorders.index')
                ->with('success', 'Preorder berhasil dibatalkan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    /**
     * Konfirmasi peminjaman (saat buku sudah 'ready')
     * → redirect ke transactions.create dengan preorder_id
     */
    public function confirm(string $id)
    {
        try {
            $preorder = $this->repo->findById($id);
            $this->authorize('confirm', $preorder);

            if (!$preorder->isReady()) {
                return back()->with('error', 'Buku belum tersedia untuk dipinjam.');
            }

            // Tandai confirmed (transaksi akan meng-update ini setelah berhasil)
            return redirect()->route('transactions.create', [
                'book_id'     => $preorder->book_id,
                'preorder_id' => $preorder->id,
            ]);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    // ─── Admin ────────────────────────────────────────────────────

    /**
     * Daftar semua preorder (admin)
     */
    public function adminIndex(Request $request)
    {
        $filters   = $request->only(['status', 'book_id', 'search']);
        $preorders = $this->repo->getAll($filters);
        $books     = Book::orderBy('name')->get(['id', 'name']);

        return view('preorders.admin', compact('preorders', 'books'));
    }

    // ─── Private helpers ─────────────────────────────────────────

    private function respond(Request $request, bool $success, string $message, string $redirectUrl = null)
    {
        if ($request->expectsJson()) {
            $payload = ['success' => $success, 'message' => $message];
            if ($redirectUrl) {
                $payload['redirect'] = $redirectUrl;
            }
            return response()->json($payload, $success ? 200 : 422);
        }

        if ($success) {
            return redirect($redirectUrl ?? back()->getTargetUrl())
                ->with('success', $message);
        }

        return back()->with('error', $message);
    }
}