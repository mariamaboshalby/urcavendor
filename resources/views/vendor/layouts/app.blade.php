<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة البائع') — urca</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom-colors.css') }}">
    @stack('styles')
</head>
<body class="admin-body">
@php $vendor = auth()->user()->vendor; $unread = auth()->user()->unreadNotifications()->count(); @endphp
<aside class="adm-sidebar" id="admSidebar">
    <div class="adm-sidebar-head">
        <a href="{{ $vendor->isApproved() ? route('vendor.dashboard') : route('vendor.pending') }}" class="adm-logo">
            <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->store_name }}" class="adm-logo-img">
        </a>
        <button class="adm-sidebar-close" id="admSidebarClose"><i class="fas fa-times"></i></button>
    </div>
    <nav class="adm-nav">
        @if($vendor->isApproved())
        <div class="adm-nav-group">
            <a href="{{ route('vendor.dashboard') }}" class="adm-nav-item {{ request()->routeIs('vendor.dashboard') ? 'is-active' : '' }}">
                <span class="adm-nav-icon"><i class="fas fa-chart-pie"></i></span><span>لوحة التحكم</span>
            </a>
        </div>
        <div class="adm-nav-group">
            <span class="adm-nav-label">المتجر</span>
            <a href="{{ route('vendor.products.index') }}" class="adm-nav-item {{ request()->routeIs('vendor.products.*') ? 'is-active' : '' }}">
                <span class="adm-nav-icon"><i class="fas fa-box"></i></span><span>المنتجات</span>
            </a>
            <a href="{{ route('vendor.orders.index') }}" class="adm-nav-item {{ request()->routeIs('vendor.orders.*') ? 'is-active' : '' }}">
                <span class="adm-nav-icon"><i class="fas fa-shopping-bag"></i></span><span>الطلبات</span>
            </a>
            <a href="{{ route('vendor.reviews.index') }}" class="adm-nav-item {{ request()->routeIs('vendor.reviews.*') ? 'is-active' : '' }}">
                <span class="adm-nav-icon"><i class="fas fa-star"></i></span><span>التقييمات</span>
            </a>
        </div>
        <div class="adm-nav-group">
            <span class="adm-nav-label">المالية</span>
            <a href="{{ route('vendor.earnings') }}" class="adm-nav-item {{ request()->routeIs('vendor.earnings') ? 'is-active' : '' }}">
                <span class="adm-nav-icon"><i class="fas fa-coins"></i></span><span>الأرباح</span>
            </a>
            <a href="{{ route('vendor.withdrawals.index') }}" class="adm-nav-item {{ request()->routeIs('vendor.withdrawals.*') ? 'is-active' : '' }}">
                <span class="adm-nav-icon"><i class="fas fa-wallet"></i></span><span>السحب</span>
            </a>
        </div>
        @endif
        <div class="adm-nav-group">
            <span class="adm-nav-label">الحساب</span>
            <a href="{{ route('vendor.store.edit') }}" class="adm-nav-item {{ request()->routeIs('vendor.store.*') ? 'is-active' : '' }}">
                <span class="adm-nav-icon"><i class="fas fa-store"></i></span><span>بيانات المتجر</span>
            </a>
            @if($vendor->isApproved())
            <a href="{{ route('vendor.settings.edit') }}" class="adm-nav-item {{ request()->routeIs('vendor.settings.*') ? 'is-active' : '' }}">
                <span class="adm-nav-icon"><i class="fas fa-cog"></i></span><span>الإعدادات</span>
            </a>
            @endif
            <a href="{{ route('vendor.notifications.index') }}" class="adm-nav-item {{ request()->routeIs('vendor.notifications.*') ? 'is-active' : '' }}">
                <span class="adm-nav-icon"><i class="fas fa-bell"></i></span><span>الإشعارات</span>
                @if($unread)<span class="adm-nav-badge">{{ $unread }}</span>@endif
            </a>
            <a href="{{ route('home') }}" class="adm-nav-item"><span class="adm-nav-icon"><i class="fas fa-globe"></i></span><span>الموقع</span></a>
        </div>
    </nav>
    <div class="adm-sidebar-foot">
        <div class="adm-user">
            <div class="adm-user-avatar">{{ mb_substr($vendor->store_name, 0, 1) }}</div>
            <div class="adm-user-info">
                <span class="adm-user-name">{{ $vendor->store_name }}</span>
                <span class="adm-user-role">{{ $vendor->status_label }}</span>
            </div>
        </div>
    </div>
</aside>
<div class="adm-overlay" id="admOverlay"></div>
<div class="adm-main" id="admMain">
    <header class="adm-topbar">
        <div class="adm-topbar-right">
            <button class="adm-toggle-btn" id="admToggleBtn"><i class="fas fa-bars"></i></button>
            <div class="adm-page-info">
                <h1>@yield('page-title', 'لوحة البائع')</h1>
            </div>
        </div>
        <div class="adm-topbar-left">
            <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="adm-topbar-btn">{{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}</a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="adm-topbar-btn" type="submit"><i class="fas fa-sign-out-alt"></i></button>
            </form>
        </div>
    </header>
    @if (session('success'))
        <div class="adm-alert adm-alert-success"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>
    @endif
    @if (session('error'))
        <div class="adm-alert adm-alert-error"><i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span></div>
    @endif
    <main class="admin-content">@yield('content')</main>
</div>
<script src="{{ asset('js/admin.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')
</body>
</html>
