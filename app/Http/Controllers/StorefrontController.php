<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Vendor;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function show(string $slug)
    {
        $vendor = Vendor::approved()->where('store_slug', $slug)->firstOrFail();

        $products = Product::published()
            ->where('vendor_id', $vendor->id)
            ->with('media')
            ->latest()
            ->paginate(16);

        $categories = Product::published()
            ->where('vendor_id', $vendor->id)
            ->select('category')
            ->distinct()
            ->pluck('category');

        $reviews = ProductReview::approved()
            ->where('vendor_id', $vendor->id)
            ->with('product')
            ->latest()
            ->take(8)
            ->get();

        $categoryModels = Category::whereIn('slug', $categories)->get()->keyBy('slug');

        return view('store.show', compact('vendor', 'products', 'categories', 'reviews', 'categoryModels'));
    }

    public function index()
    {
        $vendors = Vendor::approved()->withCount(['products' => fn ($q) => $q->published()])->latest()->paginate(12);

        return view('store.index', compact('vendors'));
    }

    public function storeReview(Request $request, Product $product)
    {
        if (! $product->isPublished()) {
            abort(404);
        }

        $validated = $request->validate([
            'author_name' => 'required|string|max:120',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        ProductReview::create([
            'product_id' => $product->id,
            'vendor_id' => $product->vendor_id,
            'user_id' => auth()->id(),
            'author_name' => $validated['author_name'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_approved' => true,
        ]);

        $avg = $product->productReviews()->approved()->avg('rating');
        $count = $product->productReviews()->approved()->count();
        $product->update([
            'rating' => (int) round($avg ?: 5),
            'reviews' => $count,
        ]);

        if ($product->vendor) {
            app(\App\Services\VendorService::class)->refreshVendorRating($product->vendor);
        }

        return back()->with('success', 'شكراً لتقييمك.');
    }
}
