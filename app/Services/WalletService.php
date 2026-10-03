<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Vendor;
use App\Models\VendorWallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Notifications\MarketplaceNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function ensureWallet(Vendor $vendor): VendorWallet
    {
        return $vendor->wallet()->firstOrCreate(
            ['vendor_id' => $vendor->id],
            ['balance' => 0, 'pending_balance' => 0]
        );
    }

    public function creditAvailable(Vendor $vendor, float $amount, string $reference, string $description, string $type = 'credit'): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($vendor, $amount, $reference, $description, $type) {
            $wallet = VendorWallet::where('vendor_id', $vendor->id)->lockForUpdate()->firstOrCreate(
                ['vendor_id' => $vendor->id],
                ['balance' => 0, 'pending_balance' => 0]
            );

            if (WalletTransaction::where('vendor_id', $vendor->id)
                ->where('reference', $reference)
                ->exists()) {
                return;
            }

            $wallet->increment('balance', $amount);

            WalletTransaction::create([
                'vendor_id' => $vendor->id,
                'type' => $type,
                'amount' => $amount,
                'reference' => $reference,
                'description' => $description,
            ]);
        });
    }

    public function markCommissionAvailable(Commission $commission): void
    {
        DB::transaction(function () use ($commission) {
            $lockedCommission = Commission::whereKey($commission->getKey())->lockForUpdate()->first();

            if (! $lockedCommission || ! in_array($lockedCommission->status, ['pending', 'available'], true)) {
                return;
            }

            if ($lockedCommission->status === 'available') {
                return;
            }

            $lockedCommission->update(['status' => 'available']);

            $vendor = $lockedCommission->vendor()->first();
            if (! $vendor) {
                return;
            }

            $this->creditAvailable(
                $vendor,
                (float) $lockedCommission->vendor_amount,
                'commission:' . $lockedCommission->id,
                'أرباح طلب #' . $lockedCommission->order_id,
                'earning'
            );

            $vendor->user?->notify(new MarketplaceNotification(
                'عمولة متاحة',
                'تم إضافة ' . number_format((float) $lockedCommission->vendor_amount, 2) . ' ج إلى رصيدك المتاح.',
                route('vendor.earnings'),
                'success'
            ));
        });
    }

    public function cancelCommission(Commission $commission): void
    {
        if ($commission->status === 'cancelled') {
            return;
        }

        if ($commission->status === 'available') {
            $wallet = $this->ensureWallet($commission->vendor);
            $wallet->decrement('balance', (float) $commission->vendor_amount);

            WalletTransaction::create([
                'vendor_id' => $commission->vendor_id,
                'type' => 'refund',
                'amount' => (float) $commission->vendor_amount,
                'reference' => 'commission-cancel:' . $commission->id,
                'description' => 'إلغاء أرباح عنصر ملغي',
            ]);
        }

        $commission->update(['status' => 'cancelled']);
    }

    public function requestWithdrawal(Vendor $vendor, float $amount, ?string $notes = null): Withdrawal
    {
        $min = (float) config('marketplace.min_withdrawal_amount', 100);

        if (! is_finite($amount) || $amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'قيمة السحب غير صالحة.',
            ]);
        }

        if ($amount < $min) {
            throw ValidationException::withMessages([
                'amount' => 'الحد الأدنى للسحب هو ' . number_format($min) . ' ج',
            ]);
        }

        return DB::transaction(function () use ($vendor, $amount, $notes, $min) {
            $wallet = VendorWallet::where('vendor_id', $vendor->id)->lockForUpdate()->firstOrCreate(
                ['vendor_id' => $vendor->id],
                ['balance' => 0, 'pending_balance' => 0]
            );

            if ((float) $wallet->balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'الرصيد المتاح غير كافٍ.',
                ]);
            }

            $withdrawal = Withdrawal::create([
                'vendor_id' => $vendor->id,
                'amount' => $amount,
                'status' => 'pending',
                'notes' => $notes,
            ]);

            $wallet->decrement('balance', $amount);
            $wallet->increment('pending_balance', $amount);

            WalletTransaction::create([
                'vendor_id' => $vendor->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'reference' => 'withdrawal-hold:' . $withdrawal->id,
                'description' => 'طلب سحب قيد المراجعة',
            ]);

            return $withdrawal;
        });
    }

    public function approveWithdrawal(Withdrawal $withdrawal, ?string $adminNotes = null): void
    {
        DB::transaction(function () use ($withdrawal, $adminNotes) {
            $lockedWithdrawal = Withdrawal::whereKey($withdrawal->getKey())->lockForUpdate()->first();

            if (! $lockedWithdrawal || $lockedWithdrawal->status !== 'pending') {
                return;
            }

            $lockedWithdrawal->update([
                'status' => 'approved',
                'admin_notes' => $adminNotes,
                'processed_at' => now(),
            ]);
        });

        $withdrawal->vendor->user?->notify(new MarketplaceNotification(
            'تمت الموافقة على طلب السحب',
            'تمت الموافقة على سحب مبلغ ' . number_format((float) $withdrawal->amount, 2) . ' ج.',
            route('vendor.withdrawals.index'),
            'success'
        ));
    }

    public function rejectWithdrawal(Withdrawal $withdrawal, ?string $adminNotes = null): void
    {
        DB::transaction(function () use ($withdrawal, $adminNotes) {
            $lockedWithdrawal = Withdrawal::whereKey($withdrawal->getKey())->lockForUpdate()->first();

            if (! $lockedWithdrawal || ! in_array($lockedWithdrawal->status, ['pending', 'approved'], true)) {
                return;
            }

            $wallet = VendorWallet::where('vendor_id', $lockedWithdrawal->vendor_id)->lockForUpdate()->firstOrCreate(
                ['vendor_id' => $lockedWithdrawal->vendor_id],
                ['balance' => 0, 'pending_balance' => 0]
            );

            $wallet->increment('balance', (float) $lockedWithdrawal->amount);
            $wallet->decrement('pending_balance', (float) $lockedWithdrawal->amount);

            WalletTransaction::updateOrCreate(
                [
                    'vendor_id' => $lockedWithdrawal->vendor_id,
                    'reference' => 'withdrawal-reject:' . $lockedWithdrawal->id,
                ],
                [
                    'type' => 'credit',
                    'amount' => (float) $lockedWithdrawal->amount,
                    'description' => 'إعادة مبلغ سحب مرفوض',
                ]
            );

            $lockedWithdrawal->update([
                'status' => 'rejected',
                'admin_notes' => $adminNotes,
                'processed_at' => now(),
            ]);
        });

        $withdrawal->vendor->user?->notify(new MarketplaceNotification(
            'تم رفض طلب السحب',
            $adminNotes ?: 'تم رفض طلب السحب وإعادة المبلغ إلى رصيدك.',
            route('vendor.withdrawals.index'),
            'danger'
        ));
    }

    public function markWithdrawalPaid(Withdrawal $withdrawal, ?string $adminNotes = null): void
    {
        DB::transaction(function () use ($withdrawal, $adminNotes) {
            $lockedWithdrawal = Withdrawal::whereKey($withdrawal->getKey())->lockForUpdate()->first();

            if (! $lockedWithdrawal || ! in_array($lockedWithdrawal->status, ['pending', 'approved'], true)) {
                return;
            }

            if ($lockedWithdrawal->status === 'paid') {
                return;
            }

            $wallet = VendorWallet::where('vendor_id', $lockedWithdrawal->vendor_id)->lockForUpdate()->firstOrCreate(
                ['vendor_id' => $lockedWithdrawal->vendor_id],
                ['balance' => 0, 'pending_balance' => 0]
            );

            $wallet->decrement('pending_balance', (float) $lockedWithdrawal->amount);

            $lockedWithdrawal->vendor->commissions()
                ->where('status', 'available')
                ->limit(50)
                ->update(['status' => 'paid']);

            $lockedWithdrawal->update([
                'status' => 'paid',
                'admin_notes' => $adminNotes ?? $lockedWithdrawal->admin_notes,
                'processed_at' => now(),
            ]);
        });

        $withdrawal->vendor->user?->notify(new MarketplaceNotification(
            'تم دفع طلب السحب',
            'تم تحويل مبلغ ' . number_format((float) $withdrawal->amount, 2) . ' ج.',
            route('vendor.withdrawals.index'),
            'success'
        ));
    }
}
