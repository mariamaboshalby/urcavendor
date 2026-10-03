@extends('vendor.layouts.app')
@section('title', 'السحب')
@section('page-title', 'طلبات السحب')
@section('content')
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-body">
        <p>الرصيد المتاح: <strong>{{ number_format(optional($wallet)->balance ?? 0) }} ج</strong></p>
        <form method="POST" action="{{ route('vendor.withdrawals.store') }}" class="form-row">
            @csrf
            <div class="form-group"><label class="form-label">المبلغ</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
            <div class="form-group"><label class="form-label">ملاحظات</label><input name="notes" class="form-control"></div>
            <div class="form-group"><label class="form-label">&nbsp;</label><button class="btn btn-primary">طلب سحب</button></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>المبلغ</th><th>الحالة</th><th>التاريخ</th></tr></thead>
            <tbody>
            @forelse($withdrawals as $w)
                <tr><td>{{ number_format($w->amount) }}</td><td>{{ $w->status_label }}</td><td>{{ $w->created_at->format('Y-m-d') }}</td></tr>
            @empty
                <tr><td colspan="3">لا توجد طلبات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $withdrawals->links() }}
</div>
@endsection
