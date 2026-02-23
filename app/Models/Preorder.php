<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Preorder extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'book_id',
        'expected_borrow_date',
        'status',
        'queue_position',
        'notified_at',
        'confirmed_at',
        'expired_at',
        'notes',
    ];

    protected $casts = [
        'expected_borrow_date' => 'date',
        'notified_at'          => 'datetime',
        'confirmed_at'         => 'datetime',
        'expired_at'           => 'datetime',
    ];

    //  Relationships 
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    //  Scopes 
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'ready']);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeForBook($query, $bookId)
    {
        return $query->where('book_id', $bookId);
    }

    //  Helpers
    public function isActive(): bool
    {
        return in_array($this->status, ['waiting', 'ready']);
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'waiting'   => 'Menunggu',
            'ready'     => 'Siap Dipinjam',
            'confirmed' => 'Dikonfirmasi',
            'cancelled' => 'Dibatalkan',
            'expired'   => 'Kedaluwarsa',
            default     => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'waiting'   => 'amber',
            'ready'     => 'green',
            'confirmed' => 'blue',
            'cancelled' => 'red',
            'expired'   => 'gray',
            default     => 'gray',
        };
    }
}