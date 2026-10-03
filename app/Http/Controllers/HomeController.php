<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\AboutStat;
use App\Models\BlogPost;
use App\Models\Download;
use App\Models\Testimonial;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Brand;
use App\Models\Faq;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // Auto-refresh products from API if configured and stale (> 15 minutes)
        try {
            app(\App\Services\ExternalProductSyncService::class)->syncIfStale(15);
        } catch (\Throwable $e) {
            // Silently continue so home page always loads even if external API is slow
        }

        $mapProduct = fn($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'category'    => $p->category,
            'price'       => $p->price,
            'old_price'   => $p->old_price,
            'badge'       => $p->badge,
            'badge_color' => $p->badge_color,
            'image_url'   => $p->getFirstMediaUrl('product-images', 'card') ?: ($p->image ?? ''),
            'image'       => $p->getFirstMediaUrl('product-images', 'card') ?: ($p->image ?? ''),
            'rating'      => $p->rating,
            'reviews'     => $p->reviews ?? 0,
            'description' => $p->description,
        ];

        $selectCols = ['id', 'name', 'category', 'price', 'old_price', 'badge', 'badge_color', 'rating', 'reviews', 'description', 'is_active'];

        // Featured Products (cached 30 mins)
        $featuredProducts = Cache::remember('home_featured_products', 1800, function () use ($mapProduct, $selectCols) {
            $items = Product::published()
                ->featured()
                ->select(array_merge($selectCols, ['is_featured']))
                ->with(['media' => fn($q) => $q->where('collection_name', 'product-images')])
                ->latest('id')
                ->take(6)
                ->get()
                ->map($mapProduct)
                ->toArray();

            if (empty($items)) {
                $items = Product::published()
                    ->select($selectCols)
                    ->with(['media' => fn($q) => $q->where('collection_name', 'product-images')])
                    ->latest('id')
                    ->take(6)
                    ->get()
                    ->map($mapProduct)
                    ->toArray();
            }
            return $items;
        });

        // Dynamic Stats (cached 1 hour)
        $stats = Cache::remember('home_stats_' . app()->getLocale(), 3600, function () {
            $aboutStats = AboutStat::active()->take(4)->get();
            if ($aboutStats->count() > 0) {
                return $aboutStats->map(function($stat) {
                    return [
                        'number' => $stat->number,
                        'label'  => app()->getLocale() === 'ar' ? $stat->title_ar : $stat->title_en,
                        'icon'   => $stat->icon,
                    ];
                })->toArray();
            }

            $productCount = Product::where('is_active', true)->count();
            return [
                ['number' => $productCount . '+', 'label' => app()->getLocale() === 'ar' ? 'منتج متاح' : 'Available Products',  'icon' => 'fa-box'],
                ['number' => '15K+',              'label' => app()->getLocale() === 'ar' ? 'عميل سعيد' : 'Happy Clients',        'icon' => 'fa-users'],
                ['number' => '10+',               'label' => app()->getLocale() === 'ar' ? 'سنوات خبرة' : 'Years Experience',    'icon' => 'fa-award'],
                ['number' => '24/7',              'label' => app()->getLocale() === 'ar' ? 'دعم فني' : 'Technical Support',     'icon' => 'fa-headset'],
            ];
        });

        // Blog Posts (cached 30 mins)
        $blogPosts = Cache::remember('home_blog_posts', 1800, function () {
            return BlogPost::active()
                ->select(['id', 'title', 'title_ar', 'excerpt', 'excerpt_ar', 'category', 'views', 'read_time', 'published_at', 'created_at', 'is_active'])
                ->latest('published_at')
                ->take(3)
                ->get();
        });

        // Downloads (cached 1 hour)
        $downloads = Cache::remember('home_downloads', 3600, function () {
            return Download::active()
                ->latest('id')
                ->take(4)
                ->get();
        });

        // Testimonials (cached 1 hour)
        $testimonials = Cache::remember('home_testimonials', 3600, function () {
            return Testimonial::approved()
                ->latest('id')
                ->take(6)
                ->get();
        });

        // Best Selling Products (cached 30 mins)
        $bestSellingProducts = Cache::remember('home_best_selling_products', 1800, function () use ($mapProduct, $selectCols) {
            return Product::published()
                ->select(array_merge($selectCols, ['sales_count']))
                ->with(['media' => fn($q) => $q->where('collection_name', 'product-images')])
                ->orderBy('sales_count', 'desc')
                ->take(6)
                ->get()
                ->map($mapProduct)
                ->toArray();
        });

        // Top 4 Categories (cached 1 hour)
        $topCategories = Cache::remember('home_top_categories', 3600, function () {
            return Category::active()
                ->select(['id', 'slug', 'name_ar', 'name_en', 'icon', 'image', 'is_active'])
                ->take(4)
                ->get();
        });

        // Most Visited Products (cached 30 mins)
        $mostVisitedProducts = Cache::remember('home_most_visited_products', 1800, function () use ($mapProduct, $selectCols) {
            return Product::published()
                ->select(array_merge($selectCols, ['views_count']))
                ->with(['media' => fn($q) => $q->where('collection_name', 'product-images')])
                ->orderBy('views_count', 'desc')
                ->take(6)
                ->get()
                ->map($mapProduct)
                ->toArray();
        });

        // Latest Products (cached 30 mins)
        $latestProducts = Cache::remember('home_latest_products', 1800, function () use ($mapProduct, $selectCols) {
            return Product::published()
                ->select($selectCols)
                ->with(['media' => fn($q) => $q->where('collection_name', 'product-images')])
                ->latest('id')
                ->take(5)
                ->get()
                ->map($mapProduct)
                ->toArray();
        });

        // Home Settings (cached 1 hour)
        $settings = Cache::remember('home_settings_map', 3600, function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        $sliderImages = array_key_exists('home_slider_images', $settings)
            ? json_decode($settings['home_slider_images'], true)
            : Setting::HOME_SLIDER_DEFAULTS;
        $sliderImages = is_array($sliderImages) ? $sliderImages : Setting::HOME_SLIDER_DEFAULTS;

        // Deal of the Day (cached 30 mins)
        $dealProduct = null;
        if (($settings['home_show_deal'] ?? '0') == '1' && !empty($settings['home_deal_product_id'])) {
            $dealProduct = Cache::remember('home_deal_product_' . $settings['home_deal_product_id'], 1800, function () use ($settings, $mapProduct) {
                $productModel = Product::with(['media' => fn($q) => $q->where('collection_name', 'product-images')])->find($settings['home_deal_product_id']);
                if ($productModel) {
                    $deal = $mapProduct($productModel);
                    $deal['end_time'] = $settings['home_deal_end_time'] ?? null;
                    return $deal;
                }
                return null;
            });
        }

        // Brands (cached 1 hour)
        $brands = [];
        if (($settings['home_show_brands'] ?? '0') == '1') {
            $brands = Cache::remember('home_brands_list', 3600, function () {
                return Brand::active()->with(['media' => fn($q) => $q->where('collection_name', 'brand-logos')])->get();
            });
        }

        // FAQs (cached 1 hour)
        $faqs = [];
        if (($settings['home_show_faq'] ?? '0') == '1') {
            $faqs = Cache::remember('home_faqs_list', 3600, function () {
                return Faq::active()->orderBy('sort_order')->get();
            });
        }

        // Recommended Products (cached 15 mins)
        $recommendedProducts = [];
        if (($settings['home_show_recommended'] ?? '0') == '1') {
            $recommendedProducts = Cache::remember('home_recommended_products', 900, function () use ($mapProduct, $selectCols) {
                return Product::published()
                    ->select($selectCols)
                    ->with(['media' => fn($q) => $q->where('collection_name', 'product-images')])
                    ->inRandomOrder()
                    ->take(6)
                    ->get()
                    ->map($mapProduct)
                    ->toArray();
            });
        }

        return view('home', compact(
            'featuredProducts', 'stats', 'blogPosts', 'downloads', 'testimonials',
            'bestSellingProducts', 'topCategories', 'mostVisitedProducts', 'latestProducts',
            'settings', 'sliderImages', 'dealProduct', 'brands', 'faqs', 'recommendedProducts'
        ));
    }
}
