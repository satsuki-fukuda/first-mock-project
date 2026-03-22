<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // これを追加
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'postal_code',
        'address',
        'building',
        'profile_image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'buyer_id');
    }

    public function sales()
    {
        return $this->hasMany(Purchase::class, 'seller_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'user_id');
    }

    public function likedItems()
    {
        return $this->belongsToMany(Item::class, 'likes');
    }
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

}
