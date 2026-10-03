@extends('vendor.layouts.app')
@section('title', 'الطلبات')
@section('page-title', 'طلباتي')
@section('content')
<div class="card">
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>رقم الطلب</th><th>العناصر</th><th>الإجمالي لعنصرك</th><th>التاريخ</th></tr></thead>
            <tbody>
            @forelse($orders as $order)
                <tr>
                    <td><a href="{{ route('vendor.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                    <td>{{ $order->items->count() }}</td>
                    <td>{{ number_format($order->items->sum('total')) }} ج</td>
                    <td>{{ $order->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">لا توجد طلبات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</div>
@endsection
