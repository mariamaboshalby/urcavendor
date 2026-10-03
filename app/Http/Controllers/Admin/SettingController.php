<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $products = \App\Models\Product::active()->get();
        $sliderImages = array_key_exists('home_slider_images', $settings)
            ? json_decode($settings['home_slider_images'], true)
            : Setting::HOME_SLIDER_DEFAULTS;
        $sliderImages = is_array($sliderImages) ? $sliderImages : Setting::HOME_SLIDER_DEFAULTS;

        return view('admin.settings.index', compact('settings', 'products', 'sliderImages'));
    }

    public function updateSliderImages(Request $request)
    {
        $request->validate([
            'slider_images' => 'nullable|array|max:10',
            'slider_images.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
            'remove_slider_images' => 'nullable|array',
            'remove_slider_images.*' => 'required|integer|min:0',
        ]);

        $storedImages = Setting::get('home_slider_images');
        $sliderImages = $storedImages === null
            ? Setting::HOME_SLIDER_DEFAULTS
            : json_decode($storedImages, true);
        $sliderImages = is_array($sliderImages) ? $sliderImages : Setting::HOME_SLIDER_DEFAULTS;
        $removeIndexes = array_map('intval', $request->input('remove_slider_images', []));
        $remainingImages = [];

        foreach ($sliderImages as $index => $image) {
            if (in_array((int) $index, $removeIndexes, true)) {
                foreach (['desktop', 'mobile'] as $size) {
                    $path = $image[$size] ?? null;
                    if (is_string($path) && str_starts_with($path, 'sliders/')) {
                        Storage::disk('public')->delete($path);
                    }
                }
                continue;
            }

            $remainingImages[] = $image;
        }

        foreach ($request->file('slider_images', []) ?? [] as $file) {
            $remainingImages[] = [
                'desktop' => $file->store('sliders', 'public'),
                'mobile' => null,
            ];
        }

        Setting::set('home_slider_images', json_encode($remainingImages, JSON_UNESCAPED_SLASHES));
        \App\Services\CacheService::clearSettingsCaches();

        return redirect()->back()->with('success', 'تم تحديث صور السلايدر بنجاح.');
    }

    public function update(Request $request)
    {
        $keys = [
            'home_show_deal',
            'home_show_slider',
            'home_deal_product_id',
            'home_deal_end_time',
            'home_show_brands',
            'home_show_recommended',
            'home_show_newsletter',
            'home_show_faq',
            'home_show_track_order',
            'products_api_url',
            'products_api_token',
        ];

        foreach ($keys as $key) {
            $value = $request->has($key) ? $request->input($key) : ($request->has('submitted') ? '0' : null);
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        \App\Services\CacheService::clearSettingsCaches();
        \Illuminate\Support\Facades\Cache::flush(); // Flush all public caches safely on settings update

        // Automatically sync products if API URL is configured
        $syncMessage = '';
        $apiUrl = trim(Setting::get('products_api_url', ''));
        if (!empty($apiUrl)) {
            try {
                $syncService = app(\App\Services\ExternalProductSyncService::class);
                $syncResult = $syncService->sync($apiUrl, Setting::get('products_api_token'));
                if ($syncResult['success']) {
                    $syncMessage = ' تم تحديث ومزامنة منتجات الـ API بنجاح (' . $syncResult['count'] . ' منتج).';
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Setting update auto-sync failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'تم حفظ الإعدادات بنجاح.' . ($syncMessage ? ' ' . $syncMessage : ''));
    }

    public function syncApi(Request $request, \App\Services\ExternalProductSyncService $syncService)
    {
        // Save current API URL & token if provided in request
        if ($request->filled('products_api_url')) {
            Setting::set('products_api_url', $request->input('products_api_url'));
        }
        if ($request->has('products_api_token')) {
            Setting::set('products_api_token', $request->input('products_api_token'));
        }

        $result = $syncService->sync(
            $request->input('products_api_url'),
            $request->input('products_api_token')
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }
}

