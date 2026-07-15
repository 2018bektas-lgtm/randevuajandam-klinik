<?php

namespace App\Http\Middleware;

use App\Services\ApiConfigService;
use App\Services\PlatformApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API gerektiren panel rotaları.
 * Entegrasyon yoksa / anahtar geçersizse API ayar sayfasına yönlendir.
 * Site ayarları (lokal) bu middleware'den muaf tutulur.
 */
class RequireApiIntegration
{
    public function handle(Request $request, Closure $next): Response
    {
        $config = app(ApiConfigService::class);
        $api = app(PlatformApiClient::class);

        if (! $config->isConfigured()) {
            return redirect()
                ->route('panel.api-entegrasyon')
                ->with('hata', 'Platform entegrasyonu yok. Ana sunucudan üretilen API anahtarını buraya girin.');
        }

        // Token yok: hekim platform oturumu için giriş gerekir
        if (! $api->token()) {
            return redirect()
                ->route('panel.giris')
                ->with('uyari', 'Hekim işlemleri için platform hesabınızla giriş yapın (e-posta/şifre).');
        }

        return $next($request);
    }
}
