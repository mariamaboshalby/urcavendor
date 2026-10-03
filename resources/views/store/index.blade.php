@extends('layouts.app')
@section('title', 'المتاجر')
@section('content')
<div class="container py-5">
    <h1 class="fw-bold mb-4">المتاجر</h1>
    <div class="row g-4">
        @forelse($vendors as $vendor)
        <div class="col-md-4">
            <a href="{{ route('stores.show', $vendor->store_slug) }}" class="card h-100 text-decoration-none text-dark border-0 shadow-sm">
                <div class="card-body">
                    <img src="{{ $vendor->logo_url }}" alt="" style="height:48px">
                    <h5 class="mt-3">{{ $vendor->store_name }}</h5>
                    <p class="text-muted small">{{ Str::limit($vendor->description, 90) }}</p>
                    <div>{{ $vendor->rating }} ★ · {{ $vendor->products_count }} منتج</div>
                </div>
            </a>
        </div>
        @empty
        <p>لا توجد متاجر بعد.</p>
        @endforelse
    </div>
    {{ $vendors->links() }}
</div>
@endsection
