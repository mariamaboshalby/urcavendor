<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\MarketplaceNotification;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected CommissionService $commissions,
        protected WalletService $wallets
    ) {}

    public function createOrder(array $orderData, array $items, ?User $user = null): Order
    {
        return DB::transaction(function () use ($orderData, $items, $user) {
            $order = Order::create(array_merge([
                'user_id' => $user?->id,
                'order_number' => Order::generateOrderNumber(),
                'shipping' => $orderData['shipping'] ?? 0,
                'status' => 'pending',
                'payment_status' => $orderData['payment_status'] ?? 'unpaid',
                'payment_method' => $orderData['payment_method'] ?? 'cash',
            ], $orderData));

            foreach ($items as $item) {
                $this->createItem($order, $item);
            }

            $this->notifyVendorsNewOrder($order);

            return $order->fresh('items');
        });
    }

    public function createItem(Order $order, array $item): OrderItem
    {
        $productId = $item['product_id'] ?? null;
        $product = isset($item['product']) ? $item['product'] : Product::with('vendor')->find($productId);

        if (! $product) {
            throw new \InvalidArgumentException('Product not found for order item.');
        }

        $vendor = $product->vendor;

        if (! $vendor) {
            throw new \InvalidArgumentException('Product vendor not found for order item.');
        }

        $qty = max(1, (int) ($item['quantity'] ?? 1));
        $price = (float) ($product->price ?? 0);
        $productName = $product->name;
        $vendorId = $vendor->id;
        $rate = $this->commissions->rateForVendor($vendor);
        $calc = $this->commissions->calculate($price, $qty, $rate);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'vendor_id' => $vendorId,
            'product_name' => $productName,
            'price' => $price,
            'quantity' => $qty,
            'total' => $calc['subtotal'],
            'status' => 'pending',
            'commission_rate' => $calc['commission_rate'],
            'commission_amount' => $calc['commission_amount'],
            'vendor_earning' => $calc['vendor_earning'],
        ]);

        if ($vendorId) {
            Commission::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'vendor_id' => $vendorId,
                'order_amount' => $calc['subtotal'],
                'commission_rate' => $calc['commission_rate'],
                'commission_amount' => $calc['commission_amount'],
                'vendor_amount' => $calc['vendor_earning'],
                'status' => 'pending',
            ]);
        }

        if ($product) {
            $product->decrement('stock', min($qty, max(0, (int) $product->stock)));
            $product->increment('sales_count', $qty);
        }

        return $orderItem;
    }

    public function updateItemStatus(OrderItem $item, string $status, ?Vendor $actingVendor = null): void
    {
        if ($actingVendor && (int) $item->vendor_id !== (int) $actingVendor->id) {
            abort(403, 'لا يمكنك تعديل عناصر بائع آخر.');
        }

        DB::transaction(function () use ($item, $status) {
            $previous = $item->status;
            $item->update(['status' => $status]);

            $commission = $item->commission;

            if ($status === 'delivered' && $previous !== 'delivered' && $commission) {
                $this->wallets->markCommissionAvailable($commission);
            }

            if ($status === 'cancelled' && $previous !== 'cancelled' && $commission) {
                $this->wallets->cancelCommission($commission);
            }

            $this->syncOrderStatus($item->order);
            $this->notifyItemStatus($item, $status);
        });
    }

    public function updateOrderStatus(Order $order, string $status): void
    {
        DB::transaction(function () use ($order, $status) {
            $order->update(['status' => $status]);

            foreach ($order->items as $item) {
                if ($item->status === $status) {
                    continue;
                }
                $previous = $item->status;
                $item->update(['status' => $status]);
                $commission = $item->commission;

                if ($status === 'delivered' && $previous !== 'delivered' && $commission) {
                    $this->wallets->markCommissionAvailable($commission);
                }

                if ($status === 'cancelled' && $previous !== 'cancelled' && $commission) {
                    $this->wallets->cancelCommission($commission);
                }
            }
        });
    }

    public function syncOrderStatus(Order $order): void
    {
        $order->load('items');
        $statuses = $order->items->pluck('status')->unique();

        if ($statuses->count() === 1) {
            $order->update(['status' => $statuses->first()]);
            return;
        }

        if ($statuses->contains('cancelled') && $statuses->every(fn ($s) => in_array($s, ['cancelled', 'delivered'], true))) {
            return;
        }

        if ($statuses->contains('shipped')) {
            $order->update(['status' => 'shipped']);
        } elseif ($statuses->contains('processing')) {
            $order->update(['status' => 'processing']);
        }
    }

    protected function notifyVendorsNewOrder(Order $order): void
    {
        $order->load('items.vendor.user');
        $byVendor = $order->items->groupBy('vendor_id');

        foreach ($byVendor as $items) {
            $vendor = $items->first()?->vendor;
            $vendor?->user?->notify(new MarketplaceNotification(
                'طلب جديد',
                'وصلك طلب جديد رقم ' . $order->order_number . ' يحتوي على ' . $items->count() . ' عنصر.',
                route('vendor.orders.show', $order),
                'info'
            ));
        }
    }

    protected function notifyItemStatus(OrderItem $item, string $status): void
    {
        $item->loadMissing('order', 'vendor.user');
        $item->vendor?->user?->notify(new MarketplaceNotification(
            'تحديث حالة الطلب',
            'تم تحديث حالة "' . $item->product_name . '" في الطلب ' . $item->order->order_number . ' إلى ' . $item->status_label,
            route('vendor.orders.show', $item->order),
            'info'
        ));
    }
}
