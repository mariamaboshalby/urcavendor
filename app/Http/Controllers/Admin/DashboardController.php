<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::active()->count(),
            'pending_products' => Product::where('approval_status', 'pending')->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'platform_commission' => \App\Models\Commission::where('status', '!=', 'cancelled')->sum('commission_amount'),
            'pending_vendors' => \App\Models\Vendor::where('status', 'pending')->count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'total_blog_posts' => \App\Models\BlogPost::count(),
            'total_downloads' => \App\Models\Download::count(),
            'total_about_entries' => \App\Models\AboutStat::count() + \App\Models\AboutTeam::count() + \App\Models\AboutValue::count(),
        ];

        $recent_orders = Order::with('items')
            ->latest()
            ->take(5)
            ->get();

        $low_stock_products = Product::where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->take(10)
            ->get();

        $categories = \App\Models\Category::all();

        $top_products = Product::withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->take(5)
            ->get();

        // Sales chart data (last 7 days)
        $sales_data = Order::where('created_at', '>=', now()->subDays(7))
            ->where('payment_status', 'paid')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recent_orders',
            'low_stock_products',
            'top_products',
            'sales_data',
            'categories'
        ));
    }
}
