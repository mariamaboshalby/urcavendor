<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\MarketplaceNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorService
{
    public function register(array $data, ?UploadedFile $logo = null): Vendor
    {
        return DB::transaction(function () use ($data, $logo) {
            if (! empty($data['user_id'])) {
                $user = User::findOrFail($data['user_id']);
                $user->update([
                    'role' => 'vendor',
                    'phone' => $data['phone'] ?? $user->phone,
                ]);
            } else {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'password' => Hash::make($data['password']),
                    'role' => 'vendor',
                    'is_active' => true,
                ]);
            }

            $vendor = Vendor::create([
                'user_id' => $user->id,
                'store_name' => $data['store_name'],
                'store_slug' => Vendor::uniqueSlug($data['store_name']),
                'phone' => $data['phone'] ?? $user->phone,
                'description' => $data['description'] ?? null,
                'status' => 'pending',
                'commission_rate' => app(CommissionService::class)->defaultRate(),
            ]);

            if ($logo) {
                $this->storeLogo($vendor, $logo);
            }

            return $vendor;
        });
    }

    public function approve(Vendor $vendor): void
    {
        $vendor->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        if ($vendor->user && $vendor->user->role !== 'admin') {
            $vendor->user->update(['role' => 'vendor']);
        }

        $vendor->user?->notify(new MarketplaceNotification(
            'تم قبول متجرك',
            'تمت الموافقة على متجر ' . $vendor->store_name . '. يمكنك الآن إضافة المنتجات واستقبال الطلبات.',
            route('vendor.dashboard'),
            'success'
        ));
    }

    public function reject(Vendor $vendor, ?string $reason = null): void
    {
        $vendor->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $vendor->user?->notify(new MarketplaceNotification(
            'تم رفض طلب المتجر',
            $reason ?: 'تم رفض طلب تسجيلك كبائع.',
            route('vendor.pending'),
            'danger'
        ));
    }

    public function suspend(Vendor $vendor, ?string $reason = null): void
    {
        $vendor->update([
            'status' => 'suspended',
            'rejection_reason' => $reason,
        ]);
    }

    public function storeLogo(Vendor $vendor, UploadedFile $logo): void
    {
        if ($vendor->logo) {
            Storage::disk('public')->delete($vendor->logo);
        }

        $path = $logo->store('vendors/logos', 'public');
        $vendor->update(['logo' => $path]);
    }

    public function defaultVendor(): Vendor
    {
        $vendor = Vendor::where('store_slug', config('marketplace.default_store_slug'))->first();

        if ($vendor) {
            return $vendor;
        }

        $admin = User::where('role', 'admin')->orderBy('id')->first();

        if (! $admin) {
            $admin = User::create([
                'name' => 'مدير النظام',
                'email' => 'admin@admin.com',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        return Vendor::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'store_name' => config('marketplace.default_store_name'),
                'store_slug' => config('marketplace.default_store_slug'),
                'status' => 'approved',
                'commission_rate' => 0,
                'description' => 'Official platform store',
            ]
        );
    }

    public function refreshVendorRating(Vendor $vendor): void
    {
        $stats = $vendor->reviews()->approved()
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')
            ->first();

        $vendor->update([
            'rating' => round((float) ($stats->avg_rating ?? 0), 2),
            'reviews_count' => (int) ($stats->total ?? 0),
        ]);
    }
}
