@extends('vendor.layouts.app')
@section('title', 'التقييمات')
@section('page-title', 'تقييمات منتجاتي')
@section('content')
<div class="card">
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>المنتج</th><th>التقييم</th><th>التعليق</th><th>بواسطة</th></tr></thead>
            <tbody>
            @forelse($reviews as $review)
                <tr>
                    <td>{{ $review->product->name ?? '-' }}</td>
                    <td>{{ $review->rating }}/5</td>
                    <td>{{ $review->comment }}</td>
                    <td>{{ $review->author_name }}</td>
                </tr>
            @empty
                <tr><td colspan="4">لا توجد تقييمات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $reviews->links() }}
</div>
@endsection
