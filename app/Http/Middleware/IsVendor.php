<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsVendor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول أولاً.');
        }

        $user = Auth::user();

        if (! $user->isVendor() || ! $user->vendor) {
            abort(403, 'غير مصرح لك بالوصول إلى لوحة البائع.');
        }

        if (in_array($user->vendor->status, ['pending', 'rejected'], true)
            && ! $request->routeIs('vendor.pending', 'vendor.store.edit', 'vendor.store.update', 'vendor.notifications.*')) {
            return redirect()->route('vendor.pending');
        }

        if ($user->vendor->status === 'suspended') {
            abort(403, 'تم إيقاف متجرك. تواصل مع الإدارة.');
        }

        return $next($request);
    }
}
