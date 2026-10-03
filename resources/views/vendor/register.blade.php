@extends('layouts.app')
@section('title', 'سجل كبائع')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h3 class="fw-bold mb-3">افتح متجرك على urca</h3>
                @if ($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('vendor.register.store') }}" enctype="multipart/form-data">
                    @csrf
                    @guest
                    <div class="mb-3"><label class="form-label">الاسم</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
                    <div class="mb-3"><label class="form-label">البريد</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
                    <div class="mb-3"><label class="form-label">كلمة المرور</label><input type="password" name="password" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">تأكيد كلمة المرور</label><input type="password" name="password_confirmation" class="form-control" required></div>
                    @endguest
                    <div class="mb-3"><label class="form-label">اسم المتجر</label><input name="store_name" class="form-control" value="{{ old('store_name') }}" required></div>
                    <div class="mb-3"><label class="form-label">الهاتف</label><input name="phone" class="form-control" value="{{ old('phone') }}" required></div>
                    <div class="mb-3"><label class="form-label">وصف المتجر</label><textarea name="description" class="form-control">{{ old('description') }}</textarea></div>
                    <div class="mb-3"><label class="form-label">الشعار</label><input type="file" name="logo" class="form-control"></div>
                    <button class="btn btn-primary w-100">إرسال الطلب</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
