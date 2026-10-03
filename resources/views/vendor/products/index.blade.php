@extends('vendor.layouts.app')
@section('title', 'المنتجات')
@section('page-title', 'منتجاتي')
@section('content')
<div class="toolbar">
    <form class="toolbar-search" method="GET">
        <div class="search-box"><i class="fas fa-search"></i><input type="text" name="search" value="{{ request('search') }}" placeholder="بحث"></div>
        <select name="approval_status" class="form-control" style="width:160px">
            <option value="">كل الحالات</option>
            @foreach(['pending'=>'قيد المراجعة','approved'=>'مقبول','rejected'=>'مرفوض','inactive'=>'غير نشط'] as $k=>$v)
            <option value="{{ $k }}" @selected(request('approval_status')===$k)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary btn-sm">بحث</button>
    </form>
    <a href="{{ route('vendor.products.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> إضافة منتج</a>
</div>
<div class="card">
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>المنتج</th><th>السعر</th><th>المخزون</th><th>الحالة</th><th></th></tr></thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ number_format($product->price) }} ج</td>
                    <td>{{ $product->stock }}</td>
                    <td>{{ $product->approval_status }}</td>
                    <td>
                        <a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-secondary btn-sm">تعديل</a>
                        <form action="{{ route('vendor.products.destroy', $product) }}" method="POST" style="display:inline">@csrf @method('DELETE')
                            <button class="btn btn-secondary btn-sm" onclick="return confirm('حذف؟')">حذف</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">لا توجد منتجات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $products->links() }}
</div>
@endsection
