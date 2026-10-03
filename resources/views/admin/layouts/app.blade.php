<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('app.dashboard')) — urca</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom-colors.css') }}">
    @if (app()->getLocale() === 'en')
        <style>
            .adm-sidebar {
                right: auto;
                left: 0;
                border-left: none;
                border-right: 1px solid var(--gray-200);
            }


            .adm-main {
                margin-right: 0;
                margin-left: 256px;
            }

            .adm-nav-icon {
                margin-left: 0;
                margin-right: 1rem;
            }

            .adm-nav-count,
            .adm-nav-badge {
                margin-right: auto;
                margin-left: 0;
            }

            /* ── Topbar ── */
            .adm-topbar {
                background: #fff;
                height: var(--topbar-h);
                border-bottom: 1px solid var(--ink-200);
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 1.75rem;
                position: sticky;
                top: 0;
                z-index: 100;
                box-shadow: var(--sh-sm);
                flex-shrink: 0;
            }

            .adm-topbar-left {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .adm-breadcrumb i.fa-chevron-left {
                transform: rotate(180deg);
            }

            @media (max-width: 1024px) {
                .adm-sidebar {
                    transform: translateX(-100%);
                }

                .adm-sidebar.is-open {
                    transform: translateX(0);
                }

                .adm-main {
                    margin-left: 0;
                }
            }
        </style>
    @endif
    @stack('styles')
</head>

<body class="admin-body">

    <!-- ══════════════════════════════════════
     SIDEBAR
    ══════════════════════════════════════ -->
    <aside class="adm-sidebar" id="admSidebar">

        <!-- Logo -->
        <div class="adm-sidebar-head">
            <a href="{{ route('admin.dashboard') }}" class="adm-logo">
                <img src="{{ asset('images/urca_logo.png') }}" alt="urca" class="adm-logo-img"
                    onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex'">
                <span style="display:none;align-items:center;gap:6px;font-weight:900;font-size:1rem;color:#030f1f;">
                    <i class="fas fa-tv" style="color:#051836;"></i> ميلاد<span style="color:#051836;">سامي</span>
                </span>
            </a>
            <button class="adm-sidebar-close" id="admSidebarClose">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="adm-nav">

            <div class="adm-nav-group">
                <a href="{{ route('admin.dashboard') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-chart-pie"></i></span>
                    <span>{{ __('app.dashboard') }}</span>
                </a>
            </div>

            <div class="adm-nav-group">
                <span class="adm-nav-label">{{ __('app.store') }}</span>
                <a href="{{ route('admin.products.index') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.products.index') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-box"></i></span>
                    <span>{{ __('app.products') }}</span>
                    <span class="adm-nav-count">{{ \App\Models\Product::count() }}</span>
                </a>
                <a href="{{ route('admin.categories.index') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-tags"></i></span>
                    <span>{{ __('app.categories') }}</span>
                </a>
                <a href="{{ route('admin.products.create') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.products.create') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-plus"></i></span>
                    <span>{{ __('app.add_product') }}</span>
                </a>
            </div>

            <div class="adm-nav-group">
                <span class="adm-nav-label">{{ __('app.orders') }}</span>
                <a href="{{ route('admin.orders.index') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.orders.index') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-shopping-bag"></i></span>
                    <span>{{ __('app.orders') }}</span>
                    @php $pending = \App\Models\Order::where('status','pending')->count(); @endphp
                    @if ($pending > 0)
                        <span class="adm-nav-badge">{{ $pending }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="adm-nav-item">
                    <span class="adm-nav-icon"><i class="fas fa-clock"></i></span>
                    <span>{{ __('app.pending_orders') }}</span>
                </a>
                <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="adm-nav-item">
                    <span class="adm-nav-icon"><i class="fas fa-cog"></i></span>
                    <span>{{ __('app.processing') }}</span>
                </a>
            </div>

            <div class="adm-nav-group">
                <span class="adm-nav-label">Marketplace</span>
                <a href="{{ route('admin.vendors.index') }}" class="adm-nav-item {{ request()->routeIs('admin.vendors.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-store"></i></span>
                    <span>البائعون</span>
                    @php $pv = \App\Models\Vendor::where('status','pending')->count(); @endphp
                    @if($pv > 0)<span class="adm-nav-badge">{{ $pv }}</span>@endif
                </a>
                <a href="{{ route('admin.products.index', ['approval_status' => 'pending']) }}" class="adm-nav-item">
                    <span class="adm-nav-icon"><i class="fas fa-clipboard-check"></i></span>
                    <span>منتجات قيد المراجعة</span>
                </a>
                <a href="{{ route('admin.withdrawals.index') }}" class="adm-nav-item {{ request()->routeIs('admin.withdrawals.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-wallet"></i></span>
                    <span>السحوبات</span>
                </a>
                <a href="{{ route('admin.finance.index') }}" class="adm-nav-item {{ request()->routeIs('admin.finance.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-chart-line"></i></span>
                    <span>المالية والتقارير</span>
                </a>
            </div>

            <div class="adm-nav-group">
                <span class="adm-nav-label">{{ __('app.system') }}</span>
                <a href="{{ route('admin.users.index') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.users.index') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-users"></i></span>
                    <span>{{ __('app.users') }}</span>
                    <span class="adm-nav-count">{{ \App\Models\User::count() }}</span>
                </a>
            </div>

            <div class="adm-nav-group">
                <span class="adm-nav-label">{{ __('app.content') }}</span>
                <a href="{{ route('admin.blog.index') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.blog.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-pen-nib"></i></span>
                    <span>{{ __('app.blog') }}</span>
                    <span class="adm-nav-count">{{ \App\Models\BlogPost::count() }}</span>
                </a>
                <a href="{{ route('admin.about.index') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.about.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-info-circle"></i></span>
                    <span>{{ __('app.about') }}</span>
                </a>
                <a href="{{ route('admin.testimonials.index') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.testimonials.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-star"></i></span>
                    <span>{{ __('app.manage_testimonials') }}</span>
                    <span
                        class="adm-nav-count">{{ \App\Models\Testimonial::where('is_approved', false)->count() }}</span>
                </a>
                <a href="{{ route('admin.brands.index') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.brands.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-copyright"></i></span>
                    <span>{{ app()->getLocale() === 'ar' ? 'العلامات التجارية' : 'Brands' }}</span>
                </a>
                <a href="{{ route('admin.faqs.index') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.faqs.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-question-circle"></i></span>
                    <span>{{ app()->getLocale() === 'ar' ? 'الأسئلة الشائعة' : 'FAQs' }}</span>
                </a>
                <a href="{{ route('admin.settings.index') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-sliders-h"></i></span>
                    <span>{{ app()->getLocale() === 'ar' ? 'إعدادات الرئيسية' : 'Home Settings' }}</span>
                </a>
                <a href="{{ route('admin.sliders.index') }}"
                    class="adm-nav-item {{ request()->routeIs('admin.sliders.*') ? 'is-active' : '' }}">
                    <span class="adm-nav-icon"><i class="fas fa-images"></i></span>
                    <span>{{ app()->getLocale() === 'ar' ? 'صور السلايدر' : 'Slider Images' }}</span>
                </a>
            </div>

            <div class="adm-nav-group">
                <a href="{{ route('home') }}" target="_blank" class="adm-nav-item">
                    <span class="adm-nav-icon"><i class="fas fa-globe"></i></span>
                    <span>{{ __('app.view_site') }}</span>
                    <i class="fas fa-external-link-alt"
                        style="font-size:.625rem;margin-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}:auto;opacity:.4"></i>
                </a>
            </div>

        </nav>

        <!-- User -->
        <div class="adm-sidebar-foot">
            <div class="adm-user">
                <div class="adm-user-avatar">
                    {{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}
                </div>
                <div class="adm-user-info">
                    <span class="adm-user-name">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <span class="adm-user-role">Admin</span>
                </div>
            </div>
        </div>

    </aside>

    <!-- Overlay -->
    <div class="adm-overlay" id="admOverlay"></div>

    <!-- ══════════════════════════════════════
     MAIN
══════════════════════════════════════ -->
    <div class="adm-main" id="admMain">

        <!-- Topbar -->
        <header class="adm-topbar">
            <div class="adm-topbar-right">
                <button class="adm-toggle-btn" id="admToggleBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="adm-page-info">
                    <h1>@yield('page-title', __('app.dashboard'))</h1>
                    <nav class="adm-breadcrumb">
                        <a href="{{ route('admin.dashboard') }}">{{ __('app.dashboard') }}</a>
                        @yield('breadcrumb')
                    </nav>
                </div>
            </div>
            <div class="adm-topbar-left">
                <!-- Language Switcher -->
                <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}"
                    class="adm-topbar-btn" title="{{ __('app.language') }}"
                    style="text-decoration: none; font-weight: bold; font-size: 0.9rem;">
                    {{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}
                </a>

                @php $pendingCount = \App\Models\Order::where('status','pending')->count(); @endphp
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="adm-topbar-btn"
                    title="{{ __('app.pending_orders') }}">
                    <i class="fas fa-bell"></i>
                    @if ($pendingCount > 0)
                        <span class="adm-topbar-dot">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('home') }}" target="_blank" class="adm-topbar-btn"
                    title="{{ __('app.view_site') }}">
                    <i class="fas fa-globe"></i>
                </a>
                <div class="adm-topbar-user">
                    <div class="adm-topbar-avatar">
                        {{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}
                    </div>
                    <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                </div>
            </div>
        </header>

        <!-- Alerts -->
        @if (session('success'))
            <div class="adm-alert adm-alert-success" id="admAlert">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            </div>
        @endif
        @if (session('error'))
            <div class="adm-alert adm-alert-error" id="admAlert">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            </div>
        @endif

        <!-- Content -->
        <main class="admin-content">
            @yield('content')
        </main>

    </div>

    <script src="{{ asset('js/admin.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>

</html>
