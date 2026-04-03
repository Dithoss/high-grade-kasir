<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Book extends Model
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;
    protected $keyType = 'string';     
    public $incrementing = false;
    protected $fillable = [
        'name', 'barcode', 'stock', 'writer', 'category_id','slug', 'image','sypnosis'];

    protected $guarded = [];
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class)->withTrashed();
    }
    public function category(){
        return $this->belongsTo(Category::class)->withTrashed();
    }
    public function wishlists()
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }
    public function views(){
        return $this->hasMany(Algorithm::class);
    }
    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists')
                    ->withTimestamps();
    }

}
