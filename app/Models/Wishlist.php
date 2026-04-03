<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'book_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the wishlist
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the book in the wishlist
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }

    /**
     * Ensure a user can only have one wishlist entry per book
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($wishlist) {
            $exists = static::where('user_id', $wishlist->user_id)
                ->where('book_id', $wishlist->book_id)
                ->exists();

            if ($exists) {
                return false;
            }
        });
    }
}