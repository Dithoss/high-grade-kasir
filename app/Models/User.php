<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'phone_number'
    ];
    protected $guard_name = 'web';


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }
    public function fines()
    {
        return $this->hasManyThrough(
            Fine::class,
            Transaction::class,
            'user_id',       
            'transaction_id' 
        );
    }
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
    public function libraryCard()
    {
        return $this->hasOne(LibraryCard::class);
    }
    public function preorders()
    {
        return $this->hasMany(Preorder::class);
    }
}
