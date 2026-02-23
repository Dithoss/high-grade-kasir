<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * EC-18: Rate limiting untuk semua endpoint preorder.
 *
 * Aturan:
 *   - POST /preorders (buat baru) : maks 5 per user per menit
 *   - Semua aksi preorder         : maks 20 per user per menit
 */
class PreorderThrottle
{
    public function handle(Request $request, Closure $next): Response
    {
        // Identifikasi: pakai user ID jika login, fallback ke IP
        $identity = $request->user()?->id ?? $request->ip();

        // ── Rate limit khusus POST (buat preorder baru) ──────────
        if ($request->isMethod('POST')) {
            $storeKey = "preorder_store:{$identity}";

            if (RateLimiter::tooManyAttempts($storeKey, maxAttempts: 5)) {
                $retryAfter = RateLimiter::availableIn($storeKey);
                return $this->tooManyResponse($request, $retryAfter, 'Terlalu banyak permintaan preorder.');
            }

            RateLimiter::hit($storeKey, decaySeconds: 60);
        }

        // ── Rate limit global semua aksi preorder ────────────────
        $globalKey = "preorder_global:{$identity}";

        if (RateLimiter::tooManyAttempts($globalKey, maxAttempts: 20)) {
            $retryAfter = RateLimiter::availableIn($globalKey);
            return $this->tooManyResponse($request, $retryAfter, 'Terlalu banyak request. Coba lagi sebentar.');
        }

        RateLimiter::hit($globalKey, decaySeconds: 60);

        /** @var Response $response */
        $response = $next($request);

        // Sertakan header info sisa kuota
        $remaining = max(0, 20 - RateLimiter::attempts($globalKey));
        $response->headers->set('X-RateLimit-Limit', '20');
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);
        $response->headers->set('X-RateLimit-Reset', (string) (now()->timestamp + RateLimiter::availableIn($globalKey)));

        return $response;
    }

    // ─────────────────────────────────────────────────────────────
    private function tooManyResponse(Request $request, int $retryAfter, string $message): Response
    {
        if ($request->expectsJson() || $request->isMethod('PUT') || $request->isMethod('DELETE')) {
            return response()->json([
                'success'     => false,
                'message'     => $message,
                'retry_after' => $retryAfter,
            ], 429)->withHeaders([
                'Retry-After'          => $retryAfter,
                'X-RateLimit-Limit'    => '20',
                'X-RateLimit-Remaining'=> '0',
            ]);
        }

        return back()
            ->with('error', "{$message} Coba lagi dalam {$retryAfter} detik.")
            ->withHeaders(['Retry-After' => $retryAfter]);
    }
}