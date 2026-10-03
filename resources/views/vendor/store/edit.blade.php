@extends('vendor.layouts.app')
@section('title', 'المتجر')
@section('page-title', 'بيانات المتجر')
@section('content')
<form method="POST" action="{{ route('vendor.store.update') }}" enctype="multipart/form-data" class="card">
    @csrf @method('PUT')
    <div class="card-body">
        <div class="form-group"><label class="form-label">اسم المتجر</label><input name="store_name" class="form-control" value="{{ $vendor->store_name }}" required></div>
        <div class="form-group"><label class="form-label">الهاتف</label><input name="phone" class="form-control" value="{{ $vendor->phone }}"></div>
        <div class="form-group"><label class="form-label">الوصف</label><textarea name="description" class="form-control">{{ $vendor->description }}</textarea></div>
        <div class="form-group"><label class="form-label">الشعار</label><input type="file" name="logo" class="form-control" accept="image/*"></div>
        <p>رابط المتجر: <a href="{{ route('stores.show', $vendor->store_slug) }}" target="_blank">/store/{{ $vendor->store_slug }}</a></p>
        <button class="btn btn-primary">حفظ</button>
    </div>
</form>
@endsection
