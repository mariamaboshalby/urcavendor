<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\Category;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(\App\Models\Product::class, \App\Policies\ProductPolicy::class);
        Gate::policy(\App\Models\Order::class, \App\Policies\OrderPolicy::class);
        Gate::policy(\App\Models\OrderItem::class, \App\Policies\OrderItemPolicy::class);
        Gate::policy(\App\Models\Withdrawal::class, \App\Policies\WithdrawalPolicy::class);

        Gate::before(function ($user) {
            if ($user && $user->isAdmin()) {
                return true;
            }

            return null;
        });

        // Set locale from session
        if (session()->has('locale')) {
            App::setLocale(session('locale'));
        }

        // Share active category TREE with frontend views (roots → children → grandchildren).
        // Cached for 1 hour; invalidated by CacheService::clearCategoryCaches().
        View::composer(['layouts.app', 'home', 'products.*', 'blog.*', 'about.*', 'news.*', 'contact.*', 'cart.*', 'checkout.*', 'auth.*', 'downloads.*', 'testimonials.*', 'store.*', 'vendor.register'], function ($view) {
            try {
                $navCategories = \Illuminate\Support\Facades\Cache::remember('active_categories', 3600, function () {
                    // Load only root-level active categories with their active children (2 levels deep)
                    return Category::with(['children' => function ($q) {
                            $q->active()
                              ->orderBy('sort_order')
                              ->orderBy('name_ar')
                              ->with(['children' => function ($q2) {
                                  $q2->active()
                                     ->orderBy('sort_order')
                                     ->orderBy('name_ar');
                              }]);
                        }])
                        ->active()
                        ->whereNull('parent_id')
                        ->select(['id', 'slug', 'name_ar', 'name_en', 'icon', 'image', 'is_active', 'parent_id', 'sort_order'])
                        ->orderBy('sort_order')
                        ->orderBy('name_ar')
                        ->get();
                });
                $view->with('navCategories', $navCategories);
            } catch (\Exception $e) {
                $view->with('navCategories', collect());
            }
        });
    }
}
