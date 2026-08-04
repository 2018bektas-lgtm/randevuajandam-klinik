<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use App\Support\ApiData;
use App\Support\PaketOzellik;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Ana site ile parity: bekleme, onam, hasta detay/export/dosya, iCal.
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
            $dosyalar = [];
            if (PaketOzellik::has('hasta_not_dosya')) {
                try {
                    $d = $this->api->get('/hastalar/'.$id.'/dosyalar');
                    $dosyalar = $d['data'] ?? [];
                } catch (\Throwable) {
                }
            }
            $onamFormlar = [];
            if (PaketOzellik::has('onam_formu')) {
                try {
                    $o = $this->api->get('/onam-formlari');
                    $onamFormlar = $o['data'] ?? [];
                } catch (\Throwable) {
                }
            }
        } catch (RuntimeException $e) {
            return redirect()->route('panel.hastalar')->with('hata', $e->getMessage());
        }

        return view('panel.paket.hasta_detay', compact('hasta', 'dosyalar', 'onamFormlar', 'id'));
    }

    public function hastaDosyaYukle(Request $request, int $id)
    {
        $request->validate(['dosya' => 'required|file|max:10240']);
        try {
            $this->api->postMultipart('/hastalar/'.$id.'/dosyalar', [
                'baslik' => $request->input('baslik'),
                'not' => $request->input('not'),
            ], ['dosya' => $request->file('dosya')]);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Dosya yüklendi.');
    }

    public function hastaDosyaSil(int $id)
    {
        try {
            $this->api->delete('/hastalar/dosyalar/'.$id);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Dosya silindi.');
    }

    public function onamIndex()
    {
        try {
            $res = $this->api->get('/onam-formlari');
            $formlar = $res['data'] ?? [];
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return view('panel.paket.onam', compact('formlar'));
    }

    public function onamStore(Request $request)
    {
        $data = $request->validate([
            'baslik' => 'required|string|max:255',
            'icerik' => 'required|string',
        ]);
        try {
            $this->api->post('/onam-formlari', array_merge($data, ['aktif_mi' => true]));
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Onam formu eklendi.');
    }

    public function onamDestroy(int $id)
    {
        try {
            $this->api->delete('/onam-formlari/'.$id);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Silindi.');
    }

    public function onamImza(Request $request)
    {
        $data = $request->validate([
            'onam_form_id' => 'required|integer',
            'hasta_id' => 'required|integer',
            'not' => 'nullable|string|max:1000',
        ]);
        try {
            $this->api->post('/onam-formlari/imza', $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Onam kaydı oluşturuldu.');
    }
}
