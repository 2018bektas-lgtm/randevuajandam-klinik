<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Online görüşme — hekim paneli.
 * WebRTC sinyali ve oda ana platformda (SITE_URL); burada token ile katılım yönlendirmesi yapılır.
 * Hasta ile aynı oda/cache paylaşımı için platform /hekim/gorusme/{id}/app kullanılır.
 */
class GorusmeController extends Controller
{
    public function __construct(protected PlatformApiClient $api) {}

    public function join(Request $request, int $id)
    {
        $token = $this->api->token();
        if (! $token) {
            return redirect()->route('panel.giris')->with('hata', 'Platform oturumu gerekli.');
        }

        try {
            $res = $this->api->get('/randevular/'.$id.'/gorusme');
            $data = $res['data'] ?? [];
        } catch (RuntimeException $e) {
            if ($e->getCode() === 401) {
                return redirect()->route('panel.giris')->with('hata', $e->getMessage());
            }

            return redirect()
                ->route('panel.randevular')
                ->with('hata', $e->getMessage() ?: 'Görüşme odası açılamadı.');
        }

        $hekimJoin = (string) ($data['hekim_join_url'] ?? '');
        if ($hekimJoin === '') {
            return redirect()
                ->route('panel.randevular')
                ->with('hata', 'Görüşme bağlantısı bulunamadı. SITE_URL yapılandırmasını kontrol edin.');
        }

        if (empty($data['can_join'])) {
            $win = $data['window'] ?? null;
            $hint = 'Görüşme penceresi henüz açık değil veya kapanmış.';
            if (is_array($win) && ! empty($win['baslangic'])) {
                $hint .= ' Pencere: '.$win['baslangic'].' – '.($win['bitis'] ?? '');
            }

            return redirect()->route('panel.randevular')->with('uyari', $hint);
        }

        $sep = str_contains($hekimJoin, '?') ? '&' : '?';
        $url = $hekimJoin.$sep.'access_token='.urlencode($token);

        return redirect()->away($url);
    }
}
