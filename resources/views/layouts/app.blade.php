@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isAr ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'urca - متجر إلكترونيات وإكسسوارات كمبيوتر')</title>
    <meta name="description" content="@yield('description', 'urca - وجهتك الأولى لأفضل الإلكترونيات، ماوسات، سماعات، طابعات، فلاشات وملحقات الكمبيوتر في مصر. جودة أصلية وأفضل أسعار.')">

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <!-- Preconnect to external origins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

    <!-- Google Fonts: async load with locale-aware subset and display=swap -->
    @if ($isAr)
        <link rel="preload" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap"
            as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap"
                rel="stylesheet">
        </noscript>
    @else
        <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap"
            as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap"
                rel="stylesheet">
        </noscript>
    @endif

    <!-- Bootstrap 5 — RTL or LTR (sync) -->
    @if ($isAr)
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    @endif

    <!-- Font Awesome — async load (non-render-blocking) -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </noscript>

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom-colors.css') }}">

    @if ($isAr)
        <style>
            /* Arabic: Cairo — 2 weights only */
            body,
            p,
            span,
            a,
            h1,
            h2,
            h3,
            h4,
            h5,
            h6,
            li,
            td,
            th,
            label,
            input,
            textarea,
            select,
            button,
            .urca-nav-link,
            .urca-mobile-link,
            .urca-footer-heading,
            .urca-footer-links a,
            .urca-footer-contact li,
            .urca-bn-item span,
            .cart-sidebar-header h3 {
                font-family: 'Cairo', system-ui, sans-serif !important;
            }

            /* RTL mega-menu alignment fix */
            .urca-mega-menu {
                left: auto !important;
                right: 0 !important;
                transform: translateX(0) translateY(8px) !important;
            }

            .urca-dropdown:hover .urca-mega-menu {
                transform: translateX(0) translateY(0) !important;
            }
        </style>
    @else
        <style>
            body,
            p,
            span,
            a,
            h1,
            h2,
            h3,
            h4,
            h5,
            h6,
            li,
            td,
            th,
            label,
            input,
            textarea,
            select,
            button,
            .urca-nav-link,
            .urca-mobile-link,
            .urca-footer-heading,
            .urca-footer-links a,
            .urca-footer-contact li,
            .urca-bn-item span,
            .cart-sidebar-header h3 {
                font-family: 'Inter', system-ui, sans-serif !important;
            }
        </style>
    @endif
    @stack('styles')
</head>

<body>
    <!-- ===== MAIN NAVBAR ===== -->
    <nav class="urca-navbar sticky-top " id="urcaNavbar" style="z-index: 10000;">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between gap-3 py-2">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="urca-brand flex-shrink-0">
                    <img src="{{ asset('images/urca-logo.png') }}" alt="urca" class="urca-logo-img" width="140"
                        height="40" fetchpriority="high"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div style="display:none;align-items:center;gap:8px;">
                        <i class="fas fa-tv" style="font-size:1.75rem;color:#051836;"></i>
                        <span style="font-size:1.1rem;font-weight:900;color:#0F172A;letter-spacing:-.02em;">ميلاد<span
                                style="color:#051836;">سامي</span></span>
                    </div>
                </a>
                <!-- Nav Links desktop -->
                <div class="d-none d-lg-flex align-items-center gap-1" id="navbarMenu">
                    <a href="{{ route('home') }}"
                        class="urca-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">{{ __('app.nav_home') }}</a>

                    <div class="urca-dropdown">
                        <a href="{{ route('products.index') }}"
                            class="urca-nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                            {{ __('app.nav_products') }} <i class="fas fa-chevron-down urca-arrow ms-1"></i>
                        </a>
                        <div class="urca-mega-menu">
                            <div class="urca-mega-inner">
                                @forelse($navCategories as $category)
                                    <div class="urca-mega-col">
                                        {{-- Root heading --}}
                                        <p class="urca-mega-heading d-flex align-items-center gap-2">
                                            @if ($category->image)
                                                <img src="{{ asset('storage/' . $category->image) }}" alt=""
                                                    style="width:22px;height:22px;object-fit:cover;border-radius:4px;">
                                            @elseif($category->icon)
                                                <i
                                                    class="{{ str_starts_with($category->icon, 'fa-') || str_starts_with($category->icon, 'fas') ? $category->icon : 'fas fa-' . $category->icon }}"></i>
                                            @endif
                                            <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                                                style="color:inherit;text-decoration:none;">
                                                {{ $isAr ? $category->name_ar : $category->name_en }}
                                            </a>
                                        </p>

                                        {{-- Sub-categories (level 1) --}}
                                        @if ($category->children->isNotEmpty())
                                            @foreach ($category->children as $sub)
                                                <div style="margin-bottom:.15rem;">
                                                    <a href="{{ route('products.index', ['category' => $sub->slug]) }}"
                                                        style="display:flex;align-items:center;gap:.35rem;padding:.2rem 0;font-size:.82rem;color:#334155;text-decoration:none;transition:color .15s;"
                                                        onmouseover="this.style.color='#051836'"
                                                        onmouseout="this.style.color='#334155'">
                                                        @if ($sub->icon)
                                                            <i class="fas fa-{{ $sub->icon }}"
                                                                style="font-size:.7rem;color:#94a3b8;width:12px;"></i>
                                                        @else
                                                            <i class="fas fa-chevron-left"
                                                                style="font-size:.6rem;color:#cbd5e1;width:12px;"></i>
                                                        @endif
                                                        {{ $isAr ? $sub->name_ar : $sub->name_en }}
                                                    </a>
                                                    {{-- Level 2 (grandchildren) --}}
                                                    @if ($sub->children->isNotEmpty())
                                                        @foreach ($sub->children as $subsub)
                                                            <a href="{{ route('products.index', ['category' => $subsub->slug]) }}"
                                                                style="display:block;padding:.15rem 0 .15rem 1.1rem;font-size:.77rem;color:#64748b;text-decoration:none;transition:color .15s;"
                                                                onmouseover="this.style.color='#051836'"
                                                                onmouseout="this.style.color='#64748b'">
                                                                — {{ $isAr ? $subsub->name_ar : $subsub->name_en }}
                                                            </a>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endforeach
                                        @else
                                            {{-- Root with no children: show as single link --}}
                                            <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                                                style="font-size:.82rem;color:#334155;text-decoration:none;">
                                                {{ $isAr ? __('app.prod_browse') : 'Browse' }}
                                            </a>
                                        @endif
                                    </div>
                                @empty
                                    <div class="urca-mega-col">
                                        <p class="urca-mega-heading">{{ __('app.no_categories') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('news.index') }}"
                        class="urca-nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}">{{ __('app.nav_news') }}</a>
                    <a href="{{ route('blog.index') }}"
                        class="urca-nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}">{{ __('app.nav_blog') }}</a>
                    <a href="{{ route('about.index') }}"
                        class="urca-nav-link {{ request()->routeIs('about.*') ? 'active' : '' }}">{{ __('app.nav_about') }}</a>
                    <a href="{{ route('track-order') }}"
                        class="urca-nav-link {{ request()->routeIs('track-order*') ? 'active' : '' }}">
                        <i class="fas fa-shipping-fast me-1"></i>{{ __('app.track_order') }}
                    </a>
                </div>

                <!-- Actions -->
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <div class="urca-search-wrap" id="navSearchWrap">
                        <form action="{{ route('products.index') }}" method="GET" class="urca-search-form">
                            <input type="text" name="search" placeholder="{{ __('app.nav_search') }}"
                                value="{{ request('search') }}" aria-label="Search">
                            <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    <button class="urca-icon-btn" id="navSearchToggle" aria-label="Toggle search">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('cart.index') }}" class="urca-icon-btn position-relative" id="cartBtn"
                        aria-label="Cart">
                        <i class="fas fa-shopping-bag"></i>
                        @if (count(session()->get('cart', [])) > 0)
                            <span class="urca-cart-badge"
                                id="cartBadge">{{ count(session()->get('cart', [])) }}</span>
                        @endif
                    </a>

                    {{-- Favourites --}}
                    <button class="urca-icon-btn position-relative" id="navFavBtn" aria-label="Favourites"
                        onclick="openFavDrawer()"
                        title="{{ app()->getLocale() === 'ar' ? 'المفضلة' : 'Favourites' }}">
                        <i class="fas fa-heart"></i>
                        <span class="urca-cart-badge d-none" id="navFavBadge" style="background:#f43f5e;">0</span>
                    </button>

                    {{-- Language Toggle --}}
                    <select onchange="window.location='/lang/'+this.value" aria-label="Switch language"
                        class="urca-lang-select d-none d-lg-flex">
                        <option value="en" {{ !$isAr ? 'selected' : '' }}>🌐 EN</option>
                        <option value="ar" {{ $isAr ? 'selected' : '' }}>🌐 ع</option>
                    </select>

                    {{-- Auth --}}
                    @auth
                        <div class="dropdown d-none d-lg-block">
                            <button class="urca-icon-btn dropdown-toggle" data-bs-toggle="dropdown" aria-label="Account"
                                style="gap:0;">
                                <i class="fas fa-user"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2"
                                style="min-width:180px;">
                                <li><span
                                        class="dropdown-item-text small text-muted fw-semibold">{{ Auth::user()->name }}</span>
                                </li>
                                <li>
                                    <hr class="dropdown-divider my-1">
                                </li>
                                @if (Auth::user()->isAdmin())
                                    <li>
                                        <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                                            <i class="fas fa-tachometer-alt me-2 text-primary"></i>{{ __('app.nav_dashboard') }}
                                        </a>
                                    </li>
                                @elseif (Auth::user()->isVendor())
                                    <li>
                                        <a href="{{ route('vendor.dashboard') }}" class="dropdown-item">
                                            <i class="fas fa-store me-2 text-primary"></i>لوحة البائع
                                        </a>
                                    </li>
                                @endif
                                    <li>
                                        <a href="{{ route('vendor.register') }}" class="dropdown-item">
                                            <i class="fas fa-store me-2"></i>بيع على urca
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider my-1">
                                    </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>{{ __('app.nav_signout') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="urca-icon-btn d-none d-lg-flex" aria-label="Sign in"
                            title="{{ __('app.nav_signin') }}">
                            <i class="fas fa-sign-in-alt"></i>
                        </a>
                    @endauth
                    <button class="urca-hamburger d-lg-none" id="mobileMenuBtn" aria-label="Menu">
                        <span></span><span></span><span></span>
                    </button>
                </div>

            </div>

            <!-- Mobile Menu -->
            <div class="urca-mobile-menu d-lg-none" id="mobileMenu">
                <a href="{{ route('home') }}"
                    class="urca-mobile-link {{ request()->routeIs('home') ? 'active' : '' }}">{{ __('app.nav_home') }}</a>
                <a href="{{ route('products.index') }}"
                    class="urca-mobile-link {{ request()->routeIs('products.*') ? 'active' : '' }}">{{ __('app.nav_products') }}</a>
                <a href="{{ route('news.index') }}"
                    class="urca-mobile-link {{ request()->routeIs('news.*') ? 'active' : '' }}">{{ __('app.nav_news') }}</a>
                <a href="{{ route('blog.index') }}"
                    class="urca-mobile-link {{ request()->routeIs('blog.*') ? 'active' : '' }}">{{ __('app.nav_blog') }}</a>
                <a href="{{ route('about.index') }}"
                    class="urca-mobile-link {{ request()->routeIs('about.*') ? 'active' : '' }}">{{ __('app.nav_about') }}</a>
                <a href="{{ route('track-order') }}"
                    class="urca-mobile-link {{ request()->routeIs('track-order*') ? 'active' : '' }}"><i
                        class="fas fa-shipping-fast me-2"></i>{{ __('app.track_order') }}</a>

                {{-- Language Switch (mobile) --}}
                <div class="px-3 py-2">
                    <select onchange="window.location='/lang/'+this.value" aria-label="Switch language"
                        class="urca-lang-select w-100">
                        <option value="en" {{ !$isAr ? 'selected' : '' }}>🌐 English</option>
                        <option value="ar" {{ $isAr ? 'selected' : '' }}>🌐 العربية</option>
                    </select>
                </div>

                <hr class="my-2 opacity-25">
                @auth
                    <div class="px-3 py-2">
                        <p class="small text-muted mb-1 fw-semibold">{{ Auth::user()->name }}</p>
                        @if (Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary w-100 mb-2">
                                <i class="fas fa-tachometer-alt me-1"></i>{{ __('app.nav_dashboard') }}
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                <i class="fas fa-sign-out-alt me-1"></i>{{ __('app.nav_signout') }}
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="urca-mobile-link"><i
                            class="fas fa-sign-in-alt me-2"></i>{{ __('app.nav_signin') }}</a>
                    <a href="{{ route('register') }}" class="urca-mobile-link"><i
                            class="fas fa-user-plus me-2"></i>{{ __('app.nav_register') }}</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main id="main-content">
        @yield('content')
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="urca-footer">
        <div class="urca-footer-top">
            <div class="container">
                <div class="row g-5">

                    <!-- Brand -->
                    <div class="col-lg-4">
                        <img src="{{ asset('images/urca-logo.png') }}" alt="urca" class="urca-footer-logo mb-3"
                            width="140" height="40" loading="lazy" decoding="async"
                            onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <div class="d-none align-items-center gap-2 mb-3">
                            <i class="fas fa-tv fa-2x text-primary"></i>
                            <span class="text-white fw-bold fs-5">urca<span class="text-primary">سامي</span></span>
                        </div>
                        <p class="text-secondary small lh-lg">{{ __('app.footer_tagline') }}</p>
                        <div class="d-flex gap-2 mt-3">
                            <a href="https://www.facebook.com/share/181WTrqgHu/" target="_blank"
                                rel="noopener noreferrer" class="urca-footer-social" aria-label="Facebook"
                                title="تابعنا على Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://www.instagram.com/urca .tv/" target="_blank"
                                rel="noopener noreferrer" class="urca-footer-social" aria-label="Instagram"
                                title="تابعنا على Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://wa.me/201093803270" target="_blank" rel="noopener noreferrer"
                                class="urca-footer-social" aria-label="WhatsApp" title="راسلنا على WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="tel:+201093803270" class="urca-footer-social" aria-label="Phone"
                                title="اتصل بنا">
                                <i class="fas fa-phone-alt"></i>
                            </a>
                            <a href="https://www.youtube.com/@urca -tv" target="_blank" rel="noopener noreferrer"
                                class="urca-footer-social" aria-label="YouTube" title="قناتنا على YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="col-sm-6 col-lg-2">
                        <h6 class="urca-footer-heading">{{ __('app.quick_links') }}</h6>
                        <ul class="list-unstyled urca-footer-links">
                            <li><a href="{{ route('home') }}">{{ __('app.nav_home') }}</a></li>
                            <li><a href="{{ route('products.index') }}">{{ __('app.nav_products') }}</a></li>
                            <li><a href="{{ route('news.index') }}">{{ __('app.nav_news') }}</a></li>
                            <li><a href="{{ route('blog.index') }}">{{ __('app.nav_blog') }}</a></li>
                            <li><a href="{{ route('about.index') }}">{{ __('app.nav_about') }}</a></li>
                            <li><a href="{{ route('track-order') }}"><i
                                        class="fas fa-shipping-fast me-1 text-primary"></i>{{ __('app.track_order') }}</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Categories -->
                    <div class="col-sm-6 col-lg-2">
                        <h6 class="urca-footer-heading">{{ __('app.categories') }}</h6>
                        <ul class="list-unstyled urca-footer-links">
                            @forelse($navCategories as $category)
                                <li><a href="{{ route('products.index', ['category' => $category->slug]) }}">
                                        @if ($category->icon)
                                            <i class="{{ $category->icon }} me-2 text-primary"></i>
                                        @endif
                                        {{ $isAr ? $category->name_ar : $category->name_en }}
                                    </a></li>
                            @empty
                                <li><span class="text-muted small">{{ __('app.no_categories') }}</span></li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Contact + Newsletter -->
                    <div class="col-lg-4">
                        <h6 class="urca-footer-heading">{{ __('app.get_in_touch') }}</h6>
                        <ul class="list-unstyled urca-footer-contact mb-4">
                            <li><i class="fas fa-map-marker-alt text-primary"></i>{{ __('app.footer_address') }}</li>
                            <li>
                                <i class="fas fa-phone-alt text-primary"></i>
                                <a href="tel:+201093803270" class="text-decoration-none" style="color:inherit;">+20
                                    10 93803270</a>
                            </li>
                            <li>
                                <i class="fas fa-envelope text-primary"></i>
                                <a href="mailto:urca .tv@gmail.com" class="text-decoration-none"
                                    style="color:inherit;">urca .tv@gmail.com</a>
                            </li>
                            <li><i class="fas fa-clock text-primary"></i>{{ __('app.footer_hours') }}</li>
                        </ul>
                        <a href="https://www.google.com/maps/place/30%C2%B056'37.3%22N+31%C2%B017'22.0%22E/@30.9436857,31.2894227,21z"
                            target="_blank" rel="noopener noreferrer" class="btn btn-sm w-100 mb-3"
                            style="background:rgba(5,24,54,.1);color:#051836;border-radius:8px;font-weight:600;transition:all .3s ease;"
                            onmouseover="this.style.background='#051836';this.style.color='#fff';"
                            onmouseout="this.style.background='rgba(5,24,54,.1)';this.style.color='#051836';">
                            <i
                                class="fas fa-map-marker-alt me-2"></i>{{ app()->getLocale() === 'ar' ? 'عرض الموقع على الخريطة' : 'View on Map' }}
                        </a>
                        <h6 class="urca-footer-heading">{{ __('app.newsletter') }}</h6>
                        <form class="urca-newsletter-form" id="newsletterForm">
                            @csrf
                            <input type="email" name="email" id="newsletterEmail"
                                placeholder="{{ __('app.newsletter_ph') }}" aria-label="Email for newsletter"
                                required>
                            <button type="submit" aria-label="Subscribe" id="newsletterBtn"><i
                                    class="fas fa-paper-plane"></i></button>
                        </form>
                        <div id="newsletterMsg" class="mt-2 small d-none"></div>
                    </div>

                </div>
            </div>
        </div>

        <div class="urca-footer-bottom">
            <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
                <p class="mb-0 small text-secondary">{{ __('app.footer_copy', ['year' => date('Y')]) }}</p><br>
                <p class="mb-0 small text-secondary">{{ __('app.rights') }}</p>
                <div class="d-flex gap-3 align-items-center" style="font-size:1.5rem; color:#64748b;">
                    <i class="fab fa-cc-visa" title="Visa"></i>
                    <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                    <i class="fas fa-money-bill-wave" title="Cash"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- Cart Sidebar -->
    <div class="cart-overlay" id="cartOverlay"></div>
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-sidebar-header">
            <h3><i class="fas fa-shopping-bag me-2"></i>{{ __('app.your_cart') }}</h3>
            <button class="close-cart" id="closeCart" aria-label="Close cart"><i class="fas fa-times"></i></button>
        </div>
        <div class="cart-sidebar-body" id="cartSidebarBody"></div>
        <div class="cart-sidebar-footer">
            <div class="cart-total">
                <span>{{ __('app.total') }}</span>
                <span class="total-price" id="sidebarTotal">0 EGP</span>
            </div>
            <a href="{{ route('cart.index') }}" class="btn-primary-full">{{ __('app.view_full_cart') }}</a>
        </div>
    </div>

    {{-- ── Favourites Drawer ── --}}
    <div class="fav-overlay" id="favOverlay" onclick="closeFavDrawer()"></div>
    <div class="fav-drawer" id="favDrawer" aria-label="Favourites drawer">
        <div class="fav-drawer-header">
            <h3><i class="fas fa-heart me-2"
                    style="color:#f43f5e;"></i>{{ app()->getLocale() === 'ar' ? 'المفضلة' : 'Favourites' }}</h3>
            <button class="fav-drawer-close" onclick="closeFavDrawer()" aria-label="Close"><i
                    class="fas fa-times"></i></button>
        </div>
        <div class="fav-drawer-body" id="favDrawerBody">
            <div class="fav-empty" id="favEmpty">
                <i class="fas fa-heart-broken"></i>
                <p>{{ app()->getLocale() === 'ar' ? 'لا توجد منتجات في المفضلة' : 'No favourites yet' }}</p>
            </div>
        </div>
    </div>

    <style>
        /* ── Fav Drawer ── */
        .fav-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 10998;
            opacity: 0;
            pointer-events: none;
            transition: opacity .3s ease;
        }

        .fav-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        /* Default (LTR): slides in from the RIGHT */
        .fav-drawer {
            position: fixed;
            top: 0;
            right: -400px;
            left: auto;
            width: 360px;
            max-width: 92vw;
            height: 100%;
            background: #fff;
            z-index: 10999;
            display: flex;
            flex-direction: column;
            box-shadow: -4px 0 32px rgba(0, 0, 0, .15);
            transition: right .32s cubic-bezier(.4, 0, .2, 1);
        }

        .fav-drawer.show {
            right: 0;
        }

        /* RTL override: slides in from the LEFT */
        html[dir="rtl"] .fav-drawer {
            right: auto;
            left: -400px;
            box-shadow: 4px 0 32px rgba(0, 0, 0, .15);
            transition: left .32s cubic-bezier(.4, 0, .2, 1);
        }

        html[dir="rtl"] .fav-drawer.show {
            left: 0;
            right: auto;
        }

        .fav-drawer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            background: #fff;
            flex-shrink: 0;
        }

        .fav-drawer-header h3 {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
            color: #0f172a;
        }

        .fav-drawer-close {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .2s, color .2s;
        }

        .fav-drawer-close:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        .fav-drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .fav-empty {
            text-align: center;
            padding: 3rem 1rem;
            color: #94a3b8;
        }

        .fav-empty i {
            font-size: 3rem;
            margin-bottom: .75rem;
            display: block;
        }

        .fav-empty p {
            font-size: .95rem;
        }

        .fav-item {
            display: flex;
            align-items: center;
            gap: .875rem;
            padding: .75rem;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
            margin-bottom: .625rem;
            background: #fff;
            transition: box-shadow .2s;
        }

        .fav-item:hover {
            box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
        }

        .fav-item-img {
            width: 64px;
            height: 64px;
            border-radius: 8px;
            object-fit: cover;
            background: #f8fafc;
            flex-shrink: 0;
            border: 1px solid #e2e8f0;
        }

        .fav-item-img-placeholder {
            width: 64px;
            height: 64px;
            border-radius: 8px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cbd5e1;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .fav-item-info {
            flex: 1;
            min-width: 0;
        }

        .fav-item-name {
            font-size: .875rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: .2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fav-item-price {
            font-size: .9rem;
            font-weight: 700;
            color: #051836;
        }

        .fav-item-remove {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: #fef2f2;
            color: #f43f5e;
            font-size: .8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .2s;
        }

        .fav-item-remove:hover {
            background: #fee2e2;
        }

        .fav-item-cart {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: #eff6ff;
            color: #2563eb;
            font-size: .8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .2s, color .2s;
        }

        .fav-item-cart:hover {
            background: #2563eb;
            color: #fff;
        }

        .fav-item-actions {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-shrink: 0;
        }

        #navFavBtn.has-favs .fa-heart {
            color: #f43f5e;
        }

        /* ── Fly-to-heart animation ── */
        .fav-fly {
            position: fixed;
            z-index: 99999;
            pointer-events: none;
            color: #f43f5e;
            font-size: 1.75rem;
            line-height: 1;
            will-change: transform, opacity;
        }
    </style>

    <script>
        /* ═══════════════════════════════════════════════════
                   WISHLIST SYSTEM
                   - Guest  : localStorage
                   - Logged : AJAX → DB  (+ sync on login)
                ═══════════════════════════════════════════════════ */
        (function() {
            var CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            var IS_AUTH = {{ Auth::check() ? 'true' : 'false' }};
            var SYNC_NOW = {{ session('wishlist_sync') ? 'true' : 'false' }};

            /* ── localStorage keys (guest fallback) ── */
            var LS_IDS = 'urca_favs';
            var LS_DATA = 'urca_favs_data';

            function lsGetIds() {
                try {
                    return JSON.parse(localStorage.getItem(LS_IDS) || '[]');
                } catch (e) {
                    return [];
                }
            }

            function lsGetData() {
                try {
                    return JSON.parse(localStorage.getItem(LS_DATA) || '{}');
                } catch (e) {
                    return {};
                }
            }

            function lsSaveIds(a) {
                localStorage.setItem(LS_IDS, JSON.stringify(a));
            }

            function lsSaveData(o) {
                localStorage.setItem(LS_DATA, JSON.stringify(o));
            }

            function lsClear() {
                localStorage.removeItem(LS_IDS);
                localStorage.removeItem(LS_DATA);
            }

            /* ── in-memory state (filled from DB or LS) ── */
            var _ids = []; // array of int
            var _data = {}; // { "id": {id,name,price,img} }

            /* ── helpers ── */
            function ajax(method, url, body) {
                return fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: body ? JSON.stringify(body) : undefined,
                }).then(function(r) {
                    return r.json();
                });
            }

            /* ── badge ── */
            function updateBadge() {
                var btn = document.getElementById('navFavBtn');
                var badge = document.getElementById('navFavBadge');
                if (!btn) return;
                var count = _ids.length;
                badge.textContent = count;
                if (count > 0) {
                    badge.classList.remove('d-none');
                    btn.classList.add('has-favs');
                } else {
                    badge.classList.add('d-none');
                    btn.classList.remove('has-favs');
                }
            }

            /* ── mark active fav buttons on page ── */
            function markButtons() {
                document.querySelectorAll('.fav-btn[data-id]').forEach(function(btn) {
                    btn.classList.toggle('active', _ids.includes(parseInt(btn.dataset.id)));
                });
            }

            /* ── load state ── */
            function loadState() {
                if (IS_AUTH) {
                    // fetch from DB
                    ajax('GET', '/wishlist').then(function(res) {
                        _ids = (res.ids || []).map(Number);
                        _data = {};
                        (res.items || []).forEach(function(p) {
                            _data[String(p.id)] = p;
                        });
                        updateBadge();
                        markButtons();
                    }).catch(function() {});
                } else {
                    // from localStorage
                    _ids = lsGetIds().map(Number);
                    _data = lsGetData();
                    updateBadge();
                    markButtons();
                }
            }

            /* ── sync localStorage → DB after login ── */
            function syncLocalToDb() {
                var guestIds = lsGetIds();
                if (guestIds.length === 0) {
                    lsClear();
                    return;
                }
                ajax('POST', '/wishlist/sync', {
                    ids: guestIds
                }).then(function() {
                    lsClear();
                    loadState();
                }).catch(function() {
                    lsClear();
                });
            }

            /* ── fly-to-heart animation ── */
            function flyHeart(fromEl) {
                var target = document.getElementById('navFavBtn');
                if (!target || !fromEl) return;

                var srcRect = fromEl.getBoundingClientRect();
                var dstRect = target.getBoundingClientRect();

                // create flying element
                var el = document.createElement('i');
                el.className = 'fas fa-heart fav-fly';
                el.style.left = (srcRect.left + srcRect.width / 2 - 14) + 'px';
                el.style.top = (srcRect.top + srcRect.height / 2 - 14) + 'px';
                document.body.appendChild(el);

                // calc delta to target center
                var dx = (dstRect.left + dstRect.width / 2) - (srcRect.left + srcRect.width / 2);
                var dy = (dstRect.top + dstRect.height / 2) - (srcRect.top + srcRect.height / 2);

                el.style.setProperty('--dx', dx + 'px');
                el.style.setProperty('--dy', dy + 'px');

                // override animation to actually travel to target
                el.style.animation = 'none';
                el.style.transition = 'transform .65s cubic-bezier(.2,.8,.3,1), opacity .65s ease';
                el.style.transform = 'scale(1)';
                el.style.opacity = '1';

                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        el.style.transform = 'translate(' + dx + 'px,' + dy + 'px) scale(.25)';
                        el.style.opacity = '0';
                    });
                });

                // pulse the nav icon when it arrives
                setTimeout(function() {
                    el.remove();
                    var icon = target.querySelector('.fa-heart');
                    if (icon) {
                        icon.style.transition = 'transform .2s ease';
                        icon.style.transform = 'scale(1.6)';
                        setTimeout(function() {
                            icon.style.transform = 'scale(1)';
                        }, 200);
                    }
                }, 650);
            }

            /* ── toggle (public) ── */
            window.toggleFav = function(btn) {
                var id = parseInt(btn.dataset.id);
                var name = btn.dataset.name || '';
                var price = parseFloat(btn.dataset.price) || 0;
                var img = btn.dataset.img || '';

                // optimistic UI
                var isAdding = !_ids.includes(id);
                if (isAdding) {
                    _ids.push(id);
                    _data[String(id)] = {
                        id: id,
                        name: name,
                        price: price,
                        img: img
                    };
                    btn.classList.add('active');
                    flyHeart(btn); // ← fly animation on add only
                } else {
                    _ids = _ids.filter(function(i) {
                        return i !== id;
                    });
                    delete _data[String(id)];
                    btn.classList.remove('active');
                }
                btn.classList.remove('pop');
                void btn.offsetWidth;
                btn.classList.add('pop');
                updateBadge();

                if (IS_AUTH) {
                    // AJAX
                    ajax('POST', '/wishlist/toggle/' + id).then(function(res) {
                        // server is source of truth — update count
                        var badge = document.getElementById('navFavBadge');
                        if (badge && res.count !== undefined) {
                            badge.textContent = res.count;
                            document.getElementById('navFavBtn').classList.toggle('has-favs', res.count >
                                0);
                            badge.classList.toggle('d-none', res.count === 0);
                        }
                    }).catch(function() {
                        // rollback on error
                        if (isAdding) {
                            _ids = _ids.filter(function(i) {
                                return i !== id;
                            });
                            delete _data[String(id)];
                            btn.classList.remove('active');
                        } else {
                            _ids.push(id);
                            _data[String(id)] = {
                                id: id,
                                name: name,
                                price: price,
                                img: img
                            };
                            btn.classList.add('active');
                        }
                        updateBadge();
                    });
                } else {
                    // localStorage
                    lsSaveIds(_ids);
                    lsSaveData(_data);
                }
            };

            /* ── open drawer ── */
            window.openFavDrawer = function() {
                if (IS_AUTH) {
                    // re-fetch fresh data
                    ajax('GET', '/wishlist').then(function(res) {
                        _ids = (res.ids || []).map(Number);
                        _data = {};
                        (res.items || []).forEach(function(p) {
                            _data[String(p.id)] = p;
                        });
                        updateBadge();
                        markButtons();
                        renderDrawer();
                    }).catch(function() {
                        renderDrawer();
                    });
                } else {
                    renderDrawer();
                }
                document.getElementById('favDrawer').classList.add('show');
                document.getElementById('favOverlay').classList.add('show');
                document.body.style.overflow = 'hidden';
            };

            /* ── close drawer ── */
            window.closeFavDrawer = function() {
                document.getElementById('favDrawer').classList.remove('show');
                document.getElementById('favOverlay').classList.remove('show');
                document.body.style.overflow = '';
            };

            /* ── remove from drawer ── */
            window.removeFav = function(id) {
                id = parseInt(id);
                _ids = _ids.filter(function(i) {
                    return i !== id;
                });
                delete _data[String(id)];
                updateBadge();
                var cardBtn = document.querySelector('.fav-btn[data-id="' + id + '"]');
                if (cardBtn) cardBtn.classList.remove('active');

                if (IS_AUTH) {
                    ajax('DELETE', '/wishlist/' + id).catch(function() {});
                } else {
                    lsSaveIds(_ids);
                    lsSaveData(_data);
                }
                renderDrawer();
            };

            /* ── render drawer ── */
            function renderDrawer() {
                var body = document.getElementById('favDrawerBody');
                var empty = document.getElementById('favEmpty');
                if (!body) return;
                body.querySelectorAll('.fav-item').forEach(function(el) {
                    el.remove();
                });

                if (_ids.length === 0) {
                    empty.style.display = 'block';
                    return;
                }
                empty.style.display = 'none';

                _ids.forEach(function(id) {
                    var p = _data[String(id)] || {};
                    var el = document.createElement('div');
                    el.className = 'fav-item';
                    el.innerHTML =
                        (p.img ?
                            '<img class="fav-item-img" src="' + p.img + '" alt="' + (p.name || '').replace(/"/g,
                                '&quot;') + '" loading="lazy">' :
                            '<div class="fav-item-img-placeholder"><i class="fas fa-image"></i></div>') +
                        '<div class="fav-item-info">' +
                        '<div class="fav-item-name">' + (p.name || '') + '</div>' +
                        '<div class="fav-item-price">' + (p.price ? Number(p.price).toLocaleString() + ' EGP' :
                            '') + '</div>' +
                        '</div>' +
                        '<div class="fav-item-actions">' +
                        '<button class="fav-item-cart" onclick="addToCart(' + id + ',\'' + (p.name || '')
                        .replace(/'/g, "\\'") + '\',' + (p.price || 0) + ',\'' + (p.img || '') +
                        '\')" aria-label="Add to cart" title="أضف للسلة"><i class="fas fa-shopping-cart"></i></button>' +
                        '<button class="fav-item-remove" onclick="removeFav(' + id +
                        ')" aria-label="Remove" title="إزالة"><i class="fas fa-times"></i></button>' +
                        '</div>';
                    body.insertBefore(el, empty);
                });
            }

            /* ── init ── */
            document.addEventListener('DOMContentLoaded', function() {
                if (IS_AUTH && SYNC_NOW) {
                    syncLocalToDb(); // sync guest favs after login
                } else {
                    loadState();
                }
            });
        }());
    </script>

    <!-- Toast -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Back to Top -->
    <button class="back-to-top" id="backToTop" aria-label="Back to top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Floating Social Buttons -->
    @include('components.social-float-buttons')

    <!-- ===== BOTTOM NAV (mobile only) ===== -->
    <nav class="urca-bottom-nav d-lg-none" aria-label="Mobile navigation">
        <a href="{{ route('home') }}" class="urca-bn-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>{{ __('app.bn_home') }}</span>
        </a>
        <a href="{{ route('products.index') }}"
            class="urca-bn-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i>
            <span>{{ __('app.bn_products') }}</span>
        </a>
        <a href="{{ route('cart.index') }}"
            class="urca-bn-item urca-bn-cart {{ request()->routeIs('cart.*') ? 'active' : '' }}" id="cartBtnMobile"
            aria-label="Cart">
            <div class="urca-bn-cart-bubble">
                <i class="fas fa-shopping-bag"></i>
                @if (count(session()->get('cart', [])) > 0)
                    <span class="urca-bn-badge" id="cartBadgeMobile">{{ count(session()->get('cart', [])) }}</span>
                @endif
            </div>
        </a>
        <a href="{{ route('news.index') }}"
            class="urca-bn-item {{ request()->routeIs('news.*') ? 'active' : '' }}">
            <i class="fas fa-newspaper"></i>
            <span>{{ __('app.bn_news') }}</span>
        </a>
        <a href="{{ route('about.index') }}"
            class="urca-bn-item {{ request()->routeIs('about.*') ? 'active' : '' }}">
            <i class="fas fa-info-circle"></i>
            <span>{{ __('app.bn_about') }}</span>
        </a>
        @auth
            <div class="urca-bn-item dropdown">
                <button class="urca-bn-item-btn" data-bs-toggle="dropdown" aria-label="Account" aria-expanded="false">
                    <i class="fas fa-user-circle"></i>
                    <span>{{ __('app.bn_account') }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3"
                    style="min-width:160px;bottom:68px;top:auto;position:fixed;right:4px;">
                    <li><span class="dropdown-item-text small text-muted fw-semibold">{{ Auth::user()->name }}</span>
                    </li>
                    <li>
                        <hr class="dropdown-divider my-1">
                    </li>
                    @if (Auth::user()->isAdmin())
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="dropdown-item small">
                                <i class="fas fa-tachometer-alt me-2 text-primary"></i>{{ __('app.nav_dashboard') }}
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider my-1">
                        </li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger small">
                                <i class="fas fa-sign-out-alt me-2"></i>{{ __('app.nav_signout') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @else
            <a href="{{ route('login') }}" class="urca-bn-item {{ request()->routeIs('login') ? 'active' : '' }}">
                <i class="fas fa-sign-in-alt"></i>
                <span>{{ __('app.bn_login') }}</span>
            </a>
        @endauth
    </nav>

    <!-- Scripts — deferred -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

    @stack('scripts')

    <!-- Newsletter AJAX -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('newsletterForm');
            if (!form) return;
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const email = document.getElementById('newsletterEmail')?.value.trim();
                const btn = document.getElementById('newsletterBtn');
                const msgEl = document.getElementById('newsletterMsg');
                if (!email) return;

                if (btn) {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    btn.disabled = true;
                }

                fetch('{{ route('newsletter.subscribe') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            email
                        }),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (msgEl) {
                            msgEl.classList.remove('d-none', 'text-danger', 'text-success');
                            if (data.success) {
                                msgEl.textContent = data.message;
                                msgEl.classList.add('text-success');
                                form.reset();
                            } else {
                                const msg = data.errors?.email?.[0] || data.message || 'Error';
                                msgEl.textContent = msg;
                                msgEl.classList.add('text-danger');
                            }
                        }
                    })
                    .catch(() => {
                        if (msgEl) {
                            msgEl.textContent =
                                '{{ app()->getLocale() === 'ar' ? 'حدث خطأ. حاول مرة أخرى.' : 'Something went wrong. Try again.' }}';
                            msgEl.classList.remove('d-none');
                            msgEl.classList.add('text-danger');
                        }
                    })
                    .finally(() => {
                        if (btn) {
                            btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                            btn.disabled = false;
                        }
                    });
            });

        });
    </script>
</body>

</html>
