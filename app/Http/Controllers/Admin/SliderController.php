<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index()
    {
        $storedImages = Setting::get('home_slider_images');
        $sliderImages = $storedImages === null
            ? Setting::HOME_SLIDER_DEFAULTS
            : json_decode($storedImages, true);
        $sliderImages = is_array($sliderImages) ? $sliderImages : Setting::HOME_SLIDER_DEFAULTS;

        return view('admin.sliders.index', compact('sliderImages'));
    }

    public function update(Request $request)
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
}