@extends('admin.layouts.app')
@section('title', 'البائعون')
@section('page-title', 'إدارة البائعين')
@section('content')
<div class="toolbar">
    <form class="toolbar-search" method="GET">
        <div class="search-box"><i class="fas fa-search"></i><input name="search" value="{{ request('search') }}" placeholder="بحث"></div>
        <select name="status" class="form-control" style="width:160px">
            <option value="">الكل</option>
            @foreach(['pending','approved','rejected','suspended'] as $st)
            <option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary btn-sm">بحث</button>
    </form>
</div>
<div class="stats-grid" style="margin-bottom:1rem">
    @foreach($counts as $k=>$v)
    <div class="stat-card"><div class="stat-content"><h3>{{ $v }}</h3><p>{{ $k }}</p></div></div>
    @endforeach
</div>
<div class="card">
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>المتجر</th><th>البائع</th><th>الحالة</th><th>العمولة</th><th></th></tr></thead>
            <tbody>
            @foreach($vendors as $vendor)
                <tr>
                    <td><a href="{{ route('admin.vendors.show', $vendor) }}">{{ $vendor->store_name }}</a></td>
                    <td>{{ $vendor->user->email ?? '-' }}</td>
                    <td>{{ $vendor->status_label }}</td>
                    <td>{{ $vendor->commission_rate }}%</td>
                    <td>
                        @if($vendor->status==='pending')
                        <form method="POST" action="{{ route('admin.vendors.approve', $vendor) }}" style="display:inline">@csrf<button class="btn btn-primary btn-sm">قبول</button></form>
                        <form method="POST" action="{{ route('admin.vendors.reject', $vendor) }}" style="display:inline">@csrf<button class="btn btn-secondary btn-sm">رفض</button></form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $vendors->links() }}
</div>
@endsection
