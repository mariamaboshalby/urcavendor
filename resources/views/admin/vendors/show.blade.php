@extends('admin.layouts.app')
@section('title', $vendor->store_name)
@section('page-title', $vendor->store_name)
@section('content')
<div class="card" style="margin-bottom:1rem">
    <div class="card-body">
        <p>الحالة: {{ $vendor->status_label }}</p>
        <p>الهاتف: {{ $vendor->phone }}</p>
        <p>الرصيد: {{ number_format(optional($vendor->wallet)->balance ?? 0) }} ج</p>
        <form method="POST" action="{{ route('admin.vendors.commission', $vendor) }}" class="form-row">
            @csrf @method('PATCH')
            <div class="form-group"><label class="form-label">نسبة العمولة</label><input type="number" step="0.01" name="commission_rate" value="{{ $vendor->commission_rate }}" class="form-control"></div>
            <div class="form-group"><label class="form-label">&nbsp;</label><button class="btn btn-primary">حفظ العمولة</button></div>
        </form>
        <div class="d-flex gap-2 mt-3">
            <form method="POST" action="{{ route('admin.vendors.approve', $vendor) }}">@csrf<button class="btn btn-primary">قبول</button></form>
            <form method="POST" action="{{ route('admin.vendors.reject', $vendor) }}">@csrf<input name="reason" placeholder="سبب الرفض"><button class="btn btn-secondary">رفض</button></form>
            <form method="POST" action="{{ route('admin.vendors.suspend', $vendor) }}">@csrf<button class="btn btn-secondary">إيقاف</button></form>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header"><h2>المنتجات</h2></div>
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>الاسم</th><th>الحالة</th><th></th></tr></thead>
            <tbody>
            @foreach($vendor->products as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->approval_status }}</td>
                    <td>
                        @if($p->approval_status==='pending')
                        <form method="POST" action="{{ route('admin.products.approve', $p) }}" style="display:inline">@csrf<button class="btn btn-primary btn-sm">قبول</button></form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
