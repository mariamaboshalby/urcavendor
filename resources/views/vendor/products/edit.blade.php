@extends('vendor.layouts.app')
@section('title', 'تعديل منتج')
@section('page-title', 'تعديل منتج')
@section('content')
<form method="POST" action="{{ route('vendor.products.update', $product) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="card">
        <div class="card-body">
            <div class="form-group"><label class="form-label required">الاسم</label><input name="name" class="form-control" value="{{ old('name', $product->name) }}" required></div>
            <div class="form-group"><label class="form-label required">العلامة</label><input name="brand" class="form-control" value="{{ old('brand', $product->brand) }}" required></div>
            <div class="form-group"><label class="form-label required">القسم</label>
                <select name="category" class="form-control" required>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected($product->getRawOriginal('category')===$cat->slug)>{{ $cat->name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label required">الوصف</label><textarea name="description" class="form-control" required>{{ old('description', $product->description) }}</textarea></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label required">السعر</label><input type="number" step="0.01" name="price" class="form-control" value="{{ $product->price }}" required></div>
                <div class="form-group"><label class="form-label">سعر قديم</label><input type="number" step="0.01" name="old_price" class="form-control" value="{{ $product->old_price }}"></div>
                <div class="form-group"><label class="form-label required">المخزون</label><input type="number" name="stock" class="form-control" value="{{ $product->stock }}" required></div>
            </div>
            <p>الحالة الحالية: {{ $product->approval_status }}</p>
            @if($product->rejection_reason)<p>سبب الرفض: {{ $product->rejection_reason }}</p>@endif
            <div class="form-group"><label class="form-label">صور إضافية</label><input type="file" name="images[]" multiple accept="image/*" class="form-control"></div>
            <button class="btn btn-primary">حفظ</button>
        </div>
    </div>
</form>
@endsection
