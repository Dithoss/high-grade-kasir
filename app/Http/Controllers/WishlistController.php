<?php
namespace App\Http\Controllers;

use App\Contracts\Interface\WishlistInterface;
use App\Models\Book;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistInterface $wishlistRepo
    ) {}

    public function index()
    {
        $wishlists = $this->wishlistRepo->getByUser(auth()->user());
        return view('wishlist.index', compact('wishlists'));
    }

public function toggle(string $bookId)
{
    $book = Book::findOrFail($bookId);

    $user = auth()->user();

    if ($this->wishlistRepo->exists($user, $book)) {
        $this->wishlistRepo->remove($user, $book);
        return response()->json(['status' => 'removed', 'message' => 'Book removed from wishlist']);
    }

    $this->wishlistRepo->add($user, $book);
    return response()->json(['status' => 'added', 'message' => 'Book added to wishlist']);
}
}
