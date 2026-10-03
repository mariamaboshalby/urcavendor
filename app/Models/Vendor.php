<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Vendor extends Model
{
    protected $fillable = [
        'user_id',
        'store_name',
        'store_slug',
        'phone',
        'logo',
        'description',
        'status',
        'commission_rate',
        'rating',
        'reviews_count',
        'rejection_reason',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Vendor $vendor) {
            if (empty($vendor->store_slug) && $vendor->store_name) {
                $vendor->store_slug = static::uniqueSlug($vendor->store_name);
            }
        });

        static::created(function (Vendor $vendor) {
            $vendor->wallet()->create([
                'balance' => 0,
                'pending_balance' => 0,
            ]);
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'store';
        $slug = $base;
        $i = 1;
        while (static::where('store_slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(VendorWallet::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function canSell(): bool
    {
        return $this->isApproved();
    }

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo) {
            return asset('storage/' . ltrim($this->logo, '/'));
        }

        return asset('images/urca_logo.png');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'قيد المراجعة',
            'approved' => 'مقبول',
            'rejected' => 'مرفوض',
            'suspended' => 'موقوف',
            default => $this->status,
        };
    }
}
