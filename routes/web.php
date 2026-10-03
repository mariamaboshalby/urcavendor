<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\AboutController as AdminAboutController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SliderController as AdminSliderController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\DownloadController as AdminDownloadController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VendorRegisterController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\ProductController as VendorProductController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\EarningsController as VendorEarningsController;
use App\Http\Controllers\Vendor\WithdrawalController as VendorWithdrawalController;
use App\Http\Controllers\Vendor\StoreController as VendorStoreController;
use App\Http\Controllers\Vendor\SettingsController as VendorSettingsController;
use App\Http\Controllers\Vendor\ReviewController as VendorReviewController;
use App\Http\Controllers\Vendor\NotificationController as VendorNotificationController;
use App\Http\Controllers\Admin\VendorController as AdminVendorController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Admin\FinanceController as AdminFinanceController;


use App\Http\Controllers\WishlistController;

// ── Auth ──────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Checkout (auth required) ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/checkout',                          [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout',                         [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{orderNumber}',    [CheckoutController::class, 'success'])->name('checkout.success');
});

// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Language Switch
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// Products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{category}', [ProductController::class, 'category'])->name('products.category');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('products.show');

// News
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{id}', [NewsController::class, 'show'])->name('news.show');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{id}', [BlogController::class, 'show'])->name('blog.show');

// About
Route::get('/about', [AboutController::class, 'index'])->name('about.index');

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/items', [CartController::class, 'items'])->name('cart.items');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Testimonials
Route::post('/testimonials', [TestimonialController::class, 'store'])->name('testimonials.store');

// Newsletter
Route::post('/newsletter/subscribe', [SubscriberController::class, 'store'])->name('newsletter.subscribe');

// ── Wishlist ──────────────────────────────────────────────────────────────
// Sync: works for guests too (after login, JS calls this)
Route::post('/wishlist/sync', [WishlistController::class, 'sync'])->name('wishlist.sync')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/wishlist',                    [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle/{product}',  [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{product}',       [WishlistController::class, 'destroy'])->name('wishlist.destroy');
});

// Track Order
Route::get('/track-order', [OrderController::class, 'track'])->name('track-order');
Route::post('/track-order', [OrderController::class, 'trackStatus'])->name('track-order.status');
Route::get('/orders/{order}/detail', [OrderController::class, 'detail'])->name('orders.detail')->middleware('auth');
Route::post('/orders/quick', [OrderController::class, 'quickOrder'])->name('orders.quick');

Route::get('/become-vendor', [VendorRegisterController::class, 'create'])->name('vendor.register');
Route::post('/become-vendor', [VendorRegisterController::class, 'store'])->name('vendor.register.store');

Route::get('/stores', [StorefrontController::class, 'index'])->name('stores.index');
Route::get('/store/{slug}', [StorefrontController::class, 'show'])->name('stores.show');
Route::post('/product/{product}/reviews', [StorefrontController::class, 'storeReview'])->name('products.reviews.store');

Route::prefix('vendor')->name('vendor.')->middleware(['auth', 'vendor'])->group(function () {
    Route::get('pending', [VendorDashboardController::class, 'pending'])->name('pending');
    Route::get('notifications', [VendorNotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [VendorNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('notifications/{id}', [VendorNotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('store', [VendorStoreController::class, 'edit'])->name('store.edit');
    Route::put('store', [VendorStoreController::class, 'update'])->name('store.update');

    Route::middleware('vendor.approved')->group(function () {
        Route::get('dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
        Route::resource('products', VendorProductController::class)->except(['show']);
        Route::get('orders', [VendorOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/items/{item}/status', [VendorOrderController::class, 'updateItemStatus'])->name('orders.items.status');
        Route::get('earnings', [VendorEarningsController::class, 'index'])->name('earnings');
        Route::get('withdrawals', [VendorWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('withdrawals', [VendorWithdrawalController::class, 'store'])->name('withdrawals.store');
        Route::get('reviews', [VendorReviewController::class, 'index'])->name('reviews.index');
        Route::get('settings', [VendorSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [VendorSettingsController::class, 'update'])->name('settings.update');
    });
});

// ============================================================
// Admin Dashboard (URL prefix: /urcaAdm)
// ============================================================
Route::prefix('urcaAdm')->name('admin.')->middleware('admin')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Products
    Route::resource('products', AdminProductController::class);
    Route::post('products/{product}/approve', [AdminProductController::class, 'approve'])->name('products.approve');
    Route::post('products/{product}/reject', [AdminProductController::class, 'reject'])->name('products.reject');
    Route::post('products/{product}/disable', [AdminProductController::class, 'disable'])->name('products.disable');
    Route::resource('categories', AdminCategoryController::class);

    Route::get('vendors', [AdminVendorController::class, 'index'])->name('vendors.index');
    Route::get('vendors/{vendor}', [AdminVendorController::class, 'show'])->name('vendors.show');
    Route::post('vendors/{vendor}/approve', [AdminVendorController::class, 'approve'])->name('vendors.approve');
    Route::post('vendors/{vendor}/reject', [AdminVendorController::class, 'reject'])->name('vendors.reject');
    Route::post('vendors/{vendor}/suspend', [AdminVendorController::class, 'suspend'])->name('vendors.suspend');
    Route::patch('vendors/{vendor}/commission', [AdminVendorController::class, 'updateCommission'])->name('vendors.commission');

    Route::get('withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
    Route::post('withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
    Route::post('withdrawals/{withdrawal}/paid', [AdminWithdrawalController::class, 'paid'])->name('withdrawals.paid');

    Route::get('finance', [AdminFinanceController::class, 'index'])->name('finance.index');

    // Orders
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('orders/{order}/payment', [AdminOrderController::class, 'updatePayment'])->name('orders.payment');
    Route::delete('orders/{order}', [AdminOrderController::class, 'destroy'])->name('orders.destroy');

    // Users
    Route::resource('users', AdminUserController::class);
    Route::patch('users/{user}/toggle', [AdminUserController::class, 'toggleStatus'])->name('users.toggle');

    // Blog
    Route::resource('blog', AdminBlogController::class);

    // Downloads
    Route::resource('downloads', AdminDownloadController::class);

    // About
    Route::get('about', [AdminAboutController::class, 'index'])->name('about.index');
    Route::post('about/stats',              [AdminAboutController::class, 'storeStat'])->name('about.stats.store');
    Route::put('about/stats/{stat}',        [AdminAboutController::class, 'updateStat'])->name('about.stats.update');
    Route::delete('about/stats/{stat}',     [AdminAboutController::class, 'destroyStat'])->name('about.stats.destroy');
    Route::post('about/team',               [AdminAboutController::class, 'storeTeam'])->name('about.team.store');
    Route::put('about/team/{team}',         [AdminAboutController::class, 'updateTeam'])->name('about.team.update');
    Route::delete('about/team/{team}',      [AdminAboutController::class, 'destroyTeam'])->name('about.team.destroy');
    Route::post('about/values',             [AdminAboutController::class, 'storeValue'])->name('about.values.store');
    Route::put('about/values/{value}',      [AdminAboutController::class, 'updateValue'])->name('about.values.update');
    Route::delete('about/values/{value}',   [AdminAboutController::class, 'destroyValue'])->name('about.values.destroy');

    // Testimonials
    Route::get('testimonials', [AdminTestimonialController::class, 'index'])->name('testimonials.index');
    Route::post('testimonials/{id}/approve', [AdminTestimonialController::class, 'approve'])->name('testimonials.approve');
    Route::post('testimonials/{id}/reject', [AdminTestimonialController::class, 'reject'])->name('testimonials.reject');
    Route::delete('testimonials/{id}', [AdminTestimonialController::class, 'destroy'])->name('testimonials.destroy');

    // Settings & Home Sections
    Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [AdminSettingController::class, 'update'])->name('settings.update');
    Route::get('sliders', [AdminSliderController::class, 'index'])->name('sliders.index');
    Route::post('sliders', [AdminSliderController::class, 'update'])->name('sliders.update');
    Route::post('settings/sync-api', [AdminSettingController::class, 'syncApi'])->name('settings.sync_api');
    Route::resource('brands', AdminBrandController::class);
    Route::resource('faqs', AdminFaqController::class);
});
