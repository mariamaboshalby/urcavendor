<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function track()
    {
        $orders = null;
        $guestOrders = null;

        if (auth()->check()) {
            $user = auth()->user();
            $orders = Order::with('items')
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('customer_email', $user->email);
                })
                ->latest()
                ->get();
        } else {
            // Load guest orders from cookie
            $cookieNumbers = json_decode(request()->cookie('guest_orders', '[]'), true) ?: [];
            if (! empty($cookieNumbers)) {
                $guestOrders = Order::with('items')
                    ->whereIn('order_number', $cookieNumbers)
                    ->latest()
                    ->get();
            }
        }

        return view('orders.track', compact('orders', 'guestOrders'));
    }

    public function trackStatus(Request $request)
    {
        $request->validate(['order_number' => 'required|string']);

        $order = Order::with('items')
            ->where('order_number', $request->order_number)
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'الطلب غير موجود. تأكد من رقم الطلب.' : 'Order not found. Please check your order number.',
            ]);
        }

        $steps = ['pending', 'processing', 'shipped', 'delivered'];
        $currentStep = array_search($order->status, $steps);
        if ($currentStep === false) $currentStep = -1;

        return response()->json([
            'success'        => true,
            'order_id'       => $order->id,
            'order_number'   => $order->order_number,
            'status'         => $order->status,
            'status_label'   => $order->status_label,
            'status_color'   => $order->status_color,
            'payment_status' => $order->payment_status,
            'payment_label'  => $order->payment_status_label,
            'customer_name'  => $order->customer_name,
            'created_at'     => $order->created_at->format('d M Y, H:i'),
            'total'          => number_format($order->total, 2),
            'items_count'    => $order->items->sum('quantity'),
            'current_step'   => $currentStep,
            'steps'          => $steps,
        ]);
    }

    public function detail(Order $order)
    {
        // Only allow owner or admin
        if (auth()->check()) {
            $user = auth()->user();
            $allowed = $order->user_id === $user->id
                || $order->customer_email === $user->email;
            if (! $allowed) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $order->load('items');

        $paymentMethodLabels = [
            'cash'     => app()->getLocale() === 'ar' ? 'كاش' : 'Cash',
            'card'     => app()->getLocale() === 'ar' ? 'بطاقة' : 'Card',
            'transfer' => app()->getLocale() === 'ar' ? 'تحويل' : 'Transfer',
        ];

        return response()->json([
            'success' => true,
            'order'   => [
                'order_number'       => $order->order_number,
                'status'             => $order->status,
                'status_label'       => $order->status_label,
                'payment_status'     => $order->payment_status,
                'payment_method'     => $order->payment_method,
                'payment_method_label' => $paymentMethodLabels[$order->payment_method] ?? $order->payment_method,
                'customer_name'      => $order->customer_name,
                'customer_phone'     => $order->customer_phone,
                'customer_address'   => $order->customer_address,
                'city'               => $order->city,
                'subtotal'           => $order->subtotal,
                'shipping'           => $order->shipping,
                'total'              => $order->total,
                'created_at'         => $order->created_at->format('d M Y, H:i'),
                'items'              => $order->items->map(fn($i) => [
                    'product_name' => $i->product_name,
                    'quantity'     => $i->quantity,
                    'price'        => $i->price,
                    'total'        => $i->total,
                ]),
            ],
        ]);
    }

    public function quickOrder(Request $request)
    {
        $validated = $request->validate([
            'product_id'       => 'required|exists:products,id',
            'quantity'         => 'required|integer|min:1|max:999',
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:30',
            'customer_address' => 'required|string|max:500',
            'city'             => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:1000',
        ], [
            'customer_name.required'    => app()->getLocale() === 'ar' ? 'يرجى إدخال الاسم بالكامل' : 'Please enter your full name',
            'customer_phone.required'   => app()->getLocale() === 'ar' ? 'يرجى إدخال رقم الهاتف' : 'Please enter your phone number',
            'customer_address.required' => app()->getLocale() === 'ar' ? 'يرجى إدخال العنوان بالتفصيل' : 'Please enter your delivery address',
        ]);

        $product = \App\Models\Product::with('vendor')->findOrFail($validated['product_id']);
        if (! $product->isPublished()) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'المنتج غير متاح.' : 'Product is not available.',
            ], 422);
        }

        $quantity = (int) $validated['quantity'];
        $unitPrice = (float) $product->price;
        $totalPrice = $unitPrice * $quantity;

        $user = \Illuminate\Support\Facades\Auth::user();

        $order = app(\App\Services\OrderService::class)->createOrder([
            'customer_name'    => $validated['customer_name'],
            'customer_email'   => $user?->email,
            'customer_phone'   => $validated['customer_phone'],
            'customer_address' => $validated['customer_address'],
            'city'             => $validated['city'] ?? null,
            'subtotal'         => $totalPrice,
            'shipping'         => 0,
            'total'            => $totalPrice,
            'payment_method'   => 'cash',
            'payment_status'   => 'unpaid',
            'notes'            => $validated['notes'] ?? null,
        ], [[
            'product' => $product,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $unitPrice,
            'quantity' => $quantity,
        ]], $user);

        $response = response()->json([
            'success'      => true,
            'order_number' => $order->order_number,
            'total'        => number_format($totalPrice, 2),
            'message'      => app()->getLocale() === 'ar' 
                ? 'تم استلام طلبك بنجاح! رقم الطلب الخاص بك: ' . $order->order_number 
                : 'Your order has been placed successfully! Order #: ' . $order->order_number,
        ]);

        // Store order number in cookie for guest users so they can track it later
        if (! auth()->check()) {
            $existing = json_decode(request()->cookie('guest_orders', '[]'), true) ?: [];
            $existing[] = $order->order_number;
            // Keep last 10 orders, expire in 90 days
            $existing = array_slice(array_unique($existing), -10);
            $response->withCookie(cookie('guest_orders', json_encode($existing), 60 * 24 * 90));
        }

        return $response;
    }
}
