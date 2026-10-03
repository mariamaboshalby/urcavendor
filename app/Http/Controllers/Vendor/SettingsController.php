<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('vendor.settings.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|confirmed|min:6',
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'] ?? $user->phone;
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return back()->with('success', 'تم حفظ الإعدادات.');
    }
}
