@extends('vendor.layouts.app')
@section('title', 'الإعدادات')
@section('page-title', 'إعدادات الحساب')
@section('content')
<form method="POST" action="{{ route('vendor.settings.update') }}" class="card">
    @csrf @method('PUT')
    <div class="card-body">
        <div class="form-group"><label class="form-label">الاسم</label><input name="name" class="form-control" value="{{ $user->name }}" required></div>
        <div class="form-group"><label class="form-label">الهاتف</label><input name="phone" class="form-control" value="{{ $user->phone }}"></div>
        <div class="form-group"><label class="form-label">كلمة مرور جديدة</label><input type="password" name="password" class="form-control"></div>
        <div class="form-group"><label class="form-label">تأكيد كلمة المرور</label><input type="password" name="password_confirmation" class="form-control"></div>
        <button class="btn btn-primary">حفظ</button>
    </div>
</form>
@endsection
