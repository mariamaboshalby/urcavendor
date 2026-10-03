<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Withdrawal;

class WithdrawalPolicy
{
    public function view(User $user, Withdrawal $withdrawal): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->vendor && (int) $withdrawal->vendor_id === (int) $user->vendor->id;
    }

    public function create(User $user): bool
    {
        return $user->isApprovedVendor();
    }

    public function update(User $user, Withdrawal $withdrawal): bool
    {
        return $user->isAdmin();
    }
}
