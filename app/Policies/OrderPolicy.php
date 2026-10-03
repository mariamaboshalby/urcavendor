<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($order->user_id === $user->id || $order->customer_email === $user->email) {
            return true;
        }

        if ($user->vendor) {
            return $order->items()->where('vendor_id', $user->vendor->id)->exists();
        }

        return false;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
