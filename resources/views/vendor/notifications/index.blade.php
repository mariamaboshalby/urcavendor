@extends('vendor.layouts.app')
@section('title', 'الإشعارات')
@section('page-title', 'الإشعارات')
@section('content')
<form method="POST" action="{{ route('vendor.notifications.read-all') }}" style="margin-bottom:1rem">@csrf
    <button class="btn btn-secondary btn-sm">تعليم الكل كمقروء</button>
</form>
<div class="card">
    <div class="card-body">
        @forelse($notifications as $n)
            <a href="{{ route('vendor.notifications.read', $n->id) }}" style="display:block;padding:.75rem 0;border-bottom:1px solid var(--ink-100);{{ $n->read_at ? 'opacity:.6' : '' }}">
                <strong>{{ $n->data['title'] ?? 'إشعار' }}</strong>
                <div>{{ $n->data['body'] ?? '' }}</div>
                <small>{{ $n->created_at->diffForHumans() }}</small>
            </a>
        @empty
            <p>لا توجد إشعارات</p>
        @endforelse
        {{ $notifications->links() }}
    </div>
</div>
@endsection
