<?php

namespace App\Http\Controllers;

use App\Services\VendorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class VendorRegisterController extends Controller
{
    public function create()
    {
        if (Auth::check() && Auth::user()->vendor) {
            return redirect()->route('vendor.dashboard');
        }

        return view('vendor.register');
    }

    public function store(Request $request, VendorService $vendors)
    {
        if (Auth::check() && Auth::user()->vendor) {
            return redirect()->route('vendor.dashboard');
        }

        $rules = [
            'store_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'description' => 'nullable|string|max:2000',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ];

        if (! Auth::check()) {
            $rules = array_merge($rules, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => ['required', 'confirmed', Password::min(6)],
            ]);
        }

        $validated = $request->validate($rules);

        if (Auth::check()) {
            $validated['user_id'] = Auth::id();
        }

        $vendor = $vendors->register($validated, $request->file('logo'));

        if (! Auth::check()) {
            Auth::login($vendor->user);
            $request->session()->regenerate();
        }

        return redirect()->route('vendor.pending')
            ->with('success', 'تم إرسال طلب التسجيل. سيتم مراجعته من الإدارة.');
    }
}
