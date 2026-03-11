<?php

namespace App\Http\Controllers;

use App\Contracts\Interface\TransactionInterface;
use App\Events\TransactionCreated;
use App\Http\Requests\Transaction\StoreTransaction;
use App\Http\Requests\Transaction\StoreTransactionAdmin;
use App\Http\Requests\Transaction\UpdateTransaction;
use App\Models\Fine;
use App\Models\Transaction;
use App\Models\AuditLog;
use App\Services\SettingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected TransactionInterface $repo,
        protected SettingService $settings
    ) {}

    /**
     * List transaksi + filter
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'sort_by',
            'sort_dir',
            'category_id',
            'book_id',
            'date_from',
            'date_to',
            'status'
        ]);

        $user   = auth()->user();
        $userId = $user->hasRole('user') ? $user->id : null;

        $transactions = $this->repo->getWithFilters($filters, $userId);

        if ($user->hasRole('user')) {
            return view('transactions.index-user', compact('transactions'));
        }

        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        $books = \App\Models\Book::with('category')
            ->whereNull('deleted_at')
            ->get();

        // Kirim $users agar view bisa render dropdown peminjam untuk admin
        $users = \App\Models\User::whereHas('roles', fn($q) => $q->where('name', 'user'))
            ->orderBy('name')
            ->get();

        return view('transactions.create', compact('books', 'users'));
    }

    /**
     * Store transaksi oleh USER biasa
     */
     // ─────────────────────────────────────────────────────────────────────────
    public function store(StoreTransaction $request)
    {
        $this->authorize('create', Transaction::class);

        $user = auth()->user();

        if (!$this->settings->allowBorrowIfHasUnpaidFine()) {
            $hasUnpaid = Fine::whereHas('transaction', fn($q) => $q->where('user_id', $user->id))
                ->where('status', 'unpaid')
                ->exists();

            if ($hasUnpaid) {
                return back()->with('error', 'Anda memiliki denda yang belum dilunasi.');
            }
        }

        $activeBorrows = Transaction::where('user_id', $user->id)
            ->whereIn('status', ['borrowed', 'pending_approval']) 
            ->withCount('items')
            ->get()
            ->sum('items_count');

        $maxBooks = $this->settings->maxBooksPerUser();
        if ($activeBorrows >= $maxBooks) {
            return back()->with('error', "Batas peminjaman maksimal {$maxBooks} buku sekaligus.");
        }

        $requireApproval = $this->settings->requireAdminApproval();

        DB::transaction(function () use ($request, $requireApproval) {
            $data = array_merge(
                $request->validated(),
                [
                    'user_id' => auth()->id(),
                    'status'  => $requireApproval ? 'pending_approval' : 'borrowed',
                ]
            );

            $transaction = $this->repo->store($data);
            event(new TransactionCreated($transaction));
        });

        if ($requireApproval) {
            return redirect()
                ->route('transactions.index')
                ->with('success', 'Permintaan peminjaman berhasil dikirim, menunggu persetujuan admin.');
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil dibuat');
    }
    /**
     * Store transaksi oleh ADMIN (bisa pilih user lain)
     */
    public function storeAdmin(StoreTransactionAdmin $request)
    {
        DB::transaction(function () use ($request) {
            $data = array_merge(
                $request->validated(),
                ['status' => 'borrowed'] // admin selalu langsung borrowed
            );

            $transaction = $this->repo->store($data);
            event(new TransactionCreated($transaction));
        });

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil dibuat');
    }

    public function requestExtend(string $id)
    {
        $transaction = $this->repo->findById($id);
        $this->authorize('extend', $transaction);

        if (!$this->settings->isExtensionAllowed()) {
            return back()->with('error', 'Fitur perpanjangan sedang tidak tersedia.');
        }

        $maxExtensions = $this->settings->maxExtensionCount();
        $currentCount  = $transaction->extension_count ?? 0;
        if ($currentCount >= $maxExtensions) {
            return back()->with('error', "Maksimal perpanjangan {$maxExtensions}x per transaksi.");
        }

        $minDays     = $this->settings->extensionMinDaysBeforeDue();
        $daysLeft    = \Carbon\Carbon::parse($transaction->due_at)->diffInDays(now(), false);
        $isNotDueYet = $daysLeft < 0; // negatif = belum jatuh tempo

        if (!$isNotDueYet) {
            return back()->with('error', 'Tidak dapat memperpanjang — buku sudah melewati jatuh tempo.');
        }

        if (abs($daysLeft) < $minDays) {
            return back()->with('error', "Perpanjangan hanya bisa diajukan minimal {$minDays} hari sebelum jatuh tempo.");
        }

        $this->repo->requestExtend($transaction);

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'request_extend',
            'target_type' => Transaction::class,
            'target_id'   => $transaction->id,
            'description' => 'User mengajukan perpanjangan peminjaman',
        ]);

        return back()->with('success', 'Permintaan perpanjangan dikirim, menunggu persetujuan admin.');
    }
    /**
     * Show transaction history
     */
    public function history(Request $request)
    {
        $filters = $request->only([
            'search',
            'status',
            'date_from',
            'date_to',
        ]);

        $userId = auth()->user()->hasRole('user') ? auth()->id() : null;

        $query = Transaction::query()
            ->with(['user', 'items.book.category', 'fine'])
            ->when($userId, fn($q) => $q->where('user_id', $userId));

        if ($search = $filters['search'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('items.book', fn($query) => $query->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $filters['status'] ?? null) {
            if ($status === 'overdue') {
                $query->where('status', 'borrowed')
                      ->whereDate('due_at', '<', now());
            } else {
                $query->where('status', $status);
            }
        }

        if ($dateFrom = $filters['date_from'] ?? null) {
            $query->whereDate('borrowed_at', '>=', $dateFrom);
        }

        if ($dateTo = $filters['date_to'] ?? null) {
            $query->whereDate('borrowed_at', '<=', $dateTo);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $stats = [
            'total'    => Transaction::when($userId, fn($q) => $q->where('user_id', $userId))->count(),
            'returned' => Transaction::when($userId, fn($q) => $q->where('user_id', $userId))->where('status', 'returned')->count(),
            'borrowed' => Transaction::when($userId, fn($q) => $q->where('user_id', $userId))->where('status', 'borrowed')->count(),
            'overdue'  => Transaction::when($userId, fn($q) => $q->where('user_id', $userId))
                ->where('status', 'borrowed')
                ->whereDate('due_at', '<', now())
                ->count(),
        ];

        return view('transactions.history', compact('transactions', 'stats'));
    }

    /**
     * Show transaction receipt
     */
    public function receipt($id)
    {
        $transaction = Transaction::with(['user', 'items.book.category'])
            ->findOrFail($id);

        return view('transactions.receipt', compact('transaction'));
    }

    public function approveExtend(string $id)
    {
        $transaction = $this->repo->findById($id);

        $this->repo->approveExtend($transaction);

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'approve_extend',
            'target_type' => Transaction::class,
            'target_id'   => $transaction->id,
            'description' => 'Admin menyetujui perpanjangan peminjaman',
        ]);

        return back()->with('success', 'Perpanjangan peminjaman disetujui');
    }

    public function show(string $id)
    {
        $transaction = $this->repo->findById($id);

        if (auth()->user()->hasRole('user') &&
            $transaction->user_id !== auth()->id()) {
            abort(403);
        }

        return view('transactions.show', compact('transaction'));
    }

    public function edit(string $id)
    {
        try {
            $transaction = $this->repo->findById($id);

            if (auth()->user()->hasRole('user') && $transaction->user_id !== auth()->id()) {
                abort(403, 'Anda tidak memiliki akses ke transaksi ini');
            }

            return view('transactions.edit', compact('transaction'));
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    public function update(UpdateTransaction $request, string $id)
    {
        try {
            $transaction = $this->repo->findById($id);

            if (auth()->user()->hasRole('user') && $transaction->user_id !== auth()->id()) {
                abort(403, 'Anda tidak memiliki akses ke transaksi ini');
            }

            if (in_array($transaction->status, ['damaged', 'lost'])) {
                abort(403, 'Transaksi sudah final dan tidak dapat diubah');
            }

            $oldStatus  = $transaction->status;
            $updateData = ['status' => $request->status];

            if ($request->status === 'returned' && !$transaction->returned_at) {
                $updateData['returned_at'] = now();
            }

            $this->repo->update($id, $updateData);

            AuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'update',
                'target_type' => Transaction::class,
                'target_id'   => $transaction->id,
                'description' => "Status transaksi diubah dari {$oldStatus} ke {$request->status}",
            ]);

            if ($request->status === 'returned' && auth()->user()->hasRole('admin')) {
                return redirect()
                    ->route('transactions.inspect', $id)
                    ->with('success', 'Status diubah menjadi dikembalikan, silakan lakukan inspeksi');
            }

            return back()->with('success', 'Status transaksi berhasil diperbarui');
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    public function requestReturn(string $id)
    {
        $transaction = $this->repo->findById($id);

        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        if ($transaction->status !== 'borrowed') {
            return back()->with('error', 'Transaksi tidak dapat diajukan pengembalian');
        }

        $this->repo->update($id, ['status' => 'return_requested']);

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'request_return',
            'target_type' => Transaction::class,
            'target_id'   => $transaction->id,
            'description' => 'User mengajukan pengembalian buku',
        ]);

        return back()->with('success', 'Pengembalian diajukan, menunggu konfirmasi admin');
    }

   public function confirmReturn(string $id)
    {
        $transaction = $this->repo->findById($id);

        if ($transaction->status !== 'return_requested') {
            return back()->with('error', 'Transaksi tidak dalam status pengajuan pengembalian');
        }

        $this->repo->update($id, [
            'status'      => 'returned',
            'returned_at' => now(),
        ]);

        // ✅ FIX 1: Arah diffInDays dibalik — due_at ke now(), bukan sebaliknya
        // Positif = melewati due_at = terlambat
        $lateDays = max(0, \Carbon\Carbon::parse($transaction->due_at)->diffInDays(now(), false));

        if ($lateDays > 0) {
            // ✅ FIX 2: Gunakan SettingService, bukan hardcoded 5000
            $perDay  = $this->settings->finePerDayLate();
            $maxFine = $this->settings->fineMaxLate();
            $amount  = $lateDays * $perDay;

            if ($maxFine > 0) {
                $amount = min($amount, $maxFine);
            }

            Fine::create([
                'transaction_id' => $transaction->id,
                'type'           => 'late',
                'late_days'      => $lateDays,
                'amount'         => $amount,
                'status'         => 'unpaid',
                'note'           => "Denda keterlambatan {$lateDays} hari @ Rp " . number_format($perDay, 0, ',', '.') . "/hari",
            ]);
        }

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'confirm_return',
            'target_type' => Transaction::class,
            'target_id'   => $transaction->id,
            'description' => 'Admin mengkonfirmasi pengembalian buku' . ($lateDays > 0 ? " (terlambat {$lateDays} hari)" : ''),
        ]);

        return redirect()
            ->route('transactions.inspect', $transaction->id)
            ->with('success', 'Pengembalian dikonfirmasi, silakan lakukan inspeksi');
    }

    /**
     * Show inspection form
     */
    public function inspect(Transaction $transaction)
    {
        if ($transaction->status !== 'returned') {
            return redirect()->route('transactions.index')
                ->with('error', 'Transaksi ini belum dikembalikan.');
        }

        return view('transactions.inspect', compact('transaction'));
    }

    /**
     * Store inspection result
     */
    public function inspectStore(Request $request, Transaction $transaction)
    {
        $request->validate([
            'condition'   => 'required|in:good,damaged,lost',
            'fine_amount' => 'nullable|numeric|min:0',
            'fine_type'   => 'nullable|in:broken,lost',
            'note'        => 'nullable|string|max:1000',
        ]);

        if ($request->condition === 'good') {
            $transaction->update([
                'status'          => 'returned',
                'inspection_note' => $request->note,
                'inspected_at'    => now(),
            ]);

            return redirect()->route('transactions.index')
                ->with('success', 'Inspeksi selesai. Buku dalam kondisi baik.');
        }

        if (in_array($request->condition, ['damaged', 'lost'])) {
            if (!$request->fine_amount || $request->fine_amount <= 0) {
                return back()->withErrors(['fine_amount' => 'Jumlah denda harus diisi untuk kondisi rusak atau hilang.']);
            }

            Fine::create([
                'transaction_id' => $transaction->id,
                'type'           => $request->fine_type ?? ($request->condition === 'lost' ? 'lost' : 'broken'),
                'amount'         => $request->fine_amount,
                'late_days'      => 0,
                'note'           => $request->note,
                'status'         => 'unpaid',
            ]);

            $transaction->update([
                'status'          => $request->condition,
                'inspection_note' => $request->note,
                'inspected_at'    => now(),
            ]);

            $conditionText = $request->condition === 'damaged' ? 'rusak' : 'hilang';

            return redirect()->route('transactions.index')
                ->with('success', "Inspeksi selesai. Buku dalam kondisi {$conditionText}. Denda telah ditambahkan.");
        }

        return back()->with('error', 'Terjadi kesalahan saat menyimpan hasil inspeksi.');
    }

    public function destroy(string $id)
    {
        try {
            $this->repo->delete($id);

            AuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'delete',
                'target_type' => Transaction::class,
                'target_id'   => $id,
                'description' => 'Menghapus transaksi (soft delete)',
            ]);

            return redirect()
                ->route('transactions.index')
                ->with('success', 'Transaksi berhasil dihapus');
        } catch (ModelNotFoundException) {
            abort(404);
        }
    }

    public function trash()
    {
        $transactions = $this->repo->trash([]);
        return view('transactions.trash', compact('transactions'));
    }

    public function restore(string $id)
    {
        $this->repo->restore($id);

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'restore',
            'target_type' => Transaction::class,
            'target_id'   => $id,
            'description' => 'Restore transaksi',
        ]);

        return back()->with('success', 'Transaksi berhasil dipulihkan');
    }

    public function forceDelete(string $id)
    {
        $this->repo->forceDelete($id);

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'force delete',
            'target_type' => Transaction::class,
            'target_id'   => $id,
            'description' => 'Hapus Permanen transaksi',
        ]);

        return back()->with('success', 'Transaksi berhasil dihapus permanen');
    }
    public function approveTransaction(string $id)
    {
        $transaction = $this->repo->findById($id);

        if ($transaction->status !== 'pending_approval') {
            return back()->with('error', 'Transaksi tidak dalam status menunggu persetujuan.');
        }

        $this->repo->update($id, ['status' => 'borrowed']);

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'approve_transaction',
            'target_type' => Transaction::class,
            'target_id'   => $transaction->id,
            'description' => 'Admin menyetujui peminjaman buku',
        ]);

        return back()->with('success', 'Peminjaman berhasil disetujui.');
    }

    public function rejectTransaction(string $id)
    {
        $transaction = $this->repo->findById($id);

        if ($transaction->status !== 'pending_approval') {
            return back()->with('error', 'Transaksi tidak dalam status menunggu persetujuan.');
        }

        $this->repo->update($id, ['status' => 'rejected']);

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'reject_transaction',
            'target_type' => Transaction::class,
            'target_id'   => $transaction->id,
            'description' => 'Admin menolak peminjaman buku',
        ]);

        return back()->with('success', 'Peminjaman berhasil ditolak.');
    }
}