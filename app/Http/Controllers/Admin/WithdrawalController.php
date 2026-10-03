<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WalletService;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = Withdrawal::with('vendor.user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->latest()->paginate(20);

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function approve(Withdrawal $withdrawal, WalletService $wallets)
    {
        $wallets->approveWithdrawal($withdrawal);
        return back()->with('success', 'تمت الموافقة على طلب السحب.');
    }

    public function reject(Request $request, Withdrawal $withdrawal, WalletService $wallets)
    {
        $wallets->rejectWithdrawal($withdrawal, $request->input('admin_notes'));
        return back()->with('success', 'تم رفض طلب السحب.');
    }

    public function paid(Withdrawal $withdrawal, WalletService $wallets)
    {
        $wallets->markWithdrawalPaid($withdrawal);
        return back()->with('success', 'تم تعليم السحب كمدفوع.');
    }
}
