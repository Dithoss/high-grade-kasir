<?php

namespace App\Contracts\Repositories;
use App\Contracts\Interface\WishlistInterface;
use App\Models\Book;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Collection;

class WishlistRepository implements WishlistInterface
{
    /**
     * Get all wishlists for a user
     */
    public function getByUser(User $user): Collection
    {
        return Wishlist::where('user_id', $user->id)
            ->with(['book' => fn($q) => $q->withTrashed(),
                    'book.category' => fn($q) => $q->withTrashed()])
            ->latest()
            ->get();
    }

    /**
     * Add a book to user's wishlist
     */
    public function add(User $user, Book $book): bool
    {
        // Check if already exists
        if ($this->exists($user, $book)) {
            return false;
        }

        Wishlist::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        return true;
    }

    /**
     * Remove a book from user's wishlist
     */
    public function remove(User $user, Book $book): bool
    {
        $deleted = Wishlist::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->delete();

        return $deleted > 0;
    }

    /**
     * Check if a book exists in user's wishlist
     */
    public function exists(User $user, Book $book): bool
    {
        return Wishlist::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->exists();
    }

    /**
     * Toggle wishlist (add if not exists, remove if exists)
     */
    public function toggle(User $user, Book $book): string
    {
        if ($this->exists($user, $book)) {
            $this->remove($user, $book);
            return 'removed';
        }

        $this->add($user, $book);
        return 'added';
    }
}