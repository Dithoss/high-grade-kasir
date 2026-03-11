<?php

namespace App\Http\Controllers;

use App\Http\Handlers\LibraryCardHandler;
use Illuminate\Http\Request;

class LibraryCardController extends Controller
{
    protected LibraryCardHandler $handler;

    public function __construct(LibraryCardHandler $handler)
    {
        $this->handler = $handler;
    }

    /*
    |--------------------------------------------------------------------------
    | Member Routes
    |--------------------------------------------------------------------------
    */

    /**
     * Show the current user's library card.
     * Route: GET /library-card
     */
    public function show()
    {
        $card = $this->handler->getMyCard();

        if (!$card) {
            return redirect()->back()->with('error', 'Kartu perpustakaan tidak ditemukan.');
        }

        return view('library-card.show', compact('card'));
    }

    /**
     * Update photo on the current user's library card.
     * Route: POST /library-card/photo
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $card = $this->handler->getMyCard();

            if (!$card) {
                return redirect()->back()->with('error', 'Kartu perpustakaan tidak ditemukan.');
            }

            $this->handler->updatePhoto($card->id, $request->file('photo'));

            return redirect()->route('library-card.show')
                ->with('success', 'Foto kartu perpustakaan berhasil diperbarui.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Gagal memperbarui foto: ' . $th->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */

    /**
     * List all library cards (admin).
     * Route: GET /admin/library-cards
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'sort_by', 'sort_dir', 'per_page', 'expiring_soon']);
        $cards   = $this->handler->getAllCards($filters);

        return view('library-card.index', compact('cards', 'filters'));
    }

    /**
     * Show a specific card detail (admin).
     * Route: GET /admin/library-cards/{id}
     */
    public function detail(string $id)
    {
        $card = $this->handler->getById($id);
        return view('library-card.detail', compact('card'));
    }

    /**
     * ★ NEW — Get card data by user ID for the modal (AJAX).
     * Route: GET /admin/library-cards/by-user/{userId}
     */
    public function byUser(string $userId)
    {
        try {
            $card = $this->handler->getCardByUserId($userId);

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kartu perpustakaan tidak ditemukan untuk user ini.',
                ], 404);
            }

            return response()->json($this->formatCardForJson($card));
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Update card status (admin).
     * Route: PATCH /admin/library-cards/{id}/status
     * Supports both regular form POST and AJAX (JSON).
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:active,expired,lost',
        ]);

        try {
            $card = $this->handler->updateStatus($id, $request->status);

            // AJAX response
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'card'    => $this->formatCardForJson($card),
                ]);
            }

            return redirect()->back()->with('success', 'Status kartu berhasil diperbarui.');
        } catch (\Throwable $th) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $th->getMessage()], 422);
            }

            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Regenerate card number and reset expiry (admin).
     * Route: POST /admin/library-cards/{id}/regenerate
     * Supports both regular form POST and AJAX (JSON).
     */
    public function regenerate(Request $request, string $id)
    {
        try {
            $card = $this->handler->regenerateCard($id);

            // AJAX response
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'card'    => $this->formatCardForJson($card),
                ]);
            }

            return redirect()->back()->with('success', 'Kartu perpustakaan berhasil diterbitkan ulang.');
        } catch (\Throwable $th) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $th->getMessage()], 422);
            }

            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Format a LibraryCard model into a consistent JSON structure for the modal.
     */
    private function formatCardForJson(\App\Models\LibraryCard $card): array
    {
        $user     = $card->user;
        $daysLeft = now()->diffInDays($card->expired_at, false);

        // Warning logic
        $warning     = null;
        $warningType = null;

        if ($card->status === 'lost') {
            $warning     = 'Kartu ini dilaporkan hilang. Hubungi petugas untuk penerbitan ulang.';
            $warningType = 'lost';
        } elseif ($card->status === 'expired' || $daysLeft <= 0) {
            $warning     = 'Kartu ini sudah kedaluwarsa.';
            $warningType = 'expired';
        } elseif ($daysLeft <= 30 && $card->status === 'active') {
            $warning     = "Kartu akan kedaluwarsa dalam {$daysLeft} hari.";
            $warningType = 'expiring';
        }

        return [
            'id'                       => $card->id,
            'card_number'              => $card->card_number,
            'status'                   => $card->status,
            'expired_at_formatted'     => $card->expired_at->format('d M Y'),
            'expired_at_formatted_short' => $card->expired_at->format('m/Y'),
            'created_at_formatted'     => $card->created_at->format('d M Y'),
            'photo_url'                => $card->photo_path
                                            ? asset('storage/' . $card->photo_path)
                                            : ($user->image ? asset('storage/' . $user->image) : null),
            'user_name'                => $user->name,
            'user_email'               => $user->email,
            'user_role'                => ucfirst($user->roles->first()?->name ?? 'member'),
            'days_left'                => $daysLeft,
            'warning'                  => $warning,
            'warning_type'             => $warningType,
        ];
    }
    public function myCardJson()
    {
        $card = $this->handler->getMyCard();

        if (!$card) {
            return response()->json(['success' => false, 'message' => 'Kartu tidak ditemukan.'], 404);
        }

        return response()->json($this->formatCardForJson($card));
    }
}