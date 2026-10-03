<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $vendor = auth()->user()->vendor;
        $query = Product::where('vendor_id', $vendor->id);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        $products = $query->latest()->paginate(15);

        return view('vendor.products.index', compact('products'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);
        $categories = Category::active()->get();

        return view('vendor.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Product::class);
        $vendor = auth()->user()->vendor;

        $validated = $this->validated($request);
        unset($validated['images'], $validated['vendor_id']);

        $validated['vendor_id'] = $vendor->id;
        $validated['approval_status'] = 'pending';
        $validated['is_active'] = false;
        $validated['is_featured'] = false;
        $validated['rating'] = $validated['rating'] ?? 5;

        $product = Product::create($validated);
        $this->attachImages($request, $product);
        CacheService::clearProductCaches();

        return redirect()->route('vendor.products.index')
            ->with('success', 'تم إرسال المنتج للمراجعة.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        $categories = Category::active()->get();

        return view('vendor.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $this->validated($request, true);
        unset($validated['images'], $validated['delete_media'], $validated['vendor_id'], $validated['main_media_id']);

        $validated['vendor_id'] = $product->vendor_id;
        if ($product->approval_status === 'rejected') {
            $validated['approval_status'] = 'pending';
        }

        $product->update($validated);

        if ($request->filled('delete_media')) {
            foreach ($request->input('delete_media') as $mediaId) {
                $product->deleteMedia($mediaId);
            }
        }

        $this->attachImages($request, $product);

        if ($request->filled('main_media_id')) {
            $mainId = (int) $request->input('main_media_id');
            $mediaIds = $product->fresh()->getMedia('product-images')->pluck('id')->all();
            if (in_array($mainId, $mediaIds, true)) {
                Media::setNewOrder(array_values(array_merge([$mainId], array_filter($mediaIds, fn ($id) => $id !== $mainId))));
            }
        }

        CacheService::clearProductCaches();
        CacheService::clearProductShowCache($product->id);

        return redirect()->route('vendor.products.index')->with('success', 'تم تحديث المنتج.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $id = $product->id;
        $product->clearMediaCollection('product-images');
        $product->delete();
        CacheService::clearProductCaches();
        CacheService::clearProductShowCache($id);

        return redirect()->route('vendor.products.index')->with('success', 'تم حذف المنتج.');
    }

    protected function validated(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'category' => 'required|string|exists:categories,slug',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'badge' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|max:50',
            'rating' => 'nullable|integer|min:1|max:5',
            'specs' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:51200',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:51200',
            'delete_media' => 'nullable|array',
            'delete_media.*' => 'integer',
            'main_media_id' => 'nullable|integer',
        ]);
    }

    protected function attachImages(Request $request, Product $product): void
    {
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image && $image->isValid()) {
                    $product->addMedia($image)->toMediaCollection('product-images');
                }
            }
        }
        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('product-images');
        }
    }
}
