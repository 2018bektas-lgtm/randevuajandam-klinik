<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\ApiConfigService;
use App\Services\PlatformApiClient;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * API entegrasyonu — anahtarlar ana sunucuda üretilir, burada yalnızca girilir.
 * Doktor sitesinde API üretimi / domain kurulum yok.
 */
class WebSitesiController extends Controller
{
    public function __construct(
        protected ApiConfigService $apiConfig,
        protected PlatformApiClient $api,
    ) {}

    public function index()
    {
        $cfg = $this->apiConfig->all();
        $status = null;

        if ($this->apiConfig->isConfigured()) {
            $status = $this->apiConfig->testConnection();
        }

        return view('panel.web_site.entegrasyon', [
            'api_key' => $cfg['api_key'] ?? '',
            'api_key_masked' => $this->apiConfig->maskedKey(),
            'api_secret_set' => filled($cfg['api_secret'] ?? null),
            'platform' => $cfg['platform'] ?? '',
            'configured' => $this->apiConfig->isConfigured(),
            'hasToken' => (bool) $this->api->token(),
            'status' => $status,
            'panelUser' => session('panel_auth'),
        ]);
    }

    public function kaydet(Request $request)
    {
        $data = $request->validate([
            'platform' => ['required', 'url', 'max:255'],
            'api_key' => ['required', 'string', 'max:255'],
            'api_secret' => ['nullable', 'string', 'max:255'],
        ], [
            'platform.required' => 'Platform API adresi zorunludur.',
            'platform.url' => 'Geçerli bir URL girin (örn. http://127.0.0.1:8001/api/v1).',
            'api_key.required' => 'API Key zorunludur (ana sunucudan kopyalayın).',
        ]);

        // Platform URL'ini normalize: .../api/v1
        $platform = rtrim($data['platform'], '/');
        $platform = preg_replace('#/(public|doctor)$#', '', $platform) ?? $platform;

        $this->apiConfig->save([
            'platform' => $platform,
            'api_key' => $data['api_key'],
            'api_secret' => $data['api_secret'] ?? '',
        ]);

        // Bağlantı testi
        $test = $this->apiConfig->testConnection();
        if (! $test['ok']) {
            return back()->withInput()->with(
                'uyari',
                'Anahtarlar kaydedildi ancak bağlantı doğrulanamadı: '.$test['message']
            );
        }

        return redirect()
            ->route('panel.api-entegrasyon')
            ->with('basari', 'API entegrasyonu kaydedildi. '.$test['message'].' Platform işlemleri için hekim e-posta/şifrenizle yeniden giriş yapın.');
    }

    public function test()
    {
        $test = $this->apiConfig->testConnection();

        return back()->with(
            $test['ok'] ? 'basari' : 'hata',
            $test['message']
        );
    }

    public function temizle()
    {
        $this->apiConfig->clear();
        $this->api->setToken(null);

        return back()->with('basari', 'API anahtarları temizlendi. Panel erişiminiz yerel oturum ile devam eder.');
    }

    /** Eski rotalar — üretim yok */
    public function kurulum()
    {
        return redirect()
            ->route('panel.api-entegrasyon')
            ->with('hata', 'API anahtarı doktor sitesinde üretilmez. Ana sunucu (Randevu Ajandam) hekim panelinden üretip buraya yapıştırın.');
    }

    public function apiAnahtari()
    {
        return $this->kurulum();
    }
}
