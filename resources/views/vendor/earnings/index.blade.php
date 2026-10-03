@extends('vendor.layouts.app')
@section('title', 'الأرباح')
@section('page-title', 'الأرباح')
@section('content')
<div class="stats-grid">
    <div class="stat-card"><div class="stat-content"><h3>{{ number_format($totals['available']) }}</h3><p>رصيد متاح</p></div></div>
    <div class="stat-card"><div class="stat-content"><h3>{{ number_format($totals['pending']) }}</h3><p>أرباح معلّقة</p></div></div>
    <div class="stat-card"><div class="stat-content"><h3>{{ number_format($totals['paid']) }}</h3><p>مدفوع</p></div></div>
    <div class="stat-card"><div class="stat-content"><h3>{{ number_format($totals['commission']) }}</h3><p>عمولة المنصة</p></div></div>
</div>
<div class="card">
    <div class="card-header"><h2>العمولات</h2></div>
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>الطلب</th><th>المبلغ</th><th>النسبة</th><th>العمولة</th><th>ربحك</th><th>الحالة</th></tr></thead>
            <tbody>
            @foreach($commissions as $c)
                <tr>
                    <td>{{ $c->order->order_number ?? $c->order_id }}</td>
                    <td>{{ number_format($c->order_amount) }}</td>
                    <td>{{ $c->commission_rate }}%</td>
                    <td>{{ number_format($c->commission_amount) }}</td>
                    <td>{{ number_format($c->vendor_amount) }}</td>
                    <td>{{ $c->status }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $commissions->links() }}
</div>
@endsection
