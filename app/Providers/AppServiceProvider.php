<?php

namespace App\Providers;

use App\Models\BarangMasuk;
use App\Models\BarangRusak;
use App\Models\Pos;
use App\Models\Product;
use App\Models\Retur;
use App\Observers\BarangMasukObserver;
use App\Observers\PosObserver;
use App\Observers\ReturObserver;
use App\Policies\BarangMasukPolicy;
use App\Policies\BarangRusakPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReturPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies untuk authorization
        Gate::policy(BarangMasuk::class, BarangMasukPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Retur::class, ReturPolicy::class);
        Gate::policy(BarangRusak::class, BarangRusakPolicy::class);

        // Register observers untuk otomatis tracking stock
        BarangMasuk::observe(BarangMasukObserver::class);
        Pos::observe(PosObserver::class);
        Retur::observe(ReturObserver::class);
    }
}