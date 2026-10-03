<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $vendor = auth()->user()->vendor;
        $withdrawals = $vendor->withdrawals()->latest()->paginate(15);
        $wallet = $vendor->wallet;

        return view('vendor.withdrawals.index', compact('withdrawals', 'wallet'));
    }

    public function store(Request $request, WalletService $wallets)
    {
        $this->authorize('create', Withdrawal::class);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $wallets->requestWithdrawal(auth()->user()->vendor, (float) $validated['amount'], $validated['notes'] ?? null);

        return back()->with('success', 'تم إرسال طلب السحب.');
    }
}
