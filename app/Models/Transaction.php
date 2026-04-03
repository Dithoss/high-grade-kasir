<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Transaction extends Model
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'status',
        'borrowed_at',
        'returned_at',
        'due_at',
        'receipt_number',

        'is_extended',
        'extended_due_at',
        'extension_requested_at',
        'extension_approved_at',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'returned_at' => 'datetime',
        'due_at' => 'datetime',

        'is_extended' => 'boolean',
        'extended_due_at' => 'date',
        'extension_requested_at' => 'datetime',
        'extension_approved_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function fine()
    {
        return $this->hasMany(Fine::class)->withTrashed();
    }

    public function canBeExtended(): bool
    {
        return $this->status === 'borrowed'
            && !$this->is_extended
            && is_null($this->extension_requested_at)
            && now()->lte($this->due_at);
    }

    public function hasPendingExtension(): bool
    {
        return !is_null($this->extension_requested_at)
            && is_null($this->extension_approved_at);
    }

    public function alreadyExtended(): bool
    {
        return $this->is_extended === true;
    }
    public function book()
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }
}
