<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Vendor;
use App\Notifications\MarketplaceNotification;
use App\Services\CacheService;

class ProductApprovalService
{
    public function approve(Product $product): void
    {
        $product->update([
            'approval_status' => 'approved',
            'is_active' => true,
            'rejection_reason' => null,
        ]);

        CacheService::clearProductCaches();
        CacheService::clearProductShowCache($product->id);

        $product->vendor?->user?->notify(new MarketplaceNotification(
            'تم قبول المنتج',
            'تمت الموافقة على المنتج: ' . $product->name,
            route('vendor.products.index'),
            'success'
        ));
    }

    public function reject(Product $product, ?string $reason = null): void
    {
        $product->update([
            'approval_status' => 'rejected',
            'is_active' => false,
            'rejection_reason' => $reason,
        ]);

        CacheService::clearProductCaches();
        CacheService::clearProductShowCache($product->id);

        $product->vendor?->user?->notify(new MarketplaceNotification(
            'تم رفض المنتج',
            'تم رفض المنتج: ' . $product->name . ($reason ? ' — ' . $reason : ''),
            route('vendor.products.index'),
            'danger'
        ));
    }

    public function disable(Product $product): void
    {
        $product->update([
            'approval_status' => 'inactive',
            'is_active' => false,
        ]);

        CacheService::clearProductCaches();
        CacheService::clearProductShowCache($product->id);
    }
}
