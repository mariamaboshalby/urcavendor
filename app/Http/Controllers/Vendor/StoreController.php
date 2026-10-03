<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\VendorService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function edit()
    {
        $vendor = auth()->user()->vendor;
        return view('vendor.store.edit', compact('vendor'));
    }

    public function update(Request $request, VendorService $vendors)
    {
        $vendor = auth()->user()->vendor;

        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:2000',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $vendor->update([
            'store_name' => $validated['store_name'],
            'phone' => $validated['phone'] ?? $vendor->phone,
            'description' => $validated['description'] ?? $vendor->description,
        ]);

        if ($request->hasFile('logo')) {
            $vendors->storeLogo($vendor, $request->file('logo'));
        }

        return back()->with('success', 'تم تحديث بيانات المتجر.');
    }
}
