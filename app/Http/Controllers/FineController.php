<?php

namespace App\Http\Controllers;

use App\Contracts\Interface\FineInterface;
use App\Models\Fine;
use App\Services\StripeService;
use App\Services\TripayService;
use Illuminate\Http\Request;

class FineController extends Controller
{
    public function __construct(
        protected FineInterface $fineRepository
    ) {}

   public function index()
    {
        $fines = $this->fineRepository->getByUser(auth()->id());
        $unpaidFinesAmount = $fines->where('status', 'unpaid')->sum('amount');
        $paidTotal = $fines->where('status', 'paid')->sum('amount');
        $unpaidTotal = $unpaidFinesAmount; // used in the alert banner
        
        return view('fines.index', compact('fines', 'unpaidFinesAmount', 'unpaidTotal', 'paidTotal'));
    }

    public function adminIndex(Request $request)
    {
        $fines = $this->fineRepository->getAll(
            $request->only(['status', 'type'])
        );

        return view('fines.admin.index', compact('fines'));
    }

    public function markPaid(Fine $fine)
    {
        if ($fine->status === 'paid') {
            return back()->with('info', 'Denda sudah lunas.');
        }

        $this->fineRepository->markAsPaid($fine);

        return back()->with('success', 'Denda berhasil ditandai lunas.');
    }

    public function pay(Request $request, Fine $fine)
{
    if ($fine->transaction->user_id !== auth()->id()) {
        return back()->with('error', 'Anda tidak memiliki akses untuk membayar denda ini.');
    }

    if ($fine->status === 'paid') {
        return back()->with('info', 'Denda sudah lunas.');
    }

    if ($fine->status === 'pending_confirmation') {
        return back()->with('info', 'Pembayaran Anda sedang menunggu konfirmasi admin.');
    }

    $request->validate([
        'payment_method' => 'required|in:cash,stripe'
    ]);

    // Delegasi ke CheckoutController
    return app(CheckoutController::class)->pay($request, $fine);
}

    public function confirmPayment(Fine $fine)
    {
        if ($fine->status !== 'pending_confirmation') {
            return back()->with('error', 'Denda ini tidak dalam status menunggu konfirmasi.');
        }

        if ($fine->payment_method !== 'cash') {
            return back()->with('error', 'Hanya pembayaran offline yang dapat dikonfirmasi.');
        }

        $this->fineRepository->markAsPaid($fine);

        return back()->with('success', 'Pembayaran offline berhasil dikonfirmasi.');
    }


    public function rejectPayment(Request $request, Fine $fine)
    {
        if ($fine->status !== 'pending_confirmation') {
            return back()->with('error', 'Denda ini tidak dalam status menunggu konfirmasi.');
        }

        $request->validate([
            'rejection_note' => 'nullable|string|max:500'
        ]);

        $fine->update([
            'status' => 'unpaid',
            'payment_method' => null,
            'payment_requested_at' => null,
            'rejection_note' => $request->rejection_note,
            'rejected_at' => now(),
        ]);

        return back()->with('success', 'Pembayaran offline ditolak. User dapat mengajukan kembali.');
    }
}