@extends('layouts.app')
@section('title', $vendor->store_name)
@section('content')
<div class="container py-5">
    <div class="d-flex gap-3 align-items-center mb-4">
        <img src="{{ $vendor->logo_url }}" alt="" style="width:72px;height:72px;object-fit:contain">
        <div>
            <h1 class="fw-bold mb-1">{{ $vendor->store_name }}</h1>
            <p class="mb-1">{{ $vendor->description }}</p>
            <div>{{ $vendor->rating }} ★ ({{ $vendor->reviews_count }} تقييم)</div>
            @if($vendor->phone)<div><i class="fas fa-phone"></i> {{ $vendor->phone }}</div>@endif
        </div>
    </div>
    @if($categories->count())
        <div class="mb-3">
            @foreach($categories as $slug)
                <span class="badge bg-primary">{{ optional($categoryModels->get($slug))->name_ar ?? $slug }}</span>
            @endforeach
        </div>
    @endif
    <div class="row g-3">
        @foreach($products as $p)
            <div class="col-6 col-md-3">
                <a href="{{ route('products.show', $p->id) }}" class="text-decoration-none text-dark">
                    <div class="card h-100 border-0 shadow-sm">
                        <img src="{{ $p->getMainImageUrl('thumb') }}" class="card-img-top" alt="">
                        <div class="card-body">
                            <div class="small">{{ $p->name }}</div>
                            <strong>{{ number_format($p->price) }} EGP</strong>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
    {{ $products->links() }}
    @if($reviews->count())
    <h3 class="mt-5">آراء العملاء</h3>
    @foreach($reviews as $review)
        <div class="border-bottom py-3">
            <strong>{{ $review->author_name }}</strong> — {{ $review->rating }}/5
            <div>{{ $review->comment }}</div>
        </div>
    @endforeach
    @endif
</div>
@endsection
