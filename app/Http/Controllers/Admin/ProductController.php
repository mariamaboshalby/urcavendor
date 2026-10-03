<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $products = $query->with('vendor')->latest()->paginate(15);
        $categories = \App\Models\Category::active()->get();
        $vendors = \App\Models\Vendor::orderBy('store_name')->get();

        return view('admin.products.index', compact('products', 'categories', 'vendors'));
    }

    public function create()
    {
        $categories = \App\Models\Category::active()->get();
        $vendors = \App\Models\Vendor::orderBy('store_name')->get();
        return view('admin.products.create', compact('categories', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'brand'          => 'required|string|max:255',
            'category'       => 'required|string|exists:categories,slug',
            'description'    => 'required|string',
            'price'          => 'required|numeric|min:0',
            'old_price'      => 'nullable|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'badge'          => 'nullable|string|max:50',
            'badge_color'    => 'nullable|string|max:50',
            'rating'         => 'required|integer|min:1|max:5',
            'specs'          => 'nullable|array',
            'images'         => 'nullable|array',
            'images.*'       => 'image|mimes:jpg,jpeg,png,webp|max:51200',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:51200',
            'vendor_id'      => 'nullable|exists:vendors,id',
            'approval_status'=> 'nullable|in:pending,approved,rejected,inactive',
        ]);

        $validated['is_active']   = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['vendor_id']   = $validated['vendor_id'] ?? app(\App\Services\VendorService::class)->defaultVendor()->id;
        $validated['approval_status'] = $validated['approval_status'] ?? 'approved';

        // إزالة images من البيانات قبل الحفظ
        unset($validated['images']);

        $product = Product::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image && $image->isValid()) {
                    $product->addMedia($image)
                            ->toMediaCollection('product-images');
                }
            }
        }

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')
                    ->toMediaCollection('product-images');
        }

        \App\Services\CacheService::clearProductCaches();

        return redirect()->route('admin.products.index')
            ->with('success', 'تم إضافة المنتج بنجاح');
    }

    public function edit(Product $product)
    {
        $categories = \App\Models\Category::active()->get();
        $vendors = \App\Models\Vendor::orderBy('store_name')->get();
        return view('admin.products.edit', compact('product', 'categories', 'vendors'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'brand'          => 'required|string|max:255',
            'category'       => 'required|string|exists:categories,slug',
            'description'    => 'required|string',
            'price'          => 'required|numeric|min:0',
            'old_price'      => 'nullable|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'badge'          => 'nullable|string|max:50',
            'badge_color'    => 'nullable|string|max:50',
            'rating'         => 'required|integer|min:1|max:5',
            'specs'          => 'nullable|array',
            'images'         => 'nullable|array',
            'images.*'       => 'image|mimes:jpg,jpeg,png,webp|max:51200',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:51200',
            'delete_media'   => 'nullable|array',
            'delete_media.*' => 'integer',
            'main_media_id'  => 'nullable|integer',
            'vendor_id'      => 'nullable|exists:vendors,id',
            'approval_status'=> 'nullable|in:pending,approved,rejected,inactive',
        ]);

        $validated['is_active']   = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        // إزالة images و delete_media من البيانات قبل الحفظ
        unset($validated['images'], $validated['delete_media']);

        $product->update($validated);

        if ($request->filled('delete_media')) {
            foreach ($request->input('delete_media') as $mediaId) {
                $product->deleteMedia($mediaId);
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $product->addMedia($image)
                        ->toMediaCollection('product-images');
            }
        }

        if ($request->filled('main_media_id')) {
            $mainId   = (int) $request->input('main_media_id');
            $mediaIds = $product->fresh()->getMedia('product-images')->pluck('id')->all();

            if (in_array($mainId, $mediaIds, true)) {
                $orderedIds = array_values(array_merge(
                    [$mainId],
                    array_values(filter: fn ($id) => $id !== $mainId)
                ));
                Media::setNewOrder($orderedIds);
            }
        }

        \App\Services\CacheService::clearProductCaches();
        \App\Services\CacheService::clearProductShowCache($product->id);

        return redirect()->route('admin.products.index')
            ->with('success', 'تم تحديث المنتج بنجاح');
    }

    public function destroy(Product $product)
    {
        $product->clearMediaCollection('product-images');
        $productId = $product->id;
        $product->delete();

        \App\Services\CacheService::clearProductCaches();
        \App\Services\CacheService::clearProductShowCache($productId);

        return redirect()->route('admin.products.index')
            ->with('success', 'تم حذف المنتج بنجاح');
    }

    public function approve(Product $product, \App\Services\ProductApprovalService $approval)
    {
        $approval->approve($product);
        return back()->with('success', 'تم قبول المنتج.');
    }

    public function reject(Request $request, Product $product, \App\Services\ProductApprovalService $approval)
    {
        $approval->reject($product, $request->input('reason'));
        return back()->with('success', 'تم رفض المنتج.');
    }

    public function disable(Product $product, \App\Services\ProductApprovalService $approval)
    {
        $approval->disable($product);
        return back()->with('success', 'تم تعطيل المنتج.');
    }
}
