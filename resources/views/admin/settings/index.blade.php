@extends('admin.layouts.app')

@section('title', 'إعدادات الصفحة الرئيسية')
@section('page-title', 'إعدادات الصفحة الرئيسية')
@section('breadcrumb')
    <i class="fas fa-chevron-left"></i>
    <span>إعدادات الصفحة الرئيسية</span>
@endsection

@section('content')
<div class="settings-container">

    <!-- Header Banner -->
    <div class="settings-hero-card">
        <div class="hero-content">
            <div class="hero-icon">
                <i class="fas fa-sliders-h"></i>
            </div>
            <div>
                <h2>تخصيص وإعدادات الصفحة الرئيسية</h2>
                <p>تحكم في الأقسام الظاهرة لزوار متجرك، وإدارة عرض اليوم المميز، وربط وتحديث المنتجات عبر الـ API الخارجي.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" id="settingsForm">
        @csrf
        <input type="hidden" name="submitted" value="1">

        <!-- ════════════════════════════════════════════════════════════
             SECTION 1: Home Page Sections Toggles
        ════════════════════════════════════════════════════════════ -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>
                    <i class="fas fa-layer-group" style="color:var(--primary)"></i>
                    أقسام الصفحة الرئيسية (Sections Visibility)
                </h2>
                <span class="header-hint">تفعيل أو إخفاء أقسام الواجهة الرئيسية للمتجر</span>
            </div>
            <div class="card-body">
                <div class="toggles-grid">

                    <div class="toggle-card">
                        <div class="toggle-icon bg-blue">
                            <i class="fas fa-images"></i>
                        </div>
                        <div class="toggle-info">
                            <div class="toggle-title">السلايدر الرئيسي</div>
                            <div class="toggle-desc">إظهار أو إخفاء صور السلايدر في الصفحة الرئيسية</div>
                        </div>
                        <div class="toggle-action">
                            <label class="switch-toggle" title="تفعيل/تعطيل">
                                <input type="checkbox" id="home_show_slider" name="home_show_slider" value="1" {{ ($settings['home_show_slider'] ?? '1') == '1' ? 'checked' : '' }}>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Shop by Brand -->
                    <div class="toggle-card">
                        <div class="toggle-icon bg-blue">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="toggle-info">
                            <div class="toggle-title">تسوق حسب الماركة</div>
                            <div class="toggle-desc">عرض شعارات العلامات التجارية المميزة (Shop by Brand)</div>
                        </div>
                        <div class="toggle-action">
                            <label class="switch-toggle" title="تفعيل/تعطيل">
                                <input type="checkbox" id="home_show_brands" name="home_show_brands" value="1" {{ ($settings['home_show_brands'] ?? '0') == '1' ? 'checked' : '' }}>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Recommended for You -->
                    <div class="toggle-card">
                        <div class="toggle-icon bg-purple">
                            <i class="fas fa-thumbs-up"></i>
                        </div>
                        <div class="toggle-info">
                            <div class="toggle-title">المقترحة لك</div>
                            <div class="toggle-desc">عرض منتجات مختارة للمستخدم (Recommended for You)</div>
                        </div>
                        <div class="toggle-action">
                            <label class="switch-toggle" title="تفعيل/تعطيل">
                                <input type="checkbox" id="home_show_recommended" name="home_show_recommended" value="1" {{ ($settings['home_show_recommended'] ?? '0') == '1' ? 'checked' : '' }}>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Newsletter Subscription -->
                    <div class="toggle-card">
                        <div class="toggle-icon bg-green">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                        <div class="toggle-info">
                            <div class="toggle-title">النشرة البريدية</div>
                            <div class="toggle-desc">صندوق اشتراك الزوار في العروض والجديد (Newsletter)</div>
                        </div>
                        <div class="toggle-action">
                            <label class="switch-toggle" title="تفعيل/تعطيل">
                                <input type="checkbox" id="home_show_newsletter" name="home_show_newsletter" value="1" {{ ($settings['home_show_newsletter'] ?? '0') == '1' ? 'checked' : '' }}>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- FAQ Section -->
                    <div class="toggle-card">
                        <div class="toggle-icon bg-orange">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="toggle-info">
                            <div class="toggle-title">الأسئلة الشائعة</div>
                            <div class="toggle-desc">عرض قائمة الأسئلة الأكثر تكراراً وإجاباتها (FAQ)</div>
                        </div>
                        <div class="toggle-action">
                            <label class="switch-toggle" title="تفعيل/تعطيل">
                                <input type="checkbox" id="home_show_faq" name="home_show_faq" value="1" {{ ($settings['home_show_faq'] ?? '0') == '1' ? 'checked' : '' }}>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Track Your Order -->
                    <div class="toggle-card">
                        <div class="toggle-icon bg-teal">
                            <i class="fas fa-truck-fast"></i>
                        </div>
                        <div class="toggle-info">
                            <div class="toggle-title">تتبع طلبك</div>
                            <div class="toggle-desc">قسم الاستعلام السريع عن حالة الشحنة بالرقم (Track Order)</div>
                        </div>
                        <div class="toggle-action">
                            <label class="switch-toggle" title="تفعيل/تعطيل">
                                <input type="checkbox" id="home_show_track_order" name="home_show_track_order" value="1" {{ ($settings['home_show_track_order'] ?? '0') == '1' ? 'checked' : '' }}>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════
             SECTION 2: Deal of the Day Section
        ════════════════════════════════════════════════════════════ -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>
                    <i class="fas fa-bolt" style="color:#F59E0B"></i>
                    عرض اليوم المميز (Deal of the Day)
                </h2>
                <div class="header-toggle">
                    <span class="toggle-label">تفعيل القسم:</span>
                    <label class="switch-toggle" title="تفعيل عرض اليوم">
                        <input type="checkbox" id="home_show_deal" name="home_show_deal" value="1" {{ ($settings['home_show_deal'] ?? '0') == '1' ? 'checked' : '' }}>
                        <span class="switch-slider"></span>
                    </label>
                </div>
            </div>
            <div class="card-body">
                <div class="deal-config-grid">
                    <div class="form-group">
                        <label class="form-label" for="home_deal_product_id">
                            <i class="fas fa-box-open text-muted me-1"></i> المنتج المعروض في الصفقة
                        </label>
                        <select name="home_deal_product_id" id="home_deal_product_id" class="form-control">
                            <option value="">-- اختر منتجاً لعرض اليوم --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ ($settings['home_deal_product_id'] ?? '') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ number_format($product->price, 2) }} ج.م)
                                </option>
                            @endforeach
                        </select>
                        <small class="form-hint">اختر المنتج الذي سيظهر في بنر العرض الترويجي اليومي.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="home_deal_end_time">
                            <i class="fas fa-clock text-muted me-1"></i> موعد انتهاء العرض (العد التنازلي)
                        </label>
                        <input type="datetime-local" 
                               id="home_deal_end_time" 
                               name="home_deal_end_time" 
                               class="form-control" 
                               value="{{ $settings['home_deal_end_time'] ?? '' }}">
                        <small class="form-hint">سيظهر مؤقت عد تنازلي للزوار ينتهي في هذا التوقيت.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════
             SECTION 3: External Products API Integration
        ════════════════════════════════════════════════════════════ -->
        <div class="card mb-4" id="api-settings">
            <div class="card-header api-card-header">
                <div>
                    <h2>
                        <i class="fas fa-cloud-arrow-down" style="color:var(--primary)"></i>
                        ربط المنتجات من API خارجي (External Products API)
                    </h2>
                    <p class="api-header-subtitle">اربط متجرك مع نظام ERP أو API خارجي لجلب المنتجات وتحديثها تلقائياً.</p>
                </div>
                <span class="badge badge-blue">REST API</span>
            </div>
            <div class="card-body">
                <div class="api-inputs-grid">
                    <div class="form-group flex-2">
                        <label class="form-label" for="products_api_url">
                            رابط الـ API (Products Endpoint URL) <span style="color:var(--red)">*</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="fas fa-link input-icon"></i>
                            <input type="url" 
                                   id="products_api_url" 
                                   name="products_api_url" 
                                   class="form-control" 
                                   placeholder="https://example.com/api/products" 
                                   value="{{ $settings['products_api_url'] ?? '' }}">
                        </div>
                        <small class="form-hint">يدعم صيغ JSON المختلفة، ويقوم بمطابقة حقول (الاسم، السعر، الصور، المخزون) تلقائياً.</small>
                    </div>

                    <div class="form-group flex-1">
                        <label class="form-label" for="products_api_token">
                            مفتاح الـ API / Bearer Token <span class="text-muted fw-normal">(اختياري)</span>
                        </label>
                        <div class="input-with-icon">
                            <i class="fas fa-key input-icon"></i>
                            <input type="password" 
                                   id="products_api_token" 
                                   name="products_api_token" 
                                   class="form-control" 
                                   placeholder="Bearer Token إن وُجد" 
                                   value="{{ $settings['products_api_token'] ?? '' }}">
                        </div>
                        <small class="form-hint">يُرسل في الـ Authorization Header للتوثيق مع الـ API.</small>
                    </div>
                </div>

                <!-- Auto-Sync Info & Live Action Box -->
                <div class="api-sync-box">
                    <div class="sync-status-col">
                        <div id="syncResultStatus" class="sync-status-msg">
                            @if(!empty($settings['products_api_last_synced_at']))
                                <span style="color:#10b981;font-weight:600;"><i class="fas fa-check-circle me-1"></i> التحديث التلقائي نشط: آخر مزامنة تمت في {{ $settings['products_api_last_synced_at'] }} (يتم التحديث تلقائياً دورياً وعند حفظ الإعدادات).</span>
                            @elseif(!empty($settings['products_api_url']))
                                <span style="color:#3b82f6;font-weight:600;"><i class="fas fa-bolt me-1"></i> التحديث التلقائي مفعّل: يتم تحديث ومزامنة المنتجات فوراً عند الحفظ وبشكل دوري تلقائياً.</span>
                            @else
                                <span class="text-muted"><i class="fas fa-info-circle me-1"></i> بمجرد إدخال الرابط، سيتم تحديث المنتجات تلقائياً ومزامنتها دورياً دائماً.</span>
                            @endif
                        </div>
                    </div>
                    <div class="sync-action-col">
                        <button type="button" id="btnSyncApiNow" class="btn btn-secondary sync-btn">
                            <i class="fas fa-sync-alt" id="syncIcon"></i> 
                            <span id="syncBtnText">مزامنة وجلب المنتجات الآن</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Save Bar -->
        <div class="settings-footer-bar">
            <div class="footer-info">
                <i class="fas fa-check-double text-muted"></i>
                <span>تأكد من مراجعة التغييرات ثم اضغط حفظ لتطبيقها على المتجر مباشرة</span>
            </div>
            <div class="footer-actions">
                <button type="submit" class="btn btn-primary save-btn">
                    <i class="fas fa-save"></i>
                    <span>حفظ جميع الإعدادات</span>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@push('styles')
<style>
/* ── Settings Container ── */
.settings-container {
    max-width: 1100px;
    margin: 0 auto;
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

/* ── Hero Banner ── */
.settings-hero-card {
    background: linear-gradient(135deg, #051836 0%, #0a2e5c 100%);
    border-radius: var(--r-xl, 20px);
    padding: 1.75rem 2rem;
    color: #fff;
    margin-bottom: 1.75rem;
    box-shadow: 0 10px 25px rgba(5, 24, 54, 0.15);
}

.hero-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.hero-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    color: #fff;
    flex-shrink: 0;
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.hero-content h2 {
    font-size: 1.35rem;
    font-weight: 800;
    margin: 0 0 0.35rem 0;
    color: #fff;
}

.hero-content p {
    font-size: 0.925rem;
    color: rgba(255, 255, 255, 0.8);
    margin: 0;
    line-height: 1.5;
}

/* ── Card Header Refinements ── */
.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.header-hint {
    font-size: 0.8125rem;
    color: var(--ink-500, #64748B);
    font-weight: 500;
}

.header-toggle {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.header-toggle .toggle-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--ink-700, #334155);
}

/* ── Toggles Grid ── */
.toggles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1rem;
}

.toggle-card {
    background: #fff;
    border: 1.5px solid var(--ink-200, #E2E8F0);
    border-radius: var(--r-lg, 16px);
    padding: 1.125rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.25s ease;
}

.toggle-card:hover {
    border-color: #051836;
    box-shadow: var(--sh, 0 2px 8px rgba(0,0,0,0.08));
    transform: translateY(-2px);
}

.toggle-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.toggle-icon.bg-blue { background: #e8edf5; color: #051836; }
.toggle-icon.bg-purple { background: #EDE9FE; color: #7C3AED; }
.toggle-icon.bg-green { background: #D1FAE5; color: #059669; }
.toggle-icon.bg-orange { background: #FEF3C7; color: #D97706; }
.toggle-icon.bg-teal { background: #CCFBF1; color: #0D9488; }

.toggle-info {
    flex: 1;
    min-width: 0;
}

.toggle-title {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--ink-900, #0F172A);
    margin-bottom: 0.2rem;
}

.toggle-desc {
    font-size: 0.8125rem;
    color: var(--ink-500, #64748B);
    line-height: 1.35;
}

.toggle-action {
    flex-shrink: 0;
}

/* ── Modern Custom Switch ── */
.switch-toggle {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    cursor: pointer;
    margin: 0;
}

.switch-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}

.switch-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #CBD5E1;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 34px;
}

.switch-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.switch-toggle input:checked + .switch-slider {
    background-color: #10B981;
}

.switch-toggle input:checked + .switch-slider:before {
    transform: translateX(22px);
}

/* ── Deal of the Day Section ── */
.deal-config-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-hint {
    display: block;
    font-size: 0.8125rem;
    color: var(--ink-400, #94A3B8);
    margin-top: 0.375rem;
}

/* ── API Section ── */
.api-card-header {
    background: #F8FAFC;
}

.api-header-subtitle {
    font-size: 0.875rem;
    color: var(--ink-500, #64748B);
    margin: 0.25rem 0 0 0;
}

.api-inputs-grid {
    display: flex;
    gap: 1.25rem;
}

.api-inputs-grid .flex-2 { flex: 2; }
.api-inputs-grid .flex-1 { flex: 1; }

.input-with-icon {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    right: 1rem;
    color: var(--ink-400, #94A3B8);
    pointer-events: none;
    font-size: 0.95rem;
}

.input-with-icon .form-control {
    padding-right: 2.75rem;
}

/* In LTR mode adjust icon position */
[dir="ltr"] .input-icon {
    right: auto;
    left: 1rem;
}
[dir="ltr"] .input-with-icon .form-control {
    padding-right: 1.125rem;
    padding-left: 2.75rem;
}

/* ── API Sync Box ── */
.api-sync-box {
    margin-top: 1.25rem;
    padding: 1.125rem 1.25rem;
    background: #F8FAFC;
    border: 1px dashed var(--ink-200, #CBD5E1);
    border-radius: var(--r-md, 12px);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.sync-status-msg {
    font-size: 0.875rem;
    font-weight: 500;
}

.sync-btn {
    background: #fff;
    border: 1.5px solid var(--ink-200, #E2E8F0);
    color: var(--primary, #051836);
    padding: 0.65rem 1.25rem;
    font-weight: 700;
    transition: all 0.25s ease;
}

.sync-btn:hover {
    background: #e8edf5;
    border-color: #051836;
    color: #051836;
    transform: translateY(-1px);
}

/* ── Sticky Save Footer Bar ── */
.settings-footer-bar {
    background: #fff;
    border: 1px solid var(--ink-200, #E2E8F0);
    border-radius: var(--r-lg, 16px);
    padding: 1.125rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    margin-top: 2rem;
    margin-bottom: 2rem;
}

.footer-info {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    font-size: 0.875rem;
    color: var(--ink-500, #64748B);
    font-weight: 500;
}

.save-btn {
    padding: 0.8rem 2rem;
    font-size: 1rem;
    font-weight: 700;
    border-radius: var(--r-md, 12px);
    box-shadow: 0 4px 15px rgba(5, 24, 54, 0.25);
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
}

.save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(5, 24, 54, 0.35);
}

/* ── Responsive ── */
@media (max-width: 900px) {
    .deal-config-grid {
        grid-template-columns: 1fr;
    }
    .api-inputs-grid {
        flex-direction: column;
    }
    .settings-hero-card {
        padding: 1.25rem 1.5rem;
    }
    .hero-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    .toggles-grid {
        grid-template-columns: 1fr;
    }
    .settings-footer-bar {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
        text-align: center;
    }
    .save-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnSync = document.getElementById('btnSyncApiNow');
    const syncIcon = document.getElementById('syncIcon');
    const syncBtnText = document.getElementById('syncBtnText');
    const statusDiv = document.getElementById('syncResultStatus');
    const urlInput = document.getElementById('products_api_url');
    const tokenInput = document.getElementById('products_api_token');

    if (btnSync) {
        btnSync.addEventListener('click', function () {
            const apiUrl = urlInput.value.trim();
            if (!apiUrl) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'تنبيه',
                        text: 'يرجى إدخال رابط الـ API أولاً.',
                        confirmButtonColor: '#051836'
                    });
                } else {
                    alert('يرجى إدخال رابط الـ API أولاً.');
                }
                urlInput.focus();
                return;
            }

            // UI loading state
            btnSync.disabled = true;
            syncIcon.classList.add('fa-spin');
            syncBtnText.innerText = 'جاري جلب المنتجات والمزامنة...';
            statusDiv.innerHTML = '<span style="color:var(--primary, #051836)"><i class="fas fa-spinner fa-spin me-1"></i> جاري الاتصال بالـ API وجلب المنتجات...</span>';

            fetch("{{ route('admin.settings.sync_api') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    products_api_url: apiUrl,
                    products_api_token: tokenInput.value.trim()
                })
            })
            .then(res => res.json())
            .then(data => {
                btnSync.disabled = false;
                syncIcon.classList.remove('fa-spin');
                syncBtnText.innerText = 'مزامنة وجلب المنتجات الآن';

                if (data.success) {
                    statusDiv.innerHTML = `<span style="color:#10B981;font-weight:600"><i class="fas fa-check-circle me-1"></i> ${data.message}</span>`;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'تمت المزامنة بنجاح!',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                } else {
                    statusDiv.innerHTML = `<span style="color:#EF4444;font-weight:600"><i class="fas fa-exclamation-triangle me-1"></i> ${data.message}</span>`;
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'فشلت المزامنة',
                            text: data.message,
                            confirmButtonColor: '#051836'
                        });
                    }
                }
            })
            .catch(err => {
                btnSync.disabled = false;
                syncIcon.classList.remove('fa-spin');
                syncBtnText.innerText = 'مزامنة وجلب المنتجات الآن';
                statusDiv.innerHTML = `<span style="color:#EF4444;font-weight:600"><i class="fas fa-times-circle me-1"></i> حدث خطأ أثناء الاتصال بالخادم.</span>`;
            });
        });
    }
});
</script>
@endpush
