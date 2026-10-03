<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'phone',
        'address',
        'city',
    ];

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
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    public function isCustomer(): bool
    {
        return in_array($this->role, ['user', 'customer'], true);
    }

    public function isApprovedVendor(): bool
    {
        return $this->isVendor() && $this->vendor && $this->vendor->isApproved();
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function wishlists()
    {
        return $this->hasMany(\App\Models\Wishlist::class);
    }

    public function wishlistProductIds(): array
    {
        return $this->wishlists()->pluck('product_id')->map(fn($id) => (int)$id)->toArray();
    }
}
