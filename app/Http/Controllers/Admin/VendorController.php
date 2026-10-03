<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::with('user')->withCount('products');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('store_name', 'like', '%' . $request->search . '%')
                    ->orWhere('store_slug', 'like', '%' . $request->search . '%');
            });
        }

        $vendors = $query->latest()->paginate(15);
        $counts = [
            'all' => Vendor::count(),
            'pending' => Vendor::where('status', 'pending')->count(),
            'approved' => Vendor::where('status', 'approved')->count(),
            'rejected' => Vendor::where('status', 'rejected')->count(),
            'suspended' => Vendor::where('status', 'suspended')->count(),
        ];

        return view('admin.vendors.index', compact('vendors', 'counts'));
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['user', 'products', 'wallet', 'withdrawals']);
        return view('admin.vendors.show', compact('vendor'));
    }

    public function approve(Vendor $vendor, VendorService $vendors)
    {
        $vendors->approve($vendor);
        return back()->with('success', 'تم قبول البائع.');
    }

    public function reject(Request $request, Vendor $vendor, VendorService $vendors)
    {
        $vendors->reject($vendor, $request->input('reason'));
        return back()->with('success', 'تم رفض البائع.');
    }

    public function suspend(Request $request, Vendor $vendor, VendorService $vendors)
    {
        $vendors->suspend($vendor, $request->input('reason'));
        return back()->with('success', 'تم إيقاف البائع.');
    }

    public function updateCommission(Request $request, Vendor $vendor)
    {
        $request->validate(['commission_rate' => 'required|numeric|min:0|max:100']);
        $vendor->update(['commission_rate' => $request->commission_rate]);

        return back()->with('success', 'تم تحديث نسبة العمولة. لن تؤثر على الطلبات السابقة.');
    }
}
