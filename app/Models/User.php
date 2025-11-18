<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'avatar',
        'city_id',
        'quarter_id',
    ];

    protected $hidden = ['password'];

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            // Si avatar uploadé via formulaire (stocké dans storage/app/public/avatars)
            if (str_starts_with($this->avatar, 'avatars/')) {
                return asset('storage/' . $this->avatar);
            }
            // Sinon, chemin relatif dans public/images/avatars
            return asset($this->avatar);
        }
        return asset('images/avatars/default.png');
    }



    /**
     * Relations
     */
    public function merchantSupplies()
    {
        return $this->hasMany(MerchantSupply::class, 'user_id');
    }

    public function cityModel()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function quarter()
    {
        return $this->belongsTo(Quarter::class, 'quarter_id');
    }

    /**
     * Backwards-compatible accessor: return city name from relation if present, else fallback to raw city column
     */
    public function getCityAttribute($value)
    {
        // Prefer related city name when available
        if ($this->city_id) {
            return $this->cityModel?->name ?? $value;
        }
        return $value;
    }

    public function ordersAsCouturier()
    {
        return $this->hasMany(Order::class, 'couturier_id');
    }

    public function ordersAsMercerie()
    {
        return $this->hasMany(Order::class, 'mercerie_id');
    }

    /**
     * Rôles
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMercerie(): bool
    {
        return $this->role === 'mercerie';
    }

    public function isCouturier(): bool
    {
        return $this->role === 'couturier';
    }
}
