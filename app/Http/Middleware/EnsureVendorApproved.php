<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $vendor = $request->user()?->vendor;

        if (! $vendor || ! $vendor->canSell()) {
            return redirect()->route('vendor.pending')
                ->with('error', 'يجب الموافقة على متجرك قبل تنفيذ هذه العملية.');
        }

        return $next($request);
    }
}
