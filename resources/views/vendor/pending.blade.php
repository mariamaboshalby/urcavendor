@extends('vendor.layouts.app')
@section('title', 'بانتظار الموافقة')
@section('page-title', 'حالة المتجر')
@section('content')
<div class="card" style="max-width:640px;margin:2rem auto">
    <div class="card-body" style="padding:2rem;text-align:center">
        <i class="fas fa-store" style="font-size:2.5rem;color:var(--primary)"></i>
        <h2 style="margin:1rem 0 .5rem">{{ $vendor->store_name }}</h2>
        <p>حالة الطلب: <strong>{{ $vendor->status_label }}</strong></p>
        @if($vendor->status === 'pending')
            <p class="text-secondary">طلبك قيد المراجعة من الإدارة. لن تتمكن من إضافة منتجات أو استقبال طلبات حتى تتم الموافقة.</p>
        @elseif($vendor->status === 'rejected')
            <p>تم رفض الطلب{{ $vendor->rejection_reason ? ': '.$vendor->rejection_reason : '.' }}</p>
        @endif
    </div>
</div>
@endsection
