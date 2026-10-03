@extends('layouts.app')

@section('title', $product['name'] . ' - urca')

@section('content')

    <div style="background:linear-gradient(135deg,#0f172a 0%,#030f1f 100%);padding:2.5rem 0;">
        <div class="container">
            <h1 class="text-white fw-bold mb-1">{{ Str::limit($product['name'], 60) }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home') }}"
                            class="text-white-50 text-decoration-none">{{ __('app.prod_breadcrumb_home') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('products.index') }}"
                            class="text-white-50 text-decoration-none">{{ __('app.prod_header_title') }}</a>
                    </li>
                    <li class="breadcrumb-item active text-white-50">{{ Str::limit($product['name'], 40) }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="container">

            {{-- ═══ TOP: Image + Info ═══ --}}
            <div class="pd-layout">

                {{-- ── Gallery ── --}}
                @php
                    // $mediaItems is passed directly from the controller (no extra query needed)
                    $media = $mediaItems ?? collect();
                    // Return conversion URL using generated_conversions metadata (no file_exists() disk hit)
                    $mediaUrl = function ($img, $conversion) {
                        if ($conversion) {
                            $generated = $img->generated_conversions ?? [];
                            if (!empty($generated[$conversion])) {
                                return '/storage/' . ltrim($img->getPathRelativeToRoot($conversion), '/');
                            }
                        }
                        return '/storage/' . ltrim($img->getPathRelativeToRoot(''), '/');
                    };
                @endphp

                <div class="pd-gallery">
                    {{-- Thumbnails column --}}
                    @if ($media->count() > 1)
                        <div class="pd-thumbs">
                            @foreach ($media as $img)
                                <button class="pd-thumb {{ $loop->first ? 'active' : '' }}"
                                    onclick="switchImg('{{ $mediaUrl($img, 'card') }}', this)">
                                    <img src="{{ $mediaUrl($img, 'thumb') }}" alt="" width="80" height="80"
                                        loading="lazy" decoding="async">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- Main image --}}
                    <div class="pd-main-img-wrap">
                        @if ($media->count() > 0)
                            <img src="{{ $mediaUrl($media->first(), 'card') }}" id="mainImg" class="pd-main-img"
                                alt="{{ $product['name'] }}" width="600" height="600" fetchpriority="high"
                                decoding="async" onclick="openImgModal(this.src)">
                        @else
                            <div class="pd-no-img">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ── Info column ── --}}
                <div class="pd-info">

                    {{-- Brand + badge --}}
                    <div class="pd-brand-row">
                        <span class="pd-brand">{{ $product['brand'] }}</span>
                        @if ($product['badge'])
                            <span class="badge badge-{{ $product['badge_color'] }}">{{ $product['badge'] }}</span>
                        @endif
                    </div>

                    <h1 class="pd-title">{{ $product['name'] }}</h1>

                    @if (!empty($product['vendor']))
                        <p class="mb-2">
                            {{ app()->getLocale() === 'ar' ? 'يُباع بواسطة:' : 'Sold by:' }}
                            <a href="{{ route('stores.show', $product['vendor']['store_slug']) }}" class="fw-semibold text-primary">
                                {{ $product['vendor']['store_name'] }}
                            </a>
                        </p>
                    @endif

                    {{-- Rating --}}
                    <div class="pd-rating">
                        @for ($i = 0; $i < 5; $i++)
                            <i class="fas fa-star {{ $i < $product['rating'] ? 'active' : '' }}"></i>
                        @endfor
                        <span>({{ $product['reviews'] ?? 0 }} {{ __('app.prod_reviews') }})</span>
                    </div>

                    {{-- Price --}}
                    <div class="pd-price-box">
                        <span class="pd-price">{{ number_format($product['price']) }} EGP</span>
                        @if ($product['old_price'])
                            <span class="pd-old-price">{{ number_format($product['old_price']) }} EGP</span>
                            <span
                                class="pd-save">{{ __('app.prod_save', ['amount' => number_format($product['old_price'] - $product['price'])]) }}</span>
                        @endif
                    </div>

                    <p class="pd-desc">{{ $product['description'] }}</p>

                    {{-- Specs table --}}
                    @if (!empty($product['specs']))
                        <div class="pd-specs">
                            <div class="pd-specs-title">
                                <i class="fas fa-list-ul"></i> {{ __('app.prod_specs_title') }}
                            </div>
                            <table class="pd-specs-table">
                                @foreach ($product['specs'] as $spec)
                                    @php
                                        $parts = explode(':', $spec, 2);
                                        $key = trim($parts[0]);
                                        $val = isset($parts[1]) ? trim($parts[1]) : '';
                                    @endphp
                                    <tr>
                                        <td class="spec-key">{{ $key }}</td>
                                        <td class="spec-val">{{ $val ?: $key }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="pd-actions">
                        <div class="qty-box">
                            <button type="button" onclick="changeQty(-1)">−</button>
                            <input type="number" id="qty" value="1" min="1" max="99"
                                onchange="syncOrderModalQty()">
                            <button type="button" onclick="changeQty(1)">+</button>
                        </div>
                        <button type="button" class="btn btn-warning btn-lg pd-buy-now-btn"
                            onclick="openQuickOrderModal()">
                            <i class="fas fa-bolt"></i>
                            {{ app()->getLocale() === 'ar' ? 'إتمام الطلب' : 'Complete Order' }}
                        </button>
                        <button type="button" class="btn btn-primary btn-lg pd-cart-btn"
                            onclick="addToCart({{ $product['id'] }}, '{{ addslashes($product['name']) }}', {{ $product['price'] }}, '{{ $product['image_url'] ?? '' }}')">
                            <i class="fas fa-shopping-cart"></i> {{ __('app.prod_add_cart_lg') }}
                        </button>
                    </div>

                    {{-- Guarantees --}}
                    <div class="pd-guarantees">
                        <div class="guar-item"><i class="fas fa-shield-alt"></i><span>{{ __('app.prod_warranty') }}</span>
                        </div>
                        <div class="guar-item"><i class="fas fa-truck"></i><span>{{ __('app.prod_delivery') }}</span></div>
                        <div class="guar-item"><i class="fas fa-undo"></i><span>{{ __('app.prod_returns') }}</span></div>
                        <div class="guar-item"><i class="fas fa-headset"></i><span>{{ __('app.prod_support') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ Related Products ═══ --}}
            @if (count($related) > 0)
                <div class="pd-related">
                    <h2 class="pd-related-title"><i class="fas fa-th-large"></i> {{ __('app.prod_related_title') }}</h2>
                    <div class="related-grid">
                        @foreach ($related as $item)
                            @php
                                $rIcon =
                                    $item['category'] === 'printers'
                                        ? 'print'
                                        : ($item['category'] === 'mice'
                                            ? 'mouse'
                                            : ($item['category'] === 'headphones'
                                                ? 'headphones'
                                                : 'usb'));
                            @endphp
                            <div class="product-card">
                                @if ($item['badge'])
                                    <span
                                        class="product-badge badge badge-{{ $item['badge_color'] }}">{{ $item['badge'] }}</span>
                                @endif
                                <div class="product-image">
                                    @if (!empty($item['image_url']))
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}"
                                            onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                        <div class="placeholder-image" style="display:none">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @else
                                        <div class="placeholder-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="product-info">
                                    <span class="product-brand">{{ $item['brand'] }}</span>
                                    <h3>{{ $item['name'] }}</h3>
                                    <div class="product-price">
                                        <span class="price-current">{{ number_format($item['price']) }} EGP</span>
                                        @if ($item['old_price'])
                                            <span class="price-old">{{ number_format($item['old_price']) }} EGP</span>
                                        @endif
                                    </div>
                                    <div class="product-actions">
                                        <button class="btn btn-primary"
                                            onclick="addToCart({{ $item['id'] }}, '{{ addslashes($item['name']) }}', {{ $item['price'] }}, '{{ $item['image_url'] ?? '' }}')">
                                            <i class="fas fa-shopping-cart"></i> {{ __('app.prod_add_cart') }}
                                        </button>
                                        <a href="{{ route('products.show', $item['id']) }}" class="btn btn-outline">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </section>

    {{-- Quick Order Modal --}}
    <div class="modal fade" id="quickOrderModal" tabindex="-1" aria-labelledby="quickOrderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                {{-- Modal Header --}}
                <div class="modal-header text-white p-4"
                    style="background: linear-gradient(135deg, #051836 0%, #1e3a8a 100%); border-bottom: none;">
                    <div class="d-flex align-items-center gap-3">
                        <div
                            style="width: 44px; height: 44px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fas fa-bolt text-warning"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-white" id="quickOrderModalLabel">
                                {{ app()->getLocale() === 'ar' ? 'اشتر الآن' : 'Quick Checkout' }}</h5>
                            <small
                                class="text-white-50">{{ app()->getLocale() === 'ar' ? 'أدخل بياناتك وسيتم تسجيل طلبك فوراً' : 'Fill your details to place order instantly' }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4 bg-white" id="quickOrderModalBody">
                    {{-- Product Mini Preview --}}
                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 mb-3"
                        style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        @if (!empty($product['image_url']))
                            <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}"
                                style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #cbd5e1;">
                        @else
                            <div
                                style="width: 60px; height: 60px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 1.5rem;">
                                <i class="fas fa-box"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: .95rem;">
                                {{ $product['name'] }}</h6>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted small">{{ app()->getLocale() === 'ar' ? 'الكمية' : 'Qty' }}:
                                    <strong id="modalQtyDisplay" class="text-primary">1</strong></span>
                                <span class="fw-bold text-primary" style="font-size: 1.1rem;"><span
                                        id="modalTotalDisplay">{{ number_format($product['price'], 2) }}</span>
                                    {{ app()->getLocale() === 'ar' ? 'جنيه' : 'EGP' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Alert for errors --}}
                    <div id="quickOrderError" class="alert alert-danger py-2 px-3 small rounded-3 d-none mb-3"></div>

                    {{-- Form --}}
                    <form id="quickOrderForm" onsubmit="submitQuickOrder(event)">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                        <input type="hidden" name="quantity" id="quickOrderQty" value="1">

                        <div class="mb-3">
                            <label
                                class="form-label fw-bold small text-dark mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم بالكامل' : 'Full Name' }}
                                <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="fas fa-user text-muted"></i></span>
                                <input type="text" name="customer_name" class="form-control rounded-start-0"
                                    value="{{ auth()->user()?->name }}" required
                                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل اسمك ثلاثي' : 'Enter your full name' }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label
                                class="form-label fw-bold small text-dark mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone Number' }}
                                <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="fas fa-phone text-muted"></i></span>
                                <input type="tel" name="customer_phone" class="form-control rounded-start-0"
                                    value="{{ auth()->user()?->phone }}" required placeholder="01xxxxxxxxx"
                                    dir="ltr" style="text-align: right;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label
                                class="form-label fw-bold small text-dark mb-1">{{ app()->getLocale() === 'ar' ? 'العنوان بالتفصيل' : 'Delivery Address' }}
                                <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="fas fa-map-marker-alt text-muted"></i></span>
                                <input type="text" name="customer_address" class="form-control rounded-start-0"
                                    value="{{ auth()->user()?->address }}" required
                                    placeholder="{{ app()->getLocale() === 'ar' ? 'المدينة، اسم الشارع، رقم المبنى والشقة' : 'City, street, building, apt...' }}">
                            </div>
                        </div>

                        {{-- <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">{{ app()->getLocale() === 'ar' ? 'المحافظة / المدينة' : 'City / Governorate' }} <span class="text-muted small">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'optional' }})</span></label>
                        <input type="text" name="city" class="form-control" value="{{ auth()->user()?->city }}" placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: القاهرة، الجيزة، الإسكندرية...' : 'e.g. Cairo, Giza, Alexandria...' }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">{{ app()->getLocale() === 'ar' ? 'ملاحظات إضافية' : 'Order Notes' }} <span class="text-muted small">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'optional' }})</span></label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="{{ app()->getLocale() === 'ar' ? 'أي تعليمات خاصة بالطلب أو موعد التسليم...' : 'Special instructions...' }}"></textarea>
                    </div> --}}

                        {{-- Payment badge --}}
                        <div class="d-flex align-items-center gap-2 p-2 px-3 rounded-3 mb-4"
                            style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; font-size: .875rem;">
                            <i class="fas fa-money-bill-wave text-success fs-5"></i>
                            <span
                                class="fw-semibold">{{ app()->getLocale() === 'ar' ? 'الدفع نقدًا عند الاستلام (Cash On Delivery)' : 'Cash on Delivery' }}</span>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" id="quickOrderSubmitBtn"
                                class="btn btn-primary btn-lg fw-bold rounded-3 shadow-sm py-3">
                                <i class="fas fa-check-circle me-1"></i>
                                {{ app()->getLocale() === 'ar' ? 'تأكيد وإتمام الطلب' : 'Confirm Order Now' }}
                            </button>
                        </div>
                    </form>

                    {{-- Success State --}}
                    <div id="quickOrderSuccessState" class="d-none text-center py-4">
                        <div
                            style="width: 76px; height: 76px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.25rem;">
                            <i class="fas fa-check"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">
                            {{ app()->getLocale() === 'ar' ? 'تم تسجيل طلبك بنجاح!' : 'Order Placed Successfully!' }}</h4>
                        <p class="text-muted mb-3" style="font-size: .95rem;">
                            {{ app()->getLocale() === 'ar' ? 'رقم الطلب الخاص بك هو:' : 'Your Order Number is:' }}
                        </p>
                        <div class="p-3 bg-light rounded-3 border mb-3 d-inline-block px-4">
                            <strong class="text-primary fs-5" id="successOrderNumber">#</strong>
                        </div>
                        <p class="text-muted small mb-4">
                            {{ app()->getLocale() === 'ar' ? 'سيقوم فريق خدمة العملاء بالتواصل معك هاتفياً لتأكيد تفاصيل الشحن والتوصيل.' : 'Our customer support will contact you shortly to confirm delivery details.' }}
                        </p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('track-order') }}"
                                class="btn btn-outline-primary px-4 rounded-3 fw-semibold">
                                <i class="fas fa-search-location me-1"></i>
                                {{ app()->getLocale() === 'ar' ? 'تتبع طلبك' : 'Track Order' }}
                            </a>
                            <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">
                                {{ app()->getLocale() === 'ar' ? 'إغلاق' : 'Close' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Image Modal Overlay --}}
    <div id="imageModal" class="img-modal" onclick="closeImgModal()">
        <span class="img-modal-close" onclick="closeImgModal()">&times;</span>
        <img class="img-modal-content" id="imgModalSrc">
    </div>

@endsection

@push('styles')
    <style>
        /* ═══════════════════════════════════════
       PRODUCT DETAIL PAGE
    ═══════════════════════════════════════ */
        .pd-layout {
            display: grid;
            grid-template-columns: 520px 1fr;
            gap: 3.5rem;
            align-items: start;
            margin-bottom: 4rem;
        }

        .pd-gallery {
            display: flex;
            gap: .875rem;
            align-items: flex-start;
        }

        .pd-thumbs {
            display: flex;
            flex-direction: column;
            gap: .625rem;
            flex-shrink: 0;
        }

        .pd-thumb {
            width: 72px;
            height: 72px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #E2E8F0;
            cursor: pointer;
            transition: all .2s ease;
            padding: 0;
            background: #fff;
            flex-shrink: 0;
        }

        .pd-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pd-thumb:hover,
        .pd-thumb.active {
            border-color: #051836;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .15);
        }

        .pd-main-img-wrap {
            flex: 1;
            background: #fff;
            border-radius: 16px;
            border: 1px solid #E2E8F0;
            overflow: hidden;
            height: 420px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 16px rgba(0, 0, 0, .07);
        }

        .pd-main-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform .4s ease;
            padding: 1rem;
        }

        .pd-main-img-wrap:hover .pd-main-img {
            transform: scale(1.04);
        }

        .pd-main-img {
            cursor: pointer;
        }

        .pd-no-img {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            font-size: 7rem;
            color: #CBD5E1;
        }

        .pd-brand-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: .875rem;
        }

        .pd-brand {
            display: inline-block;
            background: #e8edf5;
            color: #051836;
            padding: .35rem 1rem;
            border-radius: 50px;
            font-size: .8125rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .pd-title {
            font-size: 1.625rem;
            font-weight: 800;
            color: #0F172A;
            margin-bottom: .75rem;
            line-height: 1.3;
        }

        .pd-rating {
            display: flex;
            align-items: center;
            gap: .25rem;
            margin-bottom: 1.25rem;
        }

        .pd-rating i {
            color: #D1D5DB;
            font-size: .875rem;
        }

        .pd-rating i.active {
            color: #F59E0B;
        }

        .pd-rating span {
            color: #64748B;
            font-size: .875rem;
            margin-right: .375rem;
        }

        .pd-price-box {
            display: flex;
            align-items: center;
            gap: .875rem;
            flex-wrap: wrap;
            padding: 1rem 1.25rem;
            background: #F8FAFC;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            margin-bottom: 1.25rem;
        }

        .pd-price {
            font-size: 2rem;
            font-weight: 900;
            color: #051836;
        }

        .pd-old-price {
            font-size: 1.125rem;
            color: #94A3B8;
            text-decoration: line-through;
        }

        .pd-save {
            background: #FEE2E2;
            color: #DC2626;
            padding: .3rem .75rem;
            border-radius: 50px;
            font-size: .8125rem;
            font-weight: 700;
        }

        .pd-desc {
            font-size: .9375rem;
            color: #475569;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #F1F5F9;
        }

        .pd-specs {
            margin-bottom: 1.75rem;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            overflow: hidden;
        }

        .pd-specs-title {
            background: #030f1f;
            color: #fff;
            padding: .625rem 1rem;
            font-size: .875rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .pd-specs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pd-specs-table tr:nth-child(even) {
            background: #F8FAFC;
        }

        .pd-specs-table tr:nth-child(odd) {
            background: #fff;
        }

        .pd-specs-table tr:hover {
            background: #e8edf5;
        }

        .spec-key {
            padding: .6rem 1rem;
            font-size: .875rem;
            font-weight: 700;
            color: #334155;
            width: 40%;
            border-left: 1px solid #E2E8F0;
            border-bottom: 1px solid #F1F5F9;
        }

        .spec-val {
            padding: .6rem 1rem;
            font-size: .875rem;
            color: #475569;
            border-bottom: 1px solid #F1F5F9;
        }

        .pd-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .qty-box {
            display: flex;
            align-items: center;
            border: 2px solid #E2E8F0;
            border-radius: 50px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .qty-box button {
            width: 42px;
            height: 46px;
            background: #F1F5F9;
            color: #334155;
            font-size: 1.25rem;
            font-weight: 700;
            transition: all .2s ease;
            border: none;
        }

        .qty-box button:hover {
            background: #051836;
            color: #fff;
        }

        .qty-box input {
            width: 52px;
            height: 46px;
            text-align: center;
            border: none;
            font-size: 1.0625rem;
            font-weight: 700;
            color: #0F172A;
        }

        .qty-box input:focus {
            outline: none;
        }

        .pd-buy-now-btn {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: #fff !important;
            font-weight: 800;
            padding: .75rem 1.5rem;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
            transition: all .25s ease;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            justify-content: center;
            flex: 1;
            min-width: 170px;
            border-radius: 12px;
        }

        .pd-buy-now-btn:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.45);
            color: #fff !important;
        }

        .pd-cart-btn {
            flex: 1;
            justify-content: center;
            min-width: 170px;
            border-radius: 12px;
        }

        .pd-guarantees {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .75rem;
        }

        .guar-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .375rem;
            padding: .875rem .5rem;
            background: #e8edf5;
            border-radius: 12px;
            color: #051836;
            font-size: .8125rem;
            font-weight: 600;
            text-align: center;
        }

        .guar-item i {
            font-size: 1.375rem;
        }

        .pd-related {
            border-top: 2px solid #F1F5F9;
            padding-top: 3rem;
        }

        .pd-related-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            gap: .625rem;
            color: #0F172A;
        }

        .pd-related-title i {
            color: #051836;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
        }

        .product-card {
            position: relative;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
            transition: all .3s ease;
            border: 1px solid #F1F5F9;
        }

        .product-card:hover {
            box-shadow: 0 12px 36px rgba(0, 0, 0, .12);
            transform: translateY(-5px);
        }

        .product-badge {
            position: absolute;
            top: .75rem;
            right: .75rem;
            z-index: 2;
        }

        .product-image {
            height: 180px;
            background: #F8FAFC;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .4s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.06);
        }

        .placeholder-image {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            color: #CBD5E1;
        }

        .product-info {
            padding: 1.125rem;
        }

        .product-brand {
            font-size: .75rem;
            font-weight: 700;
            color: #051836;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: .375rem;
            display: block;
        }

        .product-info h3 {
            font-size: .9375rem;
            margin-bottom: .75rem;
            color: #0F172A;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .875rem;
        }

        .price-current {
            font-size: 1.125rem;
            font-weight: 800;
            color: #051836;
        }

        .price-old {
            font-size: .8125rem;
            color: #94A3B8;
            text-decoration: line-through;
        }

        .product-actions {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: .5rem;
        }

        .product-actions .btn {
            font-size: .8125rem;
            padding: .5rem .875rem;
        }

        @media (max-width: 1100px) {
            .pd-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .pd-gallery {
                max-width: 520px;
            }

            .pd-guarantees {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .pd-main-img-wrap {
                height: 300px;
            }

            .pd-thumb {
                width: 58px;
                height: 58px;
            }

            .pd-actions {
                flex-direction: column;
            }

            .pd-buy-now-btn,
            .pd-cart-btn {
                width: 100%;
            }

            .related-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .related-grid {
                grid-template-columns: 1fr;
            }

            .pd-guarantees {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Modal for Image */
        .img-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
        }

        .img-modal-content {
            max-width: 90%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
            animation: zoomIn 0.3s ease;
        }

        .img-modal-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }

        .img-modal-close:hover {
            color: #ccc;
        }

        @keyframes zoomIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        const productUnitPrice = {{ (float) $product['price'] }};

        function switchImg(url, btn) {
            const img = document.getElementById('mainImg');
            if (img) img.src = url;
            document.querySelectorAll('.pd-thumb').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
        }

        function changeQty(delta) {
            const input = document.getElementById('qty');
            input.value = Math.max(1, parseInt(input.value || 1) + delta);
            syncOrderModalQty();
        }

        function syncOrderModalQty() {
            const qtyInput = document.getElementById('qty');
            const modalQty = document.getElementById('quickOrderQty');
            const modalQtyDisplay = document.getElementById('modalQtyDisplay');
            const modalTotalDisplay = document.getElementById('modalTotalDisplay');

            const qty = Math.max(1, parseInt(qtyInput ? qtyInput.value : 1) || 1);
            if (modalQty) modalQty.value = qty;
            if (modalQtyDisplay) modalQtyDisplay.textContent = qty;
            if (modalTotalDisplay) {
                const total = (productUnitPrice * qty).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                modalTotalDisplay.textContent = total;
            }
        }

        function openQuickOrderModal() {
            syncOrderModalQty();
            const err = document.getElementById('quickOrderError');
            if (err) {
                err.classList.add('d-none');
                err.textContent = '';
            }

            const form = document.getElementById('quickOrderForm');
            const successState = document.getElementById('quickOrderSuccessState');
            if (form) form.classList.remove('d-none');
            if (successState) successState.classList.add('d-none');

            const modalEl = document.getElementById('quickOrderModal');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else {
                $(modalEl).modal('show');
            }
        }

        function submitQuickOrder(e) {
            e.preventDefault();
            const form = document.getElementById('quickOrderForm');
            const btn = document.getElementById('quickOrderSubmitBtn');
            const errBox = document.getElementById('quickOrderError');
            const successState = document.getElementById('quickOrderSuccessState');
            const successOrderNumber = document.getElementById('successOrderNumber');

            errBox.classList.add('d-none');
            errBox.innerHTML = '';

            const origBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ' + (document
                    .dir === 'rtl' ? 'جارٍ تسجيل الطلب...' : 'Processing Order...');

            const formData = new FormData(form);

            fetch('{{ route('orders.quick') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json().then(data => ({
                    status: res.status,
                    data
                })))
                .then(({
                        status,
                        data
                    }) => {
                        btn.disabled = false;
                        btn.innerHTML = origBtnHtml;

                        if (status === 200 && data.success) {
                            form.classList.add('d-none');
                            successState.classList.remove('d-none');
                            if (successOrderNumber) successOrderNumber.textContent = data.order_number;

                            // Store order number in cookie for guest tracking (client-side)
                            @guest
                            try {
                                const cookieName = 'guest_orders';
                                const existing = JSON.parse(decodeURIComponent(
                                    document.cookie.split(';').find(c => c.trim().startsWith(cookieName + '='))
                                    ?.split('=').slice(1).join('=') || '[]'
                                ) || '[]');
                                if (!existing.includes(data.order_number)) {
                                    existing.push(data.order_number);
                                    // Keep last 10, expire in 90 days
                                    const keep = existing.slice(-10);
                                    const expires = new Date(Date.now() + 90 * 24 * 60 * 60 * 1000).toUTCString();
                                    document.cookie =
                                        `${cookieName}=${encodeURIComponent(JSON.stringify(keep))};expires=${expires};path=/;SameSite=Lax`;
                                }
                            } catch (e) {}
                        @endguest
                    } else {
                        let msg = data.message || (document.dir === 'rtl' ? 'حدث خطأ، يرجى المحاولة مرة أخرى' :
                            'An error occurred, please try again.');
                        if (data.errors) {
                            msg = Object.values(data.errors).flat().join('<br>');
                        }
                        errBox.innerHTML = msg;
                        errBox.classList.remove('d-none');
                    }
                })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origBtnHtml;
            errBox.textContent = document.dir === 'rtl' ? 'فشل الاتصال بالخادم، يرجى التحقق من اتصالك' :
                'Server error, please check connection.';
            errBox.classList.remove('d-none');
        });
        }

        function openImgModal(src) {
            document.getElementById('imageModal').style.display = "flex";
            document.getElementById('imgModalSrc').src = src;
        }

        function closeImgModal() {
            document.getElementById('imageModal').style.display = "none";
        }
    </script>
@endpush
