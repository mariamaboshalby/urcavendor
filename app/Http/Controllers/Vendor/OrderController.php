<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = auth()->user()->vendor->id;

        $query = Order::whereHas('items', fn ($q) => $q->where('vendor_id', $vendorId))
            ->with(['items' => fn ($q) => $q->where('vendor_id', $vendorId)]);

        if ($request->filled('status')) {
            $query->whereHas('items', fn ($q) => $q->where('vendor_id', $vendorId)->where('status', $request->status));
        }

        $orders = $query->latest()->paginate(15);

        return view('vendor.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $vendorId = auth()->user()->vendor->id;

        $order->load(['items' => fn ($q) => $q->where('vendor_id', $vendorId)->with('product')]);

        if ($order->items->isEmpty()) {
            abort(403);
        }

        return view('vendor.orders.show', compact('order'));
    }

    public function updateItemStatus(Request $request, OrderItem $item, OrderService $orders)
    {
        $this->authorize('updateStatus', $item);

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $orders->updateItemStatus($item, $request->status, auth()->user()->vendor);

        return back()->with('success', 'تم تحديث حالة العنصر.');
    }
}
