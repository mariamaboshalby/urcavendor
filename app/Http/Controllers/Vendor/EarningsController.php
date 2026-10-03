<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\WalletTransaction;

class EarningsController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;
        $wallet = $vendor->wallet;

        $commissions = Commission::where('vendor_id', $vendor->id)->with('order')->latest()->paginate(20);
        $transactions = WalletTransaction::where('vendor_id', $vendor->id)->latest()->take(20)->get();

        $totals = [
            'pending' => Commission::where('vendor_id', $vendor->id)->where('status', 'pending')->sum('vendor_amount'),
            'available' => (float) optional($wallet)->balance,
            'paid' => Commission::where('vendor_id', $vendor->id)->where('status', 'paid')->sum('vendor_amount'),
            'commission' => Commission::where('vendor_id', $vendor->id)->where('status', '!=', 'cancelled')->sum('commission_amount'),
        ];

        return view('vendor.earnings.index', compact('vendor', 'wallet', 'commissions', 'transactions', 'totals'));
    }
}
