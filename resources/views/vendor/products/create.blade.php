@extends('vendor.layouts.app')
@section('title', 'إضافة منتج')
@section('page-title', 'إضافة منتج')
@section('content')
<form method="POST" action="{{ route('vendor.products.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="form-group"><label class="form-label required">الاسم</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
            <div class="form-group"><label class="form-label required">العلامة</label><input name="brand" class="form-control" value="{{ old('brand') }}" required></div>
            <div class="form-group"><label class="form-label required">القسم</label>
                <select name="category" class="form-control" required>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}">{{ $cat->name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label class="form-label required">الوصف</label><textarea name="description" class="form-control" required>{{ old('description') }}</textarea></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label required">السعر</label><input type="number" step="0.01" name="price" class="form-control" required></div>
                <div class="form-group"><label class="form-label">سعر قديم</label><input type="number" step="0.01" name="old_price" class="form-control"></div>
                <div class="form-group"><label class="form-label required">المخزون</label><input type="number" name="stock" class="form-control" value="0" required></div>
            </div>
            <div class="form-group"><label class="form-label">صور</label><input type="file" name="images[]" multiple accept="image/*" class="form-control"></div>
            <button class="btn btn-primary">إرسال للمراجعة</button>
        </div>
    </div>
</form>
@endsection
