@extends('admin.layouts.app')
@section('title', 'المالية')
@section('page-title', 'التقارير المالية')
@section('content')
<div class="stats-grid">
    <div class="stat-card"><div class="stat-content"><h3>{{ number_format($stats['total_sales']) }}</h3><p>إجمالي المبيعات</p></div></div>
    <div class="stat-card"><div class="stat-content"><h3>{{ number_format($stats['platform_commission']) }}</h3><p>عمولة المنصة</p></div></div>
    <div class="stat-card"><div class="stat-content"><h3>{{ number_format($stats['vendor_earnings']) }}</h3><p>أرباح البائعين</p></div></div>
    <div class="stat-card"><div class="stat-content"><h3>{{ number_format($stats['pending_withdrawals']) }}</h3><p>سحوبات معلقة</p></div></div>
    <div class="stat-card"><div class="stat-content"><h3>{{ number_format($stats['paid_withdrawals']) }}</h3><p>سحوبات مدفوعة</p></div></div>
</div>
<div class="dashboard-grid">
    <div class="card">
        <div class="card-header"><h2>أفضل البائعين</h2></div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>المتجر</th><th>المبيعات</th></tr></thead>
                <tbody>
                @foreach($bestVendors as $v)
                    <tr><td>{{ $v->store_name }}</td><td>{{ number_format($v->sales ?? 0) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h2>أفضل المنتجات</h2></div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>المنتج</th><th>المبيعات</th></tr></thead>
                <tbody>
                @foreach($bestProducts as $p)
                    <tr><td>{{ $p->name }}</td><td>{{ $p->sales_count }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="card" style="margin-top:1.5rem">
    <div class="card-header"><h2>تقرير العمولات</h2></div>
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>البائع</th><th>عمولة المنصة</th><th>أرباح البائع</th></tr></thead>
            <tbody>
            @foreach($salesPerVendor as $v)
                <tr>
                    <td>{{ $v->store_name }}</td>
                    <td>{{ number_format($v->commission_total ?? 0) }}</td>
                    <td>{{ number_format($v->vendor_total ?? 0) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
