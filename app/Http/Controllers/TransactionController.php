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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected TransactionInterface $repo
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

    $user = auth()->user();
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
                ->whereNull('deleted_at')  // karena pakai SoftDeletes
                ->get();

    return view('transactions.create', compact('books'));
    }

    public function store(StoreTransaction $request)
    {
        $this->authorize('create', Transaction::class);
        DB::transaction(function () use ($request) {
            $transaction = $this->repo->store(
                array_merge(
                    $request->validated(),
                    ['user_id' => auth()->id()]
                )
            );

            event(new TransactionCreated($transaction));
        });

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil dibuat');
    }

    public function storeAdmin(StoreTransactionAdmin $request)
    {
        DB::transaction(function () use ($request) {
            $transaction = $this->repo->store($request->validated());
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

        $this->repo->requestExtend($transaction);

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'request_extend',
            'target_type' => Transaction::class,
            'target_id'   => $transaction->id,
            'description' => 'User mengajukan perpanjangan peminjaman',
        ]);

        return back()->with('success', 'Permintaan perpanjangan dikirim');
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

        // Apply filters
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

        // Calculate stats
        $stats = [
            'total' => Transaction::when($userId, fn($q) => $q->where('user_id', $userId))->count(),
            'returned' => Transaction::when($userId, fn($q) => $q->where('user_id', $userId))
                ->where('status', 'returned')->count(),
            'borrowed' => Transaction::when($userId, fn($q) => $q->where('user_id', $userId))
                ->where('status', 'borrowed')->count(),
            'overdue' => Transaction::when($userId, fn($q) => $q->where('user_id', $userId))
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

            $oldStatus = $transaction->status;
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
            'status' => 'returned',
            'returned_at' => now(),
            
        ]);

        $lateDays = max(0, now()->diffInDays($transaction->due_at, false));
        
        if ($lateDays > 0) {
            Fine::create([
                'transaction_id' => $transaction->id,
                'type' => 'late',
                'late_days' => $lateDays,
                'amount' => $lateDays * 5000, 
                'status' => 'unpaid',
                'note' => "Denda keterlambatan {$lateDays} hari"
            ]);
        }

        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'confirm_return',
            'target_type' => Transaction::class,
            'target_id'   => $transaction->id,
            'description' => 'Admin mengkonfirmasi pengembalian buku',
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
        // Only allow for returned transactions
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
            'condition' => 'required|in:good,damaged,lost',
            'fine_amount' => 'nullable|numeric|min:0',
            'fine_type' => 'nullable|in:broken,lost',
            'note' => 'nullable|string|max:1000',
        ]);

        if ($request->condition === 'good') {
            $transaction->update([
                'status' => 'returned', 
                'inspection_note' => $request->note,
                'inspected_at' => now(),
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
                'type' => $request->fine_type ?? ($request->condition === 'lost' ? 'lost' : 'broken'),
                'amount' => $request->fine_amount,
                'late_days' => 0,
                'note' => $request->note,
                'status' => 'unpaid',
            ]);

            $transaction->update([
                'status' => $request->condition,
                'inspection_note' => $request->note,
                'inspected_at' => now(),
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

        return back()->with('success', 'Transaksi berhasil dipulihkan');
    }
}