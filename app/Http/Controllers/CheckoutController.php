<?php

namespace App\Http\Controllers;

use App\Models\Fine;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function __construct(protected StripeService $stripeService) {}

    /**
     * Handle payment initiation.
     * Supports: 'stripe' (online) or 'cash' (offline).
     */
    public function pay(Request $request, Fine $fine)
    {
        // Authorization: only the fine owner can pay
        if ($fine->transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($fine->status !== 'unpaid') {
            return back()->with('error', 'Denda ini sudah dibayar atau sedang diproses.');
        }

        $method = $request->input('payment_method', 'stripe');

        // ── CASH / OFFLINE ────────────────────────────────────────────────
        if ($method === 'cash') {
            // Check if cash payment is enabled via system settings
            if (!config('system.cash_payment_enabled', true)) {
                return back()->with('error', 'Pembayaran tunai saat ini tidak tersedia.');
            }

            $fine->update([
                'status'                 => 'pending_confirmation',
                'payment_method'         => 'cash',
                'payment_requested_at'   => now(),
            ]);

            return back()->with('success', '✅ Permintaan pembayaran tunai telah dikirim. Silakan datang ke perpustakaan untuk menyelesaikan pembayaran.');
        }

        // ── STRIPE / ONLINE ───────────────────────────────────────────────
        if ($method === 'stripe') {
            // Check if online payment is enabled via system settings
            if (!config('system.online_payment_enabled', true)) {
                return back()->with('error', 'Pembayaran online saat ini tidak tersedia.');
            }

            try {
                $session = $this->stripeService->createCheckoutSession($fine);

                // Store session ID on the fine for webhook reconciliation
                $fine->update([
                    'payment_method'       => 'stripe',
                    'stripe_session_id'    => $session->id,
                    'payment_requested_at' => now(),
                ]);

                return redirect($session->url);

            } catch (\Exception $e) {
                report($e);
                return back()->with('error', 'Gagal membuat sesi pembayaran. Silakan coba lagi.');
            }
        }

        return back()->with('error', 'Metode pembayaran tidak valid.');
    }

    /**
     * Stripe redirects here after successful payment.
     * NOTE: This is NOT the source of truth — the webhook is.
     * We just verify the session and show a confirmation page.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('fines.index')->with('error', 'Session tidak ditemukan.');
        }

        try {
            Stripe::setApiKey(config('stripe.secret_key'));
            $session = Session::retrieve($sessionId);

            // Guard: only show success if payment actually completed
            if ($session->payment_status !== 'paid') {
                return redirect()->route('fines.index')
                    ->with('error', 'Pembayaran belum berhasil. Silakan coba lagi.');
            }

            $fineId = $session->metadata->fine_id ?? null;
            $fine   = $fineId ? Fine::find($fineId) : null;

            // Fallback: if webhook hasn't fired yet, mark paid here too
            if ($fine && $fine->status !== 'paid') {
                $fine->update([
                    'status'  => 'paid',
                    'paid_at' => now(),
                ]);
            }

            return view('payment.success', compact('fine', 'session'));

        } catch (\Exception $e) {
            report($e);
            return redirect()->route('fines.index')
                ->with('error', 'Terjadi kesalahan saat memverifikasi pembayaran.');
        }
    }

    /**
     * Stripe redirects here if user cancels payment.
     */
    public function cancel()
    {
        return redirect()->route('fines.index')
            ->with('error', '❌ Pembayaran dibatalkan. Denda Anda belum dibayar.');
    }
}