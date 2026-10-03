<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Models\User;

class OrderItemPolicy
{
    public function view(User $user, OrderItem $item): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->vendor && (int) $item->vendor_id === (int) $user->vendor->id;
    }

    public function updateStatus(User $user, OrderItem $item): bool
    {
        return $this->view($user, $item) && $user->isApprovedVendor();
    }
}
