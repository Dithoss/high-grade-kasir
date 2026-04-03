<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Fine extends Model
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'type',
        'late_days',
        'amount',
        'note',
        'status',
        'paid_at',
        'payment_method',
        'payment_reference',
        'payment_requested_at',
        'rejection_note',
        'rejected_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'payment_requested_at' => 'datetime',
        'rejected_at' => 'datetime',
        'deleted_at' => 'datetime', 
    ];

     public function transaction()
    {
        return $this->belongsTo(Transaction::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('transaction', fn($q) => $q->where('user_id', $userId));
    }

}
