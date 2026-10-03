<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'vendor_id',
        'name', 'brand', 'category', 'description',
        'price', 'old_price', 'image', 'stock',
        'badge', 'badge_color', 'rating', 'reviews',
        'sales_count', 'views_count',
        'specs', 'is_active', 'is_featured',
        'approval_status', 'rejection_reason',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'old_price'   => 'decimal:2',
        'stock'       => 'integer',
        'rating'      => 'integer',
        'reviews'     => 'integer',
        'sales_count' => 'integer',
        'views_count' => 'integer',
        'specs'       => 'array',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
    ];

    /* ── Media Collections ── */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product-images')
             ->useFallbackUrl(asset('images/placeholder.png'))
             ->useFallbackPath(public_path('images/placeholder.png'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // 'thumb': used in product listing cards (small, fast-loading)
        $this->addMediaConversion('thumb')
             ->width(300)
             ->height(240)
             ->sharpen(5)
             ->optimize()
             ->performOnCollections('product-images');

        // 'card': used in product detail page main image
        $this->addMediaConversion('card')
             ->width(600)
             ->height(600)
             ->sharpen(5)
             ->optimize()
             ->performOnCollections('product-images');
    }

    /* ── Helpers ── */
    public function getMainImageUrl(string $conversion = ''): string
    {
        $media = $this->getFirstMedia('product-images');
        if (! $media) {
            return '';
        }

        // Use generated_conversions to check availability without file_exists() disk hit.
        if ($conversion) {
            $generated = $media->generated_conversions ?? [];
            if (! empty($generated[$conversion])) {
                return '/storage/' . ltrim($media->getPathRelativeToRoot($conversion), '/');
            }
        }

        return '/storage/' . ltrim($media->getPathRelativeToRoot(''), '/');
    }

    /* ── Scopes ── */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopePublished($query)
    {
        return $query->active()
            ->approved()
            ->whereHas('vendor', fn ($q) => $q->where('status', 'approved'));
    }

    public function isPublished(): bool
    {
        return $this->is_active
            && $this->approval_status === 'approved'
            && $this->vendor
            && $this->vendor->isApproved();
    }

    /* ── Relations ── */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category', 'slug');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function productReviews()
    {
        return $this->hasMany(ProductReview::class);
    }
}
