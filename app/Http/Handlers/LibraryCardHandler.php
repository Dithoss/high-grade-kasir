<?php

namespace App\Http\Handlers;

use App\Contracts\Interface\LibraryCardInterface;
use App\Helpers\UploadHelper;
use App\Models\LibraryCard;
use Illuminate\Support\Facades\DB;

class LibraryCardHandler
{
    protected LibraryCardInterface $libraryCardInterface;

    public function __construct(LibraryCardInterface $libraryCardInterface)
    {
        $this->libraryCardInterface = $libraryCardInterface;
    }

    /**
     * Get current user's library card.
     */
    public function getMyCard(): ?LibraryCard
    {
        return $this->libraryCardInterface->findByUserId(auth()->id());
    }

    /**
     * Get a card by ID (admin use).
     */
    public function getById(string $id): LibraryCard
    {
        return $this->libraryCardInterface->findById($id);
    }

    /**
     * Get all library cards (admin use).
     */
    public function getAllCards(array $filters = [])
    {
        return $this->libraryCardInterface->getAll($filters);
    }

    /**
     * Update photo on the library card.
     */
    public function updatePhoto(string $cardId, $imageFile): LibraryCard
    {
        DB::beginTransaction();
        try {
            $card = $this->libraryCardInterface->findById($cardId);

            // Delete old photo if exists
            if ($card->photo_path) {
                UploadHelper::deleteFile($card->photo_path);
            }

            $photoPath = UploadHelper::uploadImage($imageFile, 'library_card_photos');

            $updated = $this->libraryCardInterface->update($cardId, [
                'photo_path' => $photoPath,
            ]);

            DB::commit();
            return $updated;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Admin: change card status (active / expired / lost).
     */
    public function updateStatus(string $cardId, string $status): LibraryCard
    {
        $allowed = ['active', 'expired', 'lost'];

        if (!in_array($status, $allowed)) {
            throw new \InvalidArgumentException("Status tidak valid: {$status}");
        }

        DB::beginTransaction();
        try {
            $card = $this->libraryCardInterface->updateStatus($cardId, $status);
            DB::commit();
            return $card;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Admin: regenerate card number and reset expiry.
     */
    public function regenerateCard(string $cardId): LibraryCard
    {
        DB::beginTransaction();
        try {
            $card = $this->libraryCardInterface->regenerateCard($cardId);
            DB::commit();
            return $card;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Check if user's card is still active and not expired.
     */
    public function isCardValid(string $userId): bool
    {
        $card = $this->libraryCardInterface->findByUserId($userId);

        if (!$card) return false;

        return $card->status === 'active' && $card->expired_at->isFuture();
    }
    public function getCardByUserId(string $userId): ?\App\Models\LibraryCard
    {
        return $this->libraryCardInterface->findByUserId($userId);
    }
}