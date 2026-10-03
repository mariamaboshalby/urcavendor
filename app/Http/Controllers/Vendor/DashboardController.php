<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;

        $itemQuery = OrderItem::where('vendor_id', $vendor->id);

        $stats = [
            'total_sales' => (clone $itemQuery)->where('status', '!=', 'cancelled')->sum('total'),
            'total_orders' => (clone $itemQuery)->distinct('order_id')->count('order_id'),
            'pending_orders' => (clone $itemQuery)->whereIn('status', ['pending', 'processing'])->distinct('order_id')->count('order_id'),
            'completed_orders' => (clone $itemQuery)->where('status', 'delivered')->distinct('order_id')->count('order_id'),
            'total_earnings' => Commission::where('vendor_id', $vendor->id)->whereIn('status', ['available', 'paid'])->sum('vendor_amount'),
            'platform_commission' => Commission::where('vendor_id', $vendor->id)->where('status', '!=', 'cancelled')->sum('commission_amount'),
            'available_balance' => (float) optional($vendor->wallet)->balance,
            'pending_withdrawals' => $vendor->withdrawals()->where('status', 'pending')->sum('amount'),
        ];

        $recentOrders = OrderItem::with('order')
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->take(8)
            ->get()
            ->groupBy('order_id');

        $bestProducts = Product::where('vendor_id', $vendor->id)
            ->orderByDesc('sales_count')
            ->take(5)
            ->get();

        $salesData = OrderItem::where('vendor_id', $vendor->id)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('vendor.dashboard', compact('vendor', 'stats', 'recentOrders', 'bestProducts', 'salesData'));
    }

    public function pending()
    {
        $vendor = auth()->user()->vendor;
        return view('vendor.pending', compact('vendor'));
    }
}
