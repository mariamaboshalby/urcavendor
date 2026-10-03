<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isVendor();
    }

    public function view(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->vendor && (int) $product->vendor_id === (int) $user->vendor->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isApprovedVendor();
    }

    public function update(User $user, Product $product): bool
    {
        return $this->view($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->view($user, $product);
    }
}
