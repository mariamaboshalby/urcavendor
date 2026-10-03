<?php

namespace App\Services;

use App\Models\Setting;

class CommissionService
{
    public function defaultRate(): float
    {
        $fromSettings = Setting::get('default_commission_rate');
        if ($fromSettings !== null && $fromSettings !== '') {
            return (float) $fromSettings;
        }

        return (float) config('marketplace.default_commission_rate', 10);
    }

    public function rateForVendor(?\App\Models\Vendor $vendor): float
    {
        if ($vendor && $vendor->commission_rate !== null) {
            return (float) $vendor->commission_rate;
        }

        return $this->defaultRate();
    }

    /**
     * @return array{subtotal: float, commission_rate: float, commission_amount: float, vendor_earning: float}
     */
    public function calculate(float $unitPrice, int $quantity, float $rate): array
    {
        $subtotal = round($unitPrice * $quantity, 2);
        $commissionAmount = round($subtotal * ($rate / 100), 2);
        $vendorEarning = round($subtotal - $commissionAmount, 2);

        return [
            'subtotal' => $subtotal,
            'commission_rate' => $rate,
            'commission_amount' => $commissionAmount,
            'vendor_earning' => $vendorEarning,
        ];
    }
}
