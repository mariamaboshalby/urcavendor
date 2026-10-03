<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Withdrawal;

class FinanceController extends Controller
{
    public function index()
    {
        $stats = [
            'total_sales' => Order::where('status', '!=', 'cancelled')->sum('total'),
            'platform_commission' => Commission::where('status', '!=', 'cancelled')->sum('commission_amount'),
            'vendor_earnings' => Commission::whereIn('status', ['available', 'paid'])->sum('vendor_amount'),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->sum('amount'),
            'paid_withdrawals' => Withdrawal::where('status', 'paid')->sum('amount'),
        ];

        $bestVendors = Vendor::withSum(['orderItems as sales' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'total')
            ->orderByDesc('sales')
            ->take(8)
            ->get();

        $bestProducts = Product::orderByDesc('sales_count')->take(8)->get();

        $salesPerVendor = Vendor::withSum(['commissions as commission_total' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'commission_amount')
            ->withSum(['commissions as vendor_total' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'vendor_amount')
            ->orderByDesc('commission_total')
            ->take(10)
            ->get();

        return view('admin.finance.index', compact('stats', 'bestVendors', 'bestProducts', 'salesPerVendor'));
    }
}
