<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\ApiConfigService;
use App\Services\PlatformApiClient;
use App\Support\ApiData;
use App\Support\PaketOzellik;
use RuntimeException;

class DashboardController extends Controller
{
    public function __construct(
        protected PlatformApiClient $api,
        protected ApiConfigService $apiConfig,
    ) {}

    public function index()
    {
        // Entegrasyon yok → panel açık, yönlendirme kartı
        if (! $this->apiConfig->isConfigured()) {
            return view('panel.dashboard_simple', [
                'error' => null,
                'data' => null,
                'apiMissing' => true,
                'message' => 'Platform entegrasyonu yok. Ana sunucuda üretilen API anahtarını Entegrasyon sayfasına girin.',
            ]);
        }

        if (! $this->api->token()) {
            return view('panel.dashboard_simple', [
                'error' => null,
                'data' => null,
                'apiMissing' => false,
                'needLogin' => true,
                'message' => 'API anahtarları kayıtlı ancak platform oturumu yok. Hekim e-posta/şifrenizle yeniden giriş yapın.',
            ]);
        }

        try {
            PaketOzellik::refreshFromApi($this->api);
            $res = $this->api->get('/dashboard');
            $data = $res['data'] ?? [];
            if (! empty($data['doktor'])) {
                $user = PaketOzellik::mergeIntoUser(array_merge($this->api->user() ?? [], $data['doktor']));
                $this->api->setUser($user);
            }
        } catch (RuntimeException $e) {
            $code = (int) $e->getCode();
            // Geçersiz API anahtarı → paneli düşürme
            if ($code === 403 || str_contains(mb_strtolower($e->getMessage()), 'api anahtar')) {
                return redirect()
                    ->route('panel.api-entegrasyon')
                    ->with('hata', $e->getMessage());
            }
            if ($code === 401) {
                return view('panel.dashboard_simple', [
                    'error' => $e->getMessage(),
                    'data' => null,
                    'needLogin' => true,
                    'message' => 'Platform oturumu sona erdi. Yeniden giriş yapın.',
                ]);
            }

            return view('panel.dashboard_simple', [
                'error' => $e->getMessage(),
                'data' => null,
            ]);
        }

        $stats = $data['stats'] ?? [];
        $doktor = (object) array_merge($this->api->user() ?? [], $data['doktor'] ?? []);
        $toplamRandevu = $stats['toplam_randevu'] ?? 0;
        $kayitliHasta = $stats['kayitli_hasta'] ?? 0;
        $bekleyenTalep = $stats['bekleyen_talep'] ?? 0;
        $klinikDurumu = (bool) ($stats['randevuya_acik_mi'] ?? false);
        $bugunRandevular = ApiData::collection($data['bugun_randevular'] ?? []);
        $davetiyeler = collect();

        return view('panel.dashboard', compact(
            'doktor', 'toplamRandevu', 'kayitliHasta', 'bekleyenTalep', 'klinikDurumu',
            'bugunRandevular', 'davetiyeler', 'data'
        ));
    }
}
