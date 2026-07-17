<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use App\Support\ApiData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RuntimeException;

class FinansController extends Controller
{
    public function __construct(protected PlatformApiClient $api) {}

    public function index()
    {
        try {
            $ozet = $this->api->get('/finans/ozet')['data'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $buAyGelir = (float) ($ozet['bu_ay_gelir'] ?? 0);
        $buAyGider = (float) ($ozet['bu_ay_gider'] ?? 0);
        $buAyNetKar = (float) ($ozet['bu_ay_net'] ?? ($buAyGelir - $buAyGider));
        $toplamBorc = (float) ($ozet['bekleyen_odeme'] ?? 0);

        $trend = $ozet['trend'] ?? [];
        $trendLabels = $trend['labels'] ?? [];
        $trendGelir = $trend['gelir'] ?? [];
        $trendGider = $trend['gider'] ?? [];
        if (! count($trendLabels)) {
            for ($i = 11; $i >= 0; $i--) {
                $trendLabels[] = now()->subMonths($i)->translatedFormat('M Y');
                $trendGelir[] = 0;
                $trendGider[] = 0;
            }
        }

        $hizmetLabels = $ozet['hizmet_dagilim']['labels'] ?? [];
        $hizmetValues = $ozet['hizmet_dagilim']['values'] ?? [];
        $giderLabels = $ozet['gider_dagilim']['labels'] ?? [];
        $giderValues = $ozet['gider_dagilim']['values'] ?? [];

        // Blade (ana site kopyası) months/incomeTrends isimleri de kullanabilir
        $months = $trendLabels;
        $incomeTrends = $trendGelir;
        $expenseTrends = $trendGider;

        $sonOdemeler = ApiData::collection($ozet['son_odemeler'] ?? []);
        $sonGiderler = ApiData::collection($ozet['son_giderler'] ?? []);
        $sonGelirler = $sonOdemeler;

        return view('panel.finans.index', compact(
            'buAyGelir', 'buAyGider', 'buAyNetKar', 'toplamBorc',
            'trendLabels', 'trendGelir', 'trendGider',
            'months', 'incomeTrends', 'expenseTrends',
            'hizmetLabels', 'hizmetValues', 'giderLabels', 'giderValues',
            'sonOdemeler', 'sonGelirler', 'sonGiderler'
        ));
    }

    public function gelirler(Request $request)
    {
        try {
            $res = $this->api->get('/finans/gelirler', array_filter([
                'durum' => $request->get('durum'),
                'hasta_id' => $request->get('hasta_id'),
                'finans_kategori_id' => $request->get('finans_kategori_id'),
                'tarih_baslangic' => $request->get('tarih_baslangic'),
                'tarih_bitis' => $request->get('tarih_bitis'),
                'page' => $request->get('page'),
            ]));
            $kategoriler = $this->api->get('/finans/kategoriler')['data'] ?? [];
            $hizmetler = $this->api->get('/hizmetler')['data'] ?? [];
            $hastalarRes = $this->api->get('/hastalar', ['per_page' => 50])['data']['items'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $items = $res['data']['items'] ?? [];
        $meta = $res['data']['meta'] ?? [];
        $odemeler = ApiData::paginate($items, $meta);
        $gelirKategorileri = ApiData::collection(collect($kategoriler)->where('tur', 'gelir')->values()->all());
        $hastalar = ApiData::collection(collect($hastalarRes)->map(function ($h) {
            $h = is_array($h) ? $h : (array) $h;
            $h['ad_soyad'] = trim(($h['ad'] ?? '').' '.($h['soyad'] ?? ''));

            return $h;
        })->all());
        $hizmetler = ApiData::collection($hizmetler);

        return view('panel.finans.gelirler', compact('odemeler', 'gelirKategorileri', 'hastalar', 'hizmetler'));
    }

    public function giderler(Request $request)
    {
        try {
            $res = $this->api->get('/finans/giderler', array_filter([
                'page' => $request->get('page'),
                'finans_kategori_id' => $request->get('finans_kategori_id'),
                'tarih_baslangic' => $request->get('tarih_baslangic'),
                'tarih_bitis' => $request->get('tarih_bitis'),
            ]));
            $kategoriler = $this->api->get('/finans/kategoriler')['data'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $items = $res['data']['items'] ?? [];
        $meta = $res['data']['meta'] ?? [];
        $giderler = ApiData::paginate($items, $meta);
        $giderKategorileri = ApiData::collection(collect($kategoriler)->where('tur', 'gider')->values()->all());

        return view('panel.finans.giderler', compact('giderler', 'giderKategorileri'));
    }

    public function kategoriler()
    {
        try {
            $items = $this->api->get('/finans/kategoriler')['data'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $all = ApiData::collection($items);
        $gelirKategorileri = $all->filter(fn ($k) => ($k->tur ?? '') === 'gelir')->values();
        $giderKategorileri = $all->filter(fn ($k) => ($k->tur ?? '') === 'gider')->values();
        $kategoriler = $all;

        return view('panel.finans.kategoriler', compact('kategoriler', 'gelirKategorileri', 'giderKategorileri'));
    }

    public function hastaBakiyeleri(Request $request)
    {
        try {
            $items = $this->api->get('/finans/hasta-bakiyeleri', array_filter([
                'arama' => $request->get('arama'),
                'sadece_borclular' => $request->get('sadece_borclular'),
            ]))['data'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $hastalar = ApiData::collection($items);

        return view('panel.finans.hasta_bakiyeleri', compact('hastalar'));
    }

    /**
     * Hasta cari hesap sayfası (platform API).
     */
    public function hastaHesap(int $hastaId)
    {
        try {
            $data = $this->api->get('/finans/hasta/'.$hastaId)['data'] ?? [];
            $hizmetler = $this->api->get('/hizmetler')['data'] ?? [];
            $kategoriler = $this->api->get('/finans/kategoriler')['data'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.finans.hasta-bakiyeleri')->with('hata', $e->getMessage());
        }

        $hasta = (object) ($data['hasta'] ?? []);
        if (empty($hasta->id) && empty($hasta->ad_soyad)) {
            return redirect()->route('panel.finans.hasta-bakiyeleri')->with('hata', 'Hasta hesabı bulunamadı.');
        }
        $hasta->id = $hasta->id ?? $hastaId;
        $hasta->ad_soyad = $hasta->ad_soyad ?? trim(($hasta->ad ?? '').' '.($hasta->soyad ?? ''));

        $ozet = $data['ozet'] ?? [];
        $toplamBorc = (float) ($ozet['toplam_borc'] ?? 0);
        $toplamOdenen = (float) ($ozet['toplam_odenen'] ?? 0);
        $kalanBakiye = (float) ($ozet['kalan_bakiye'] ?? ($toplamBorc - $toplamOdenen));

        $faturalar = ApiData::collection($data['faturalar'] ?? []);
        $acikFaturalar = ApiData::collection($data['acik_faturalar'] ?? []);
        $hizmetler = ApiData::collection($hizmetler);
        $gelirKategorileri = ApiData::collection(collect($kategoriler)->where('tur', 'gelir')->values()->all());

        $hareketler = $faturalar->map(function ($f) {
            $tutar = (float) ($f->tutar ?? 0);
            $odenen = (float) ($f->odenen_tutar ?? 0);
            $kalemler = collect($f->kalemler ?? [])->map(function ($k) {
                $k = is_array($k) ? (object) $k : $k;
                if (is_string($k->tarih ?? null) && ! ($k->tarih instanceof \DateTimeInterface)) {
                    try {
                        $k->tarih = Carbon::parse($k->tarih);
                    } catch (\Throwable) {
                        // keep string
                    }
                }

                return $k;
            });

            return [
                'tip' => 'borc',
                'tarih' => $f->odeme_tarihi ?? null,
                'baslik' => $f->hizmet ?? ($f->aciklama ?: 'Borç / hizmet kaydı'),
                'aciklama' => $f->aciklama ?? null,
                'tutar' => $tutar,
                'odenen' => $odenen,
                'kalan' => (float) ($f->kalan ?? max(0, $tutar - $odenen)),
                'durum' => $f->durum ?? 'beklemede',
                'odeme_id' => $f->id ?? null,
                'kalemler' => $kalemler,
            ];
        })->sortByDesc('tarih')->values();

        return view('panel.finans.hasta_hesap', compact(
            'hasta',
            'toplamBorc',
            'toplamOdenen',
            'kalanBakiye',
            'acikFaturalar',
            'hizmetler',
            'gelirKategorileri',
            'hareketler',
        ));
    }

    public function hastaTahsilat(Request $request, int $hastaId)
    {
        $data = $request->validate([
            'odeme_id' => ['required', 'integer'],
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'tarih' => ['required', 'date'],
            'odeme_yontemi' => ['required', 'in:nakit,kredi_karti,havale,online'],
            'not' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->api->post('/finans/hasta/'.$hastaId.'/tahsilat', $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('panel.finans.hasta-hesap', $hastaId)
            ->with('basari', 'Tahsilat kaydedildi.');
    }

    public function hastaBorcEkle(Request $request, int $hastaId)
    {
        $data = $request->validate([
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'odeme_tarihi' => ['required', 'date'],
            'hizmet_id' => ['nullable', 'integer'],
            'finans_kategori_id' => ['nullable', 'integer'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
            'ilk_odeme_tutar' => ['nullable', 'numeric', 'min:0'],
            'ilk_odeme_yontemi' => ['nullable', 'in:nakit,kredi_karti,havale,online'],
        ]);

        try {
            $this->api->post('/finans/hasta/'.$hastaId.'/borc', $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('panel.finans.hasta-hesap', $hastaId)
            ->with('basari', 'Borç kaydı oluşturuldu.');
    }

    public function raporPdf(Request $request)
    {
        try {
            $data = $this->api->get('/finans/rapor', array_filter([
                'tarih_baslangic' => $request->get('tarih_baslangic'),
                'tarih_bitis' => $request->get('tarih_bitis'),
            ]))['data'] ?? [];
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        $tarihBaslangic = Carbon::parse($data['tarih_baslangic'] ?? now()->startOfMonth());
        $tarihBitis = Carbon::parse($data['tarih_bitis'] ?? now()->endOfMonth());
        $doktor = (object) ($data['doktor'] ?? []);
        $odemeler = ApiData::collection($data['odemeler'] ?? []);
        $giderler = ApiData::collection($data['giderler'] ?? []);
        $toplamGelir = (float) ($data['toplam_gelir'] ?? 0);
        $toplamGider = (float) ($data['toplam_gider'] ?? 0);
        $netKar = (float) ($data['net_kar'] ?? 0);
        $toplamTahsilEdilmeyen = (float) ($data['toplam_tahsil_edilmeyen'] ?? 0);

        // DomPDF yoksa HTML olarak indirilebilir rapor
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('panel.finans.rapor_pdf', compact(
                'doktor', 'tarihBaslangic', 'tarihBitis', 'odemeler', 'giderler',
                'toplamGelir', 'toplamGider', 'netKar', 'toplamTahsilEdilmeyen'
            ));

            return $pdf->download('Finans_Raporu_'.$tarihBaslangic->format('d_m_Y').'_'.$tarihBitis->format('d_m_Y').'.pdf');
        }

        return response()
            ->view('panel.finans.rapor_pdf', compact(
                'doktor', 'tarihBaslangic', 'tarihBitis', 'odemeler', 'giderler',
                'toplamGelir', 'toplamGider', 'netKar', 'toplamTahsilEdilmeyen'
            ))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'inline; filename="Finans_Raporu.html"');
    }

    public function storeGelir(Request $request)
    {
        $data = $request->validate([
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'odenen_tutar' => ['nullable', 'numeric', 'min:0'],
            'odeme_yontemi' => ['nullable', 'in:nakit,kredi_karti,havale,online'],
            'ilk_odeme_yontemi' => ['nullable', 'in:nakit,kredi_karti,havale,online'],
            'odeme_tarihi' => ['required', 'date'],
            'aciklama' => ['nullable', 'string'],
            'finans_kategori_id' => ['nullable', 'integer'],
            'hasta_id' => ['nullable', 'integer'],
            'hizmet_id' => ['nullable', 'integer'],
            'durum' => ['nullable', 'in:beklemede,odendi,kismi_odeme,iptal'],
        ]);

        try {
            $this->api->post('/finans/gelirler', $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Gelir eklendi.');
    }

    public function updateGelir(Request $request, int $id)
    {
        $data = $request->validate([
            'hasta_id' => ['nullable', 'integer'],
            'hizmet_id' => ['nullable', 'integer'],
            'finans_kategori_id' => ['nullable', 'integer'],
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
            'odeme_tarihi' => ['required', 'date'],
        ]);

        try {
            $this->api->post('/finans/gelirler/'.$id.'/guncelle', $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Gelir güncellendi.');
    }

    public function storeKalem(Request $request, int $id)
    {
        $data = $request->validate([
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'tarih' => ['required', 'date'],
            'odeme_yontemi' => ['required', 'in:nakit,kredi_karti,havale,online'],
            'not' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->api->post('/finans/gelirler/'.$id.'/kalem', $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Ödeme kalemi eklendi.');
    }

    public function destroyKalem(int $odemeId, int $kalemId)
    {
        try {
            $this->api->delete('/finans/gelirler/'.$odemeId.'/kalem/'.$kalemId);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Ödeme kalemi silindi.');
    }

    public function destroyGelir(int $id)
    {
        try {
            $this->api->delete('/finans/gelirler/'.$id);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Gelir silindi.');
    }

    public function storeGider(Request $request)
    {
        $data = $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'tarih' => ['required', 'date'],
            'aciklama' => ['nullable', 'string'],
            'finans_kategori_id' => ['nullable', 'integer'],
        ]);

        try {
            $this->api->post('/finans/giderler', $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Gider eklendi.');
    }

    public function updateGider(Request $request, int $id)
    {
        $data = $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'finans_kategori_id' => ['nullable', 'integer'],
            'tutar' => ['required', 'numeric', 'min:0.01'],
            'tarih' => ['required', 'date'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->api->post('/finans/giderler/'.$id.'/guncelle', $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Gider güncellendi.');
    }

    public function destroyGider(int $id)
    {
        try {
            $this->api->delete('/finans/giderler/'.$id);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Gider silindi.');
    }

    public function storeKategori(Request $request)
    {
        $data = $request->validate([
            'ad' => ['required', 'string', 'max:100'],
            'tur' => ['required', 'in:gelir,gider'],
            'renk' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            $this->api->post('/finans/kategoriler', $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Kategori eklendi.');
    }

    public function updateKategori(Request $request, int $id)
    {
        $data = $request->validate([
            'ad' => ['required', 'string', 'max:100'],
            'tur' => ['required', 'in:gelir,gider'],
            'renk' => ['nullable', 'string', 'max:20'],
            'aktif' => ['nullable'],
        ]);
        if ($request->has('aktif')) {
            $data['aktif'] = $request->boolean('aktif');
        }

        try {
            $this->api->post('/finans/kategoriler/'.$id, $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Kategori güncellendi.');
    }

    public function toggleKategori(int $id)
    {
        try {
            $this->api->post('/finans/kategoriler/'.$id.'/toggle');
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Kategori durumu güncellendi.');
    }

    public function destroyKategori(int $id)
    {
        try {
            $this->api->delete('/finans/kategoriler/'.$id);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Kategori silindi.');
    }
}
