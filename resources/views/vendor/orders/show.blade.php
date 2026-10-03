@extends('vendor.layouts.app')
@section('title', 'تفاصيل الطلب')
@section('page-title', $order->order_number)
@section('content')
<div class="card">
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>المنتج</th><th>الكمية</th><th>السعر</th><th>العمولة</th><th>ربحك</th><th>الحالة</th></tr></thead>
            <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->total) }}</td>
                    <td>{{ number_format($item->commission_amount) }} ({{ $item->commission_rate }}%)</td>
                    <td>{{ number_format($item->vendor_earning) }}</td>
                    <td>
                        <form method="POST" action="{{ route('vendor.orders.items.status', $item) }}">
                            @csrf @method('PATCH')
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                @foreach(['pending','processing','shipped','delivered','cancelled'] as $st)
                                <option value="{{ $st }}" @selected($item->status===$st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
