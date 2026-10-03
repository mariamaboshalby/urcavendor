<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_service_ignores_manipulated_vendor_id_and_price(): void
    {
        $vendorA = Vendor::create([
            'user_id' => User::factory()->create(['role' => 'user'])->id,
            'store_name' => 'Vendor A',
            'store_slug' => 'vendor-a',
            'status' => 'approved',
            'commission_rate' => 10,
        ]);

        $vendorB = Vendor::create([
            'user_id' => User::factory()->create(['role' => 'user'])->id,
            'store_name' => 'Vendor B',
            'store_slug' => 'vendor-b',
            'status' => 'approved',
            'commission_rate' => 20,
        ]);

        $product = Product::create([
            'vendor_id' => $vendorA->id,
            'name' => 'Laptop',
            'brand' => 'BrandX',
            'category' => 'electronics',
            'description' => 'Sample product',
            'price' => 150.00,
            'old_price' => 180.00,
            'stock' => 10,
            'is_active' => true,
            'approval_status' => 'approved',
            'rating' => 5,
            'reviews' => 0,
            'specs' => [],
        ]);

        $order = Order::create([
            'user_id' => User::factory()->create()->id,
            'order_number' => 'URCA-TEST-1',
            'customer_name' => 'Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '123456',
            'customer_address' => 'Address',
            'city' => 'Cairo',
            'subtotal' => 150,
            'shipping' => 0,
            'total' => 150,
            'status' => 'pending',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
        ]);

        $orderItem = app(OrderService::class)->createItem($order, [
            'product' => $product,
            'product_id' => $product->id,
            'product_name' => 'Tampered Name',
            'price' => 9999.99,
            'quantity' => 2,
            'vendor_id' => $vendorB->id,
        ]);

        $this->assertSame($vendorA->id, $orderItem->vendor_id);
        $this->assertSame(150.00, (float) $orderItem->price);
        $this->assertSame($product->id, $orderItem->product_id);
        $this->assertSame($product->name, $orderItem->product_name);
    }
}
