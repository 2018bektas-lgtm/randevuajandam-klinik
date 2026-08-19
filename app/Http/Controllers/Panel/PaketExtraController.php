<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use App\Support\ApiData;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Ana site ile parity: bekleme, hasta detay/export, iCal.
 * (KTS/USBS kapsam disi kalmak icin: hasta dosya + onam modulleri kaldirildi.)
 */
class PaketExtraController extends Controller
{
    public function __construct(protected PlatformApiClient $api) {}

    public function bekleme()
    {
        try {
            $res = $this->api->get('/bekleme-listesi');
            $items = ApiData::paginate($res['data']['items'] ?? [], $res['data']['meta'] ?? []);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return view('panel.paket.bekleme', compact('items'));
    }

    public function beklemeDurum(Request $request, int $id)
    {
        $request->validate(['durum' => 'required|string|max:40']);
        try {
            $this->api->post('/bekleme-listesi/'.$id.'/durum', ['durum' => $request->durum]);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Durum güncellendi.');
    }

    public function beklemeBildir(int $id)
    {
        try {
            $this->api->post('/bekleme-listesi/'.$id.'/bildir');
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Bildirim gönderildi.');
    }

    public function beklemeSil(int $id)
    {
        try {
            $this->api->delete('/bekleme-listesi/'.$id);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Silindi.');
    }

    public function ical()
    {
        try {
            // Binary download via client raw
            $api = $this->api;
            $res = $api->http(true)->get($api->doctorBase().'/takvim/ical');
            if (! $res->successful()) {
                return back()->with('hata', 'iCal alınamadı.');
            }

            return response($res->body(), 200, [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="randevular.ics"',
            ]);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }
    }

    public function hastaExport()
    {
        try {
            $res = $this->api->http(true)->get($this->api->doctorBase().'/hastalar/export');
            if (! $res->successful()) {
                return back()->with('hata', 'Dışa aktarma başarısız.');
            }

            return response($res->body(), 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="hastalar.csv"',
            ]);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }
    }

    public function hastaDetay(int $id)
    {
        try {
            $res = $this->api->get('/hastalar/'.$id);
            $hasta = $res['data'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.hastalar')->with('hata', $e->getMessage());
        }

        return view('panel.paket.hasta_detay', compact('hasta', 'id'));
    }
}
