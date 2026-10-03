<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorWallet;
use App\Models\Withdrawal;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_commission_credit_is_idempotent(): void
    {
        $vendor = Vendor::create([
            'user_id' => User::factory()->create(['role' => 'user'])->id,
            'store_name' => 'Vendor One',
            'store_slug' => 'vendor-one',
            'status' => 'approved',
            'commission_rate' => 10,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Phone',
            'brand' => 'A',
            'category' => 'electronics',
            'description' => 'desc',
            'price' => 1000,
            'old_price' => 1200,
            'stock' => 5,
            'is_active' => true,
            'approval_status' => 'approved',
            'rating' => 5,
            'reviews' => 0,
            'specs' => [],
        ]);

        $order = Order::create([
            'user_id' => User::factory()->create()->id,
            'order_number' => 'URCA-001',
            'customer_name' => 'Cust',
            'customer_email' => 'cust@example.com',
            'customer_phone' => '123',
            'customer_address' => 'addr',
            'city' => 'Cairo',
            'subtotal' => 1000,
            'shipping' => 0,
            'total' => 1000,
            'status' => 'pending',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'vendor_id' => $vendor->id,
            'product_name' => $product->name,
            'price' => 1000,
            'quantity' => 1,
            'total' => 1000,
            'status' => 'delivered',
            'commission_rate' => 10,
            'commission_amount' => 100,
            'vendor_earning' => 900,
        ]);

        $commission = Commission::create([
            'order_id' => $order->id,
            'order_item_id' => $item->id,
            'vendor_id' => $vendor->id,
            'order_amount' => 1000,
            'commission_rate' => 10,
            'commission_amount' => 100,
            'vendor_amount' => 900,
            'status' => 'pending',
        ]);

        $walletService = app(WalletService::class);
        $walletService->markCommissionAvailable($commission);
        $walletService->markCommissionAvailable($commission);

        $wallet = $vendor->wallet()->first();

        $this->assertNotNull($wallet);
        $this->assertSame('900.00', (string) $wallet->balance);
        $this->assertSame('available', $commission->fresh()->status);
    }

    public function test_vendor_cannot_withdraw_more_than_balance(): void
    {
        $vendor = Vendor::create([
            'user_id' => User::factory()->create(['role' => 'user'])->id,
            'store_name' => 'Vendor Two',
            'store_slug' => 'vendor-two',
            'status' => 'approved',
            'commission_rate' => 10,
        ]);

        $wallet = $vendor->wallet;
        $wallet->update([
            'balance' => 200,
            'pending_balance' => 0,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(WalletService::class)->requestWithdrawal($vendor, 500, 'too much');
    }

    public function test_duplicate_withdrawal_approval_does_not_double_deduct(): void
    {
        $vendor = Vendor::create([
            'user_id' => User::factory()->create(['role' => 'user'])->id,
            'store_name' => 'Vendor Three',
            'store_slug' => 'vendor-three',
            'status' => 'approved',
            'commission_rate' => 10,
        ]);

        $wallet = $vendor->wallet;
        $wallet->update([
            'balance' => 300,
            'pending_balance' => 200,
        ]);

        $withdrawal = Withdrawal::create([
            'vendor_id' => $vendor->id,
            'amount' => 200,
            'status' => 'pending',
            'notes' => 'test',
        ]);

        $walletService = app(WalletService::class);
        $walletService->approveWithdrawal($withdrawal);
        $walletService->approveWithdrawal($withdrawal);

        $wallet->refresh();
        $this->assertSame('300.00', (string) $wallet->balance);
        $this->assertSame('approved', $withdrawal->fresh()->status);
    }
}
