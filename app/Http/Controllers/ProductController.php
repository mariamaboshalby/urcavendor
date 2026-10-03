<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Map a Product model to a plain array for the view.
     * Uses the pre-loaded 'thumb' conversion URL (computed once per product).
     */
    private function toArray(Product $p, string $conversion = 'card'): array
    {
        // getMainImageUrl already handles fallback; re-use cached media relation
        $imageUrl = $p->getMainImageUrl($conversion);

        return [
            'id'          => $p->id,
            'name'        => $p->name,
            'brand'       => $p->brand,
            'category'    => $p->getRawOriginal('category'),
            'price'       => $p->price,
            'old_price'   => $p->old_price,
            'badge'       => $p->badge,
            'badge_color' => $p->badge_color,
            'image_url'   => $imageUrl,
            'image'       => $imageUrl ?: ($p->image ?? ''),
            'rating'      => $p->rating,
            'reviews'     => $p->reviews,
            'description' => $p->description,
            'specs'       => $p->specs ?? [],
            'vendor'      => $p->vendor ? [
                'store_name' => $p->vendor->store_name,
                'store_slug' => $p->vendor->store_slug,
            ] : null,
        ];
    }

    public function index(Request $request)
    {
        // Auto-refresh products from API if configured and stale (> 15 minutes)
        try {
            app(\App\Services\ExternalProductSyncService::class)->syncIfStale(15);
        } catch (\Throwable $e) {
            // Silently continue so product catalog always loads even if external API is slow
        }

        $category = $request->get('category', 'all');
        $sort     = $request->get('sort', 'default');
        $search   = $request->filled('search') ? trim($request->search) : null;
        $page     = (int) $request->get('page', 1);

        // Build a stable cache key so filtered/sorted pages are cached independently.
        // Do NOT cache search results (they're dynamic and rarely repeated).
        // Uses a version counter so CacheService::clearProductsListingCaches() invalidates all pages at once.
        $cacheKey = null;
        if (! $search) {
            $listingVersion = Cache::get('products_listing_version', 1);
            $cacheKey = "products_listing_v{$listingVersion}:{$category}:{$sort}:page:{$page}";
        }

        // Fetch products — use cache for non-search pages
        if ($cacheKey) {
            $products = Cache::remember($cacheKey, 300, function () use ($category, $sort, $search) {
                return $this->buildProductQuery($category, $sort, $search)->paginate(20);
            });
            // Restore paginator path (lost during serialization) and map to arrays
            $products->setPath(url()->current());
            $products->appends($request->query());
            $mappedProducts = $products->through(fn($p) => $this->toArray($p, 'thumb'));
        } else {
            $mappedProducts = $this->buildProductQuery($category, $sort, $search)
                ->paginate(20)
                ->through(fn($p) => $this->toArray($p, 'thumb'));
        }

        // Categories cached in AppServiceProvider; also cache for sidebar (flat roots only for filter sidebar)
        $dbCategories = \Illuminate\Support\Facades\Cache::remember('active_categories_tree', 3600, function () {
            return \App\Models\Category::with(['children' => function ($q) {
                    $q->active()->orderBy('sort_order')->orderBy('name_ar')
                      ->with(['children' => function ($q2) {
                          $q2->active()->orderBy('sort_order')->orderBy('name_ar');
                      }]);
                }])
                ->active()
                ->whereNull('parent_id')
                ->select(['id', 'slug', 'name_ar', 'name_en', 'icon', 'image', 'parent_id', 'sort_order'])
                ->orderBy('sort_order')
                ->orderBy('name_ar')
                ->get();
        });

        // Build icon map from already-fetched categories (no extra query) — flatten all levels
        $allCats = $dbCategories->flatMap(function ($root) {
            $flat = collect([$root]);
            foreach ($root->children as $child) {
                $flat->push($child);
                foreach ($child->children as $grandchild) {
                    $flat->push($grandchild);
                }
            }
            return $flat;
        });
        $categoryIconMap = $allCats->pluck('icon', 'slug')->toArray();

        return view('products.index', [
            'products'       => $mappedProducts,
            'category'       => $category,
            'sort'           => $sort,
            'dbCategories'   => $dbCategories,
            'categoryIconMap'=> $categoryIconMap,
        ]);
    }

    /**
     * Reusable query builder for the product listing.
     * Only selects columns needed for the card — no SELECT *.
     */
    private function buildProductQuery(string $category, string $sort, ?string $search)
    {
        $query = Product::published()
            ->select([
                'id', 'vendor_id', 'name', 'brand', 'category',
                'price', 'old_price', 'badge', 'badge_color',
                'rating', 'reviews', 'image', 'description',
                'is_active',
            ])
            ->with([
                'vendor:id,store_name,store_slug',
                'media' => fn($q) => $q->select([
                'id', 'model_id', 'model_type', 'uuid', 'collection_name',
                'name', 'file_name', 'mime_type', 'disk', 'conversions_disk',
                'size', 'generated_conversions', 'custom_properties',
                'order_column',
            ])->where('collection_name', 'product-images')->orderBy('order_column')]);

        if ($category !== 'all') {
            // Load the selected category with all descendants to filter by entire subtree
            $catModel = \Illuminate\Support\Facades\Cache::remember("cat_slugs:{$category}", 3600, function () use ($category) {
                return \App\Models\Category::with('allChildren')
                    ->where('slug', $category)
                    ->first();
            });

            if ($catModel) {
                $slugs = $catModel->descendantSlugs();
                $query->whereIn('category', $slugs);
            } else {
                $query->where('category', $category);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('brand', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        match ($sort) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'rating'     => $query->orderBy('rating', 'desc'),
            default      => $query->latest('id'),
        };

        return $query;
    }

    public function category(string $category)
    {
        return redirect()->route('products.index', ['category' => $category]);
    }

    public function show(int $id)
    {
        // Cache individual product data (5 min TTL — invalidated on update/delete)
        $productData = Cache::remember("product_show:{$id}", 300, function () use ($id) {
            $product = Product::published()
                ->select([
                    'id', 'vendor_id', 'name', 'brand', 'category', 'description',
                    'price', 'old_price', 'badge', 'badge_color',
                    'rating', 'reviews', 'image', 'specs',
                    'is_active',
                ])
                ->with([
                    'vendor:id,store_name,store_slug,logo,description,rating,reviews_count',
                    'media' => fn($q) => $q
                    ->select([
                        'id', 'model_id', 'model_type', 'uuid', 'collection_name',
                        'name', 'file_name', 'mime_type', 'disk', 'conversions_disk',
                        'size', 'generated_conversions', 'custom_properties',
                        'order_column',
                    ])
                    ->where('collection_name', 'product-images')
                    ->orderBy('order_column')
                ])
                ->findOrFail($id);

            $categorySlug = $product->getRawOriginal('category');

            $related = Product::published()
                ->select([
                    'id', 'vendor_id', 'name', 'brand', 'category',
                    'price', 'old_price', 'badge', 'badge_color',
                    'rating', 'reviews', 'image', 'description',
                    'is_active',
                ])
                ->with([
                    'vendor:id,store_name,store_slug',
                    'media' => fn($q) => $q
                    ->select([
                        'id', 'model_id', 'model_type', 'uuid', 'collection_name',
                        'name', 'file_name', 'mime_type', 'disk', 'conversions_disk',
                        'size', 'generated_conversions', 'custom_properties',
                        'order_column',
                    ])
                    ->where('collection_name', 'product-images')
                    ->orderBy('order_column')
                ])
                ->where('category', $categorySlug)
                ->where('id', '!=', $id)
                ->latest('id')
                ->take(4)
                ->get();

            return [
                'product' => $product,
                'related' => $related,
            ];
        });

        return view('products.show', [
            'product' => $this->toArray($productData['product'], 'card'),
            'related' => $productData['related']->map(fn($p) => $this->toArray($p, 'card'))->toArray(),
            'mediaItems' => $productData['product']->getMedia('product-images'),
        ]);
    }

    public function getProductCategories(Request $request)
    {
        $lazy = $request->boolean('lazy', true);

        $products = Product::published()
            ->when(! $lazy, fn ($query) => $query->with('category'))
            ->when(
                $lazy,
                fn ($query) => $query->paginate(15),
                fn ($query) => $query->get()
            );

        return response()->json([
            'status'      => true,
            'message'     => $lazy
                ? 'Products fetched successfully with lazy loading enabled.'
                : 'Products and categories fetched successfully with eager loading enabled.',
            'lazy_status' => $lazy,
            'data'        => $products,
        ]);
    }
}
