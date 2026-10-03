<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class WishlistController extends Controller
{
    /**
     * Toggle a product in the authenticated user's wishlist.
     * POST /wishlist/toggle/{product}
     */
    public function toggle(Request $request, int $productId)
    {
        // Rate limit: 60 toggles per minute per user
        $key = 'wishlist:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json(['error' => 'Too many requests.'], 429);
        }
        RateLimiter::hit($key, 60);

        // Validate product exists and is active
        $product = Product::published()->select('id', 'name', 'price', 'image')->find($productId);
        if (! $product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        $user     = Auth::user();
        $existing = Wishlist::where('user_id', $user->id)
                            ->where('product_id', $productId)
                            ->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            Wishlist::create([
                'user_id'    => $user->id,
                'product_id' => $productId,
            ]);
            $action = 'added';
        }

        return response()->json([
            'action' => $action,
            'count'  => Wishlist::where('user_id', $user->id)->count(),
        ]);
    }

    /**
     * Get all wishlist items for the authenticated user.
     * GET /wishlist
     */
    public function index()
    {
        $items = Wishlist::where('user_id', Auth::id())
            ->with(['product' => fn($q) => $q
                ->select('id', 'name', 'price', 'image')
                ->with(['media' => fn($m) => $m
                    ->select('id', 'model_id', 'model_type', 'uuid', 'collection_name',
                             'name', 'file_name', 'mime_type', 'disk', 'conversions_disk',
                             'size', 'generated_conversions', 'custom_properties', 'order_column')
                    ->where('collection_name', 'product-images')
                    ->orderBy('order_column')
                    ->limit(1)
                ])
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                $p = $item->product;
                if (! $p) return null;
                return [
                    'id'    => $p->id,
                    'name'  => $p->name,
                    'price' => (float) $p->price,
                    'img'   => $p->getMainImageUrl('thumb'),
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'items' => $items,
            'ids'   => $items->pluck('id')->toArray(),
        ]);
    }

    /**
     * Sync guest localStorage wishlist into DB after login.
     * POST /wishlist/sync
     */
    public function sync(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|max:100',
            'ids.*' => 'integer|min:1',
        ]);

        $user       = Auth::user();
        $productIds = Product::published()
                        ->whereIn('id', $request->ids)
                        ->pluck('id')
                        ->toArray();

        foreach ($productIds as $pid) {
            Wishlist::firstOrCreate([
                'user_id'    => $user->id,
                'product_id' => $pid,
            ]);
        }

        return response()->json([
            'synced' => count($productIds),
            'count'  => Wishlist::where('user_id', $user->id)->count(),
        ]);
    }

    /**
     * Remove a single item from the wishlist.
     * DELETE /wishlist/{product}
     */
    public function destroy(int $productId)
    {
        Wishlist::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->delete();

        return response()->json([
            'action' => 'removed',
            'count'  => Wishlist::where('user_id', Auth::id())->count(),
        ]);
    }
}
