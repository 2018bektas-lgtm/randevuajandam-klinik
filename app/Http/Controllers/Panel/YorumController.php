<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use App\Support\ApiData;
use Illuminate\Http\Request;
use RuntimeException;

class YorumController extends Controller
{
    public function __construct(protected PlatformApiClient $api) {}

    public function index(Request $request)
    {
        try {
            $res = $this->api->get('/yorumlar', array_filter(['durum' => $request->get('durum')]));
            $items = $res['data']['items'] ?? [];
            $meta = $res['data']['meta'] ?? [];
            $stats = $res['data']['stats'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $yorumlar = ApiData::paginate($items, $meta);

        // Blade `istatistikler` bekliyor; API `stats` döner
        $istatistikler = [
            'toplam' => (int) ($stats['toplam'] ?? $meta['total'] ?? count($items)),
            'beklemede' => (int) ($stats['beklemede'] ?? 0),
            'onaylandi' => (int) ($stats['onaylandi'] ?? 0),
            'reddedildi' => (int) ($stats['reddedildi'] ?? 0),
            'ortalama_puan' => $stats['ortalama_puan'] ?? null,
        ];

        return view('panel.yorum.index', compact('yorumlar', 'istatistikler'));
    }

    public function yanit(Request $request, int $id)
    {
        $data = $request->validate([
            'doktor_yaniti' => ['required', 'string', 'min:5', 'max:500'],
            'onay_durumu' => ['nullable', 'in:beklemede,onaylandi,reddedildi'],
        ]);

        try {
            $this->api->post('/yorumlar/'.$id.'/yanit', $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Yanıt kaydedildi.');
    }

    public function durum(Request $request, int $id)
    {
        $data = $request->validate([
            'onay_durumu' => ['required', 'in:beklemede,onaylandi,reddedildi'],
        ]);

        try {
            $this->api->put('/yorumlar/'.$id.'/durum', $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Yorum durumu güncellendi.');
    }
}
