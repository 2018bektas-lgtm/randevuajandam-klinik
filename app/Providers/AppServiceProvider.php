<?php

namespace App\Providers;

use App\Services\SiteContentService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // API anahtarları DB → runtime config
        try {
            app(\App\Services\ApiConfigService::class)->applyRuntimeConfig();
        } catch (\Throwable) {
            // migrate öncesi
        }

        // Frontend layout + sayfalar: canlı API (varsa) veya demo config
        View::composer('frontend.*', function ($view) {
            if (! $view->offsetExists('doktor')) {
                try {
                    // klinik sitesi: SiteContentService::klinik()
                    $svc = app(SiteContentService::class);
                    $data = method_exists($svc, 'klinik') ? $svc->klinik() : $svc->doktor();
                    $view->with('doktor', $data);
                } catch (\Throwable) {
                    $view->with('doktor', config('doktor', []));
                }
            }

            // Tüm frontend view'lara menü (tema header $nav)
            try {
                $doktor = $view->offsetExists('doktor') ? $view->offsetGet('doktor') : [];
                $view->with('nav', function_exists('site_nav') ? site_nav(is_array($doktor) ? $doktor : []) : []);
            } catch (\Throwable) {
                $view->with('nav', []);
            }
        });

        // Panel layout: API entegrasyon durumu
        View::composer('panel.layouts.app', function ($view) {
            try {
                $configured = app(\App\Services\ApiConfigService::class)->isConfigured();
            } catch (\Throwable) {
                $configured = false;
            }
            $view->with([
                'apiConfigured' => $configured,
                'apiToken' => (bool) session('doctor_api_token'),
            ]);
        });
    }
}
