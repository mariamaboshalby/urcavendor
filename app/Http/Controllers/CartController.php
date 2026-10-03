<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getCartData(array $cart): array
    {
        $total = 0;
        $items = [];
        foreach ($cart as $id => $item) {
            $qty = (int)($item['quantity'] ?? $item['qty'] ?? 1);
            $price = (float)($item['price'] ?? 0);
            $total += $price * $qty;
            $items[] = [
                'id'       => $item['id'] ?? $id,
                'name'     => $item['name'] ?? '',
                'price'    => $price,
                'quantity' => $qty,
                'image'    => $item['image'] ?? '',
            ];
        }
        return ['items' => $items, 'total' => $total, 'cart_count' => count($cart)];
    }

    public function items()
    {
        $cart  = session()->get('cart', []);
        $data  = $this->getCartData($cart);

        return response()->json($data);
    }

    public function index()
    {
        $cart  = session()->get('cart', []);
        $total = 0;

        // Normalise keys so the view always gets 'qty' and 'icon'
        $normalised = [];
        foreach ($cart as $id => $item) {
            $qty = $item['qty'] ?? $item['quantity'] ?? 1;
            $normalised[$id] = [
                'id'       => $item['id']    ?? $id,
                'name'     => $item['name']  ?? '',
                'price'    => $item['price'] ?? 0,
                'qty'      => $qty,
                'quantity' => $qty,
                'image'    => $item['image'] ?? '',
                'icon'     => $item['icon']  ?? 'fa-box',
            ];
            $total += $normalised[$id]['price'] * $qty;
        }

        return view('cart.index', ['cart' => $normalised, 'total' => $total]);
    }

    public function add(Request $request)
    {
        $product = \App\Models\Product::with('vendor')->find($request->input('product_id'));

        if (! $product || ! $product->isPublished()) {
            return response()->json(['success' => false, 'message' => 'المنتج غير متاح.'], 422);
        }

        $cart = session()->get('cart', []);

        $productId   = $product->id;
        $productName = $product->name;
        $productPrice= (float) $product->price;
        $productImage= $request->input('product_image') ?: $product->getMainImageUrl('thumb');
        $quantity    = (int)$request->input('quantity', 1);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
            $cart[$productId]['qty']      += $quantity;
        } else {
            $cart[$productId] = [
                'id'       => $productId,
                'name'     => $productName,
                'price'    => $productPrice,
                'image'    => $productImage,
                'quantity' => $quantity,
                'qty'      => $quantity,
                'icon'     => 'fa-box',
            ];
        }

        session()->put('cart', $cart);

        // Persist cart in cookie for 10 days (for guests)
        $cookieMinutes = 60 * 24 * 10;
        \Cookie::queue('urca_cart', json_encode($cart), $cookieMinutes);

        $data = $this->getCartData($cart);

        return response()->json([
            'success'    => true,
            'message'    => 'Product added to cart!',
            'cart_count' => $data['cart_count'],
            'items'      => $data['items'],
            'total'      => $data['total'],
        ]);
    }

    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        $productId = $request->input('product_id');

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        $data = $this->getCartData($cart);

        return response()->json([
            'success'    => true,
            'message'    => 'تم حذف المنتج من السلة',
            'cart_count' => $data['cart_count'],
            'items'      => $data['items'],
            'total'      => $data['total'],
        ]);
    }

    public function update(Request $request)
    {
        $cart = session()->get('cart', []);
        $productId = $request->input('product_id');
        $quantity = (int)$request->input('quantity', 1);

        if (isset($cart[$productId])) {
            if ($quantity <= 0) {
                unset($cart[$productId]);
            } else {
                $cart[$productId]['quantity'] = $quantity;
                $cart[$productId]['qty'] = $quantity;
            }
            session()->put('cart', $cart);
        }

        $data = $this->getCartData($cart);

        return response()->json([
            'success'    => true,
            'message'    => 'تم تحديث الكمية',
            'cart_count' => $data['cart_count'],
            'items'      => $data['items'],
            'total'      => $data['total'],
        ]);
    }

    public function clear()
    {
        session()->forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'تم تفريغ السلة',
        ]);
    }
}
