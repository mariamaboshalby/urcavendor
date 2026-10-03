@extends('admin.layouts.app')
@section('title', 'السحوبات')
@section('page-title', 'طلبات السحب')
@section('content')
<div class="card">
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>البائع</th><th>المبلغ</th><th>الحالة</th><th></th></tr></thead>
            <tbody>
            @forelse($withdrawals as $w)
                <tr>
                    <td>{{ $w->vendor->store_name ?? '-' }}</td>
                    <td>{{ number_format($w->amount) }}</td>
                    <td>{{ $w->status_label }}</td>
                    <td>
                        @if($w->status==='pending')
                        <form method="POST" action="{{ route('admin.withdrawals.approve', $w) }}" style="display:inline">@csrf<button class="btn btn-primary btn-sm">موافقة</button></form>
                        <form method="POST" action="{{ route('admin.withdrawals.reject', $w) }}" style="display:inline">@csrf<button class="btn btn-secondary btn-sm">رفض</button></form>
                        @endif
                        @if(in_array($w->status, ['pending','approved']))
                        <form method="POST" action="{{ route('admin.withdrawals.paid', $w) }}" style="display:inline">@csrf<button class="btn btn-primary btn-sm">تم الدفع</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">لا توجد طلبات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $withdrawals->links() }}
</div>
@endsection
