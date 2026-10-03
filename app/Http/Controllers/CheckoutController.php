<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty.');
        }

        $user  = Auth::user();
        $total = collect($cart)->sum(fn($i) => $i['price'] * ($i['qty'] ?? $i['quantity'] ?? 1));

        // Auto-place order if user has all required info saved
        if ($user->address && $user->city && $user->phone) {
            return $this->autoPlaceOrder($user, $cart, $total);
        }

        return view('checkout.index', compact('cart', 'total', 'user'));
    }

    /**
     * Automatically place the order using the user's saved profile data.
     */
    private function autoPlaceOrder($user, array $cart, float $total)
    {
        $items = $this->cartToOrderItems($cart);

        $order = app(OrderService::class)->createOrder([
            'customer_name'    => $user->name,
            'customer_email'   => $user->email,
            'customer_phone'   => $user->phone,
            'customer_address' => $user->address,
            'city'             => $user->city,
            'subtotal'         => $total,
            'shipping'         => 0,
            'total'            => $total,
            'payment_method'   => 'cash',
            'payment_status'   => 'unpaid',
            'notes'            => null,
        ], $items, $user);

        session()->forget('cart');
        Cookie::queue(Cookie::forget('urca_cart'));

        return redirect()->route('checkout.success', $order->order_number);
    }

    public function store(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email',
            'phone'          => 'required|string|max:20',
            'address'        => 'required|string|max:500',
            'city'           => 'required|string|max:100',
            'payment_method' => 'required|in:cash,card,transfer',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $subtotal = collect($cart)->sum(
            fn($i) => $i['price'] * ($i['qty'] ?? $i['quantity'] ?? 1)
        );

        $order = app(OrderService::class)->createOrder([
            'customer_name'    => $validated['name'],
            'customer_email'   => $validated['email'],
            'customer_phone'   => $validated['phone'],
            'customer_address' => $validated['address'],
            'city'             => $validated['city'],
            'subtotal'         => $subtotal,
            'shipping'         => 0,
            'total'            => $subtotal,
            'payment_method'   => $validated['payment_method'],
            'payment_status'   => 'unpaid',
            'notes'            => $validated['notes'] ?? null,
        ], $this->cartToOrderItems($cart), Auth::user());

        // Clear cart from session and cookie
        session()->forget('cart');
        Cookie::queue(Cookie::forget('urca_cart'));

        // Save address & city to user profile for future auto-checkout
        $user = Auth::user();
        if ($user) {
            $user->update([
                'phone'   => $validated['phone'],
                'address' => $validated['address'],
                'city'    => $validated['city'],
            ]);
        }

        return redirect()->route('checkout.success', $order->order_number);
    }

    public function success($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('items')
            ->firstOrFail();

        return view('checkout.success', compact('order'));
    }

    private function cartToOrderItems(array $cart): array
    {
        $items = [];
        foreach ($cart as $item) {
            $product = Product::with('vendor')->find($item['id'] ?? null);
            if (! $product || ! $product->isPublished()) {
                continue;
            }
            $qty = (int) ($item['qty'] ?? $item['quantity'] ?? 1);
            $items[] = [
                'product' => $product,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => $qty,
            ];
        }

        if (empty($items)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cart' => 'لا توجد منتجات متاحة في السلة.',
            ]);
        }

        return $items;
    }
}
