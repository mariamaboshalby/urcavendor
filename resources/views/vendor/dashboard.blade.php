@extends('vendor.layouts.app')
@section('title', 'لوحة البائع')
@section('page-title', 'لوحة التحكم')
@section('content')
<div class="stats-grid">
    @foreach([
        ['total_sales','إجمالي المبيعات','fa-chart-line','blue'],
        ['total_orders','إجمالي الطلبات','fa-shopping-bag','green'],
        ['pending_orders','طلبات قيد التنفيذ','fa-clock','orange'],
        ['completed_orders','طلبات مكتملة','fa-check','green'],
        ['total_earnings','إجمالي الأرباح','fa-coins','blue'],
        ['platform_commission','عمولة المنصة','fa-percent','orange'],
        ['available_balance','الرصيد المتاح','fa-wallet','green'],
        ['pending_withdrawals','سحوبات معلقة','fa-hourglass','orange'],
    ] as $card)
    <div class="stat-card">
        <div class="stat-icon {{ $card[3] }}"><i class="fas {{ $card[2] }}"></i></div>
        <div class="stat-content">
            <h3>{{ number_format($stats[$card[0]]) }}</h3>
            <p>{{ $card[1] }}</p>
        </div>
    </div>
    @endforeach
</div>
<div class="dashboard-grid">
    <div class="card">
        <div class="card-header"><h2>المبيعات (7 أيام)</h2></div>
        <div class="card-body"><canvas id="salesChart" height="120"></canvas></div>
    </div>
    <div class="card">
        <div class="card-header"><h2>الأكثر مبيعاً</h2></div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>المنتج</th><th>المبيعات</th></tr></thead>
                <tbody>
                @forelse($bestProducts as $p)
                    <tr><td>{{ $p->name }}</td><td>{{ $p->sales_count }}</td></tr>
                @empty
                    <tr><td colspan="2">لا توجد بيانات</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="card" style="margin-top:1.5rem">
    <div class="card-header"><h2>أحدث الطلبات</h2><a href="{{ route('vendor.orders.index') }}" class="btn btn-secondary btn-sm">عرض الكل</a></div>
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>الطلب</th><th>العناصر</th><th>الحالة</th></tr></thead>
            <tbody>
            @forelse($recentOrders as $orderId => $items)
                <tr>
                    <td><a href="{{ route('vendor.orders.show', $orderId) }}">{{ $items->first()->order->order_number ?? $orderId }}</a></td>
                    <td>{{ $items->pluck('product_name')->join('، ') }}</td>
                    <td>{{ $items->first()->status_label }}</td>
                </tr>
            @empty
                <tr><td colspan="3">لا توجد طلبات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
const ctx = document.getElementById('salesChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! $salesData->pluck('date')->toJson() !!},
        datasets: [{ label: 'المبيعات', data: {!! $salesData->pluck('total')->toJson() !!}, borderColor: '#051836', backgroundColor: 'rgba(5,24,54,.1)', fill: true, tension: .3 }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
