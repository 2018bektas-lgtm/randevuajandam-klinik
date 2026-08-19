<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use App\Support\ApiData;
use Illuminate\Http\Request;
use RuntimeException;

class RandevuController extends Controller
{
    public function __construct(protected PlatformApiClient $api) {}

    /**
     * Ana site ile aynı FullCalendar takvim görünümü.
     */
    public function index(Request $request)
    {
        try {
            $ayarRes = $this->api->get('/randevu-ayarlari');
            $data = $ayarRes['data'] ?? [];
            $hizmetlerRaw = $this->api->get('/hizmetler')['data'] ?? [];
        } catch (RuntimeException $e) {
            if ($e->getCode() === 401) {
                return redirect()->route('panel.giris')->with('hata', $e->getMessage());
            }

            return view('panel.randevu.takvim', [
                'error' => $e->getMessage(),
                'minHour' => '08:00:00',
                'maxHour' => '20:00:00',
                'slotDurationString' => '00:30:00',
                'businessHours' => [
                    ['daysOfWeek' => [1, 2, 3, 4, 5], 'startTime' => '09:00', 'endTime' => '17:00'],
                ],
                'periyot' => 30,
                'hizmetler' => collect(),
            ]);
        }

        $hizmetler = collect($hizmetlerRaw)->map(function ($h) {
            $h = is_array($h) ? $h : (array) $h;

            return (object) [
                'id' => $h['id'] ?? null,
                'ad' => $h['ad'] ?? '',
                'sure' => $h['sure'] ?? 30,
                'aktif_mi' => $h['aktif_mi'] ?? true,
            ];
        })->filter(fn ($h) => $h->aktif_mi && $h->id)->values();

        $ayar = $data['ayar'] ?? [];
        if (is_object($ayar)) {
            $ayar = (array) $ayar;
        }
        $periyot = (int) ($ayar['randevu_periyodu'] ?? 30);
        if ($periyot <= 0) {
            $periyot = 30;
        }
        $slotDurationString = '00:'.str_pad((string) min($periyot, 59), 2, '0', STR_PAD_LEFT).':00';
        if ($periyot >= 60) {
            $slotDurationString = '01:00:00';
        }

        $saatler = collect($data['calisma_saatleri'] ?? []);
        $minHour = '08:00:00';
        $maxHour = '20:00:00';
        $businessHours = [];
        $baslangiclar = [];
        $bitisler = [];

        foreach ($saatler as $cs) {
            $cs = is_array($cs) ? $cs : (array) $cs;
            if (empty($cs['aktif_mi'])) {
                continue;
            }
            $gun = (int) ($cs['gun'] ?? 0);
            $fcDay = $gun === 7 ? 0 : $gun;
            $bas = substr((string) ($cs['mesai_baslangic'] ?? '09:00'), 0, 5);
            $bit = substr((string) ($cs['mesai_bitis'] ?? '17:00'), 0, 5);
            $baslangiclar[] = $bas;
            $bitisler[] = $bit;
            $businessHours[] = [
                'daysOfWeek' => [$fcDay],
                'startTime' => $bas,
                'endTime' => $bit,
            ];
        }

        if (count($baslangiclar)) {
            sort($baslangiclar);
            $minHour = $baslangiclar[0].':00';
        }
        if (count($bitisler)) {
            rsort($bitisler);
            $maxHour = $bitisler[0].':00';
        }
        if (! count($businessHours)) {
            $businessHours = [
                ['daysOfWeek' => [1, 2, 3, 4, 5], 'startTime' => '09:00', 'endTime' => '17:00'],
            ];
        }

        return view('panel.randevu.takvim', [
            'error' => null,
            'minHour' => $minHour,
            'maxHour' => $maxHour,
            'slotDurationString' => $slotDurationString,
            'businessHours' => $businessHours,
            'periyot' => $periyot,
            'hizmetler' => $hizmetler,
        ]);
    }

    public function updatePeriod(Request $request)
    {
        $data = $request->validate([
            'periyot' => ['required', 'integer', 'in:15,20,30,45,60'],
        ]);

        try {
            $res = $this->api->post('/takvim/periyot', $data);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json($res);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hizmet_id' => ['required', 'integer'],
            'danisan_id' => ['nullable', 'integer'],
            'tarih' => ['required', 'date'],
            'saat' => ['required', 'date_format:H:i'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
            'ad' => ['nullable', 'string', 'max:100'],
            'soyad' => ['nullable', 'string', 'max:100'],
            'telefon' => ['nullable', 'string', 'max:40'],
            'e_posta' => ['nullable', 'email'],
        ]);

        try {
            if (! empty($data['danisan_id'])) {
                $res = $this->api->post('/randevular', $data);
            } else {
                $request->validate([
                    'ad' => ['required', 'string', 'max:100'],
                    'soyad' => ['required', 'string', 'max:100'],
                    'telefon' => ['required', 'string', 'max:40'],
                ]);
                $res = $this->api->post('/randevular/misafir', $data);
            }
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json($res, 201);
    }

    public function destroy(int $id)
    {
        try {
            $res = $this->api->delete('/randevular/'.$id);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json($res);
    }

    public function hastaAra(Request $request)
    {
        $q = $request->get('q', '');
        try {
            $res = $this->api->http(true)->get($this->api->doctorBase().'/hastalar/ara', ['q' => $q]);

            return response()->json($res->json() ?? ['results' => []], $res->status());
        } catch (\Throwable $e) {
            return response()->json(['results' => [], 'message' => $e->getMessage()], 500);
        }
    }

    public function hastaEkle(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'telefon' => ['required', 'string', 'max:40'],
        ]);

        try {
            $res = $this->api->post('/hastalar', $data);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json($res, 201);
    }

    /**
     * Proxy FullCalendar events from platform API (session-authenticated).
     */
    public function events(Request $request)
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
        ]);

        try {
            // PlatformApiClient::get appends query; calendar API expects start/end
            $res = $this->api->http(true)->get($this->api->doctorBase().'/takvim/events', [
                'start' => $request->start,
                'end' => $request->end,
            ]);

            if ($res->status() === 401) {
                return response()->json(['message' => 'Oturum sona erdi'], 401);
            }

            return response()->json($res->json() ?? [], $res->status());
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Liste görünümü (talepler) — isteğe bağlı.
     */
    public function talepler(Request $request)
    {
        $durum = $request->get('durum', 'beklemede');

        try {
            $res = $this->api->get('/randevular', array_filter([
                'durum' => $durum !== 'all' ? $durum : null,
                'tarih' => $request->get('tarih'),
                'page' => $request->get('page'),
                'per_page' => 20,
            ], fn ($v) => $v !== null && $v !== ''));
            $items = $res['data']['items'] ?? [];
            $meta = $res['data']['meta'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $mapped = collect($items)->map(function ($r) {
            $r = is_array($r) ? $r : (array) $r;
            $hizmet = $r['hizmet'] ?? null;
            if (is_array($hizmet)) {
                $hizmet = (object) $hizmet;
            }

            return (object) [
                'id' => $r['id'] ?? null,
                'ad' => $r['ad'] ?? '',
                'soyad' => $r['soyad'] ?? '',
                'telefon' => $r['telefon'] ?? '',
                'e_posta' => $r['e_posta'] ?? '',
                'tarih' => $r['tarih'] ?? null,
                'saat' => $r['saat'] ?? '',
                'durum' => $r['durum'] ?? '',
                'not' => $r['not'] ?? '',
                'gorusme_tipi' => $r['gorusme_tipi'] ?? 'yuz_yuze',
                'meeting_url' => $r['meeting_url'] ?? null,
                'hizmet' => $hizmet,
            ];
        });

        $talepler = new \Illuminate\Pagination\LengthAwarePaginator(
            $mapped,
            (int) ($meta['total'] ?? $mapped->count()),
            20,
            (int) ($meta['current_page'] ?? 1),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('panel.randevu.talepler', [
            'talepler' => $talepler,
            'items' => $mapped,
            'meta' => $meta,
            'durum' => $durum,
        ]);
    }

    public function durum(Request $request, int $id)
    {
        $data = $request->validate([
            'durum' => ['required', 'in:beklemede,onaylandi,tamamlandi,iptal'],
        ]);

        try {
            $this->api->put('/randevular/'.$id.'/durum', $data);
        } catch (RuntimeException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 422);
            }

            return back()->with('hata', $e->getMessage());
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Randevu durumu güncellendi.']);
        }

        return back()->with('basari', 'Randevu durumu güncellendi (ana platform ile senkron).');
    }

    public function ayarlar()
    {
        try {
            $res = $this->api->get('/randevu-ayarlari');
            $data = $res['data'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $ayarlar = ApiData::obj($data['ayar'] ?? [
            'aktif_mi' => true,
            'randevu_iptal_aktif_mi' => true,
            'email_bildirimleri' => true,
            'sms_bildirimleri' => false,
            'randevu_onay_tipi' => 'manuel',
            'en_erken_randevu_saati' => 2,
            'en_gec_randevu_gunu' => 30,
            'randevu_periyodu' => 30,
            'iptal_saat_limiti' => 24,
            'gunluk_maksimum_randevu' => 0,
        ]);

        $gunAdlari = [1 => 'Pazartesi', 2 => 'Salı', 3 => 'Çarşamba', 4 => 'Perşembe', 5 => 'Cuma', 6 => 'Cumartesi', 7 => 'Pazar'];
        $saatler = collect($data['calisma_saatleri'] ?? []);
        $byGun = $saatler->keyBy(fn ($s) => is_array($s) ? ($s['gun'] ?? 0) : ($s->gun ?? 0));

        $calismaSaatleri = collect(range(1, 7))->map(function ($gun) use ($byGun, $gunAdlari) {
            $row = $byGun->get($gun);
            $row = is_array($row) ? $row : (array) ($row ?? []);

            return (object) [
                'gun' => $gun,
                'gun_adi' => $gunAdlari[$gun],
                'aktif_mi' => (bool) ($row['aktif_mi'] ?? in_array($gun, [1, 2, 3, 4, 5], true)),
                'mesai_baslangic' => isset($row['mesai_baslangic']) ? substr((string) $row['mesai_baslangic'], 0, 5) : '09:00',
                'mesai_bitis' => isset($row['mesai_bitis']) ? substr((string) $row['mesai_bitis'], 0, 5) : '17:00',
                'ogle_arasi_aktif_mi' => (bool) ($row['ogle_arasi_aktif_mi'] ?? false),
                'ogle_baslangic' => isset($row['ogle_baslangic']) && $row['ogle_baslangic'] ? substr((string) $row['ogle_baslangic'], 0, 5) : '12:00',
                'ogle_bitis' => isset($row['ogle_bitis']) && $row['ogle_bitis'] ? substr((string) $row['ogle_bitis'], 0, 5) : '13:00',
            ];
        });

        // give each day a stable pseudo-id for form field names (main blade uses $cs->id)
        $calismaSaatleri = $calismaSaatleri->map(function ($cs) {
            $cs->id = $cs->gun;

            return $cs;
        });

        $izinler = collect();
        try {
            $izinRes = $this->api->get('/izinler');
            $izinItems = $izinRes['data'] ?? [];
            $izinler = collect(is_array($izinItems) ? $izinItems : [])->map(function ($i) {
                $i = is_array($i) ? $i : (array) $i;
                $bas = $i['baslangic_zaman'] ?? null;
                $bit = $i['bitis_zaman'] ?? null;

                return (object) [
                    'id' => $i['id'] ?? null,
                    'baslangic_zaman' => $bas ? \Carbon\Carbon::parse($bas) : null,
                    'bitis_zaman' => $bit ? \Carbon\Carbon::parse($bit) : null,
                    'aciklama' => $i['aciklama'] ?? null,
                ];
            })->filter(fn ($i) => $i->id && $i->baslangic_zaman && $i->bitis_zaman)->values();
        } catch (RuntimeException) {
            // izin listesi yoksa boş kalsın
        }

        return view('panel.randevu.ayarlar', compact('ayarlar', 'calismaSaatleri', 'izinler'));
    }

    public function izinEkle(Request $request)
    {
        $data = $request->validate([
            'baslangic_tarih' => ['required', 'date'],
            'baslangic_saat' => ['required', 'date_format:H:i'],
            'bitis_tarih' => ['required', 'date'],
            'bitis_saat' => ['required', 'date_format:H:i'],
            'aciklama' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->api->post('/izinler', $data);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'İzin/tatil eklendi (ana platform ile senkron).');
    }

    public function izinSil(int $id)
    {
        try {
            $this->api->delete('/izinler/'.$id);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'İzin silindi.');
    }

    public function reschedule(Request $request, int $id)
    {
        $data = $request->validate([
            'tarih' => ['required', 'date'],
            'saat' => ['required', 'date_format:H:i'],
        ]);

        try {
            $res = $this->api->post('/randevular/'.$id.'/reschedule', $data);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 422);
        }

        return response()->json($res);
    }

    public function guncelle(Request $request, int $id)
    {
        $data = $request->validate([
            'hizmet_id' => ['required', 'integer'],
            'aciklama' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $res = $this->api->post('/randevular/'.$id.'/guncelle', $data);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 422);
        }

        return response()->json($res);
    }

    public function hastalar(Request $request)
    {
        try {
            $res = $this->api->get('/hastalar', array_filter([
                'q' => $request->get('q'),
                'page' => $request->get('page'),
                'per_page' => 20,
            ], fn ($v) => $v !== null && $v !== ''));
            $items = $res['data']['items'] ?? [];
            $meta = $res['data']['meta'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $mapped = collect($items)->map(function ($h) {
            $h = is_array($h) ? $h : (array) $h;

            return (object) [
                'id' => $h['id'] ?? null,
                'ad' => $h['ad'] ?? '',
                'soyad' => $h['soyad'] ?? '',
                'telefon' => $h['telefon'] ?? '',
                'e_posta' => $h['e_posta'] ?? '',
                'aktif_mi' => (bool) ($h['aktif_mi'] ?? true),
                'randevular_count' => (int) ($h['randevu_sayisi'] ?? 0),
            ];
        });

        $hastalar = new \Illuminate\Pagination\LengthAwarePaginator(
            $mapped,
            (int) ($meta['total'] ?? $mapped->count()),
            20,
            (int) ($meta['current_page'] ?? 1),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('panel.randevu.hastalar', compact('hastalar'));
    }

    public function hizliKapatSlots(Request $request)
    {
        $request->validate(['tarih' => ['required', 'date']]);

        try {
            $res = $this->api->http(true)->get($this->api->doctorBase().'/hizli-kapat/slots', [
                'tarih' => $request->tarih,
            ]);

            return response()->json($res->json() ?? [], $res->status());
        } catch (\Throwable $e) {
            return response()->json(['aktif_mi' => false, 'mesaj' => $e->getMessage(), 'slots' => []], 500);
        }
    }

    public function hizliKapatKaydet(Request $request)
    {
        $data = $request->validate([
            'tarih' => ['required', 'date'],
            'saatler' => ['nullable', 'array'],
            'saatler.*' => ['required', 'date_format:H:i'],
        ]);

        try {
            $res = $this->api->post('/hizli-kapat', [
                'tarih' => $data['tarih'],
                'saatler' => $data['saatler'] ?? [],
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'basarili' => false,
                'message' => $e->getMessage(),
                'mesaj' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }

        return response()->json(array_merge($res, [
            'basarili' => $res['basarili'] ?? $res['success'] ?? true,
        ]));
    }

    public function ayarlarKaydet(Request $request)
    {
        $data = $request->validate([
            'randevu_periyodu' => ['required', 'integer', 'in:10,15,20,30,45,60'],
            'randevu_onay_tipi' => ['required', 'in:manuel,otomatik'],
            'en_erken_randevu_saati' => ['required', 'integer', 'min:0', 'max:168'],
            'en_gec_randevu_gunu' => ['required', 'integer', 'min:0', 'max:365'],
            'randevu_iptal_aktif_mi' => ['nullable'],
            'iptal_saat_limiti' => ['required', 'integer', 'min:0', 'max:168'],
            'gunluk_maksimum_randevu' => ['required', 'integer', 'min:0', 'max:200'],
            'aktif_mi' => ['nullable'],
            'online_randevu_aktif' => ['nullable'],
            'yuzyuze_randevu_aktif' => ['nullable'],
            'email_bildirimleri' => ['nullable'],
            'sms_bildirimleri' => ['nullable'],
        ]);

        $payload = [
            'randevu_periyodu' => (int) $data['randevu_periyodu'],
            'randevu_onay_tipi' => $data['randevu_onay_tipi'],
            'en_erken_randevu_saati' => (int) $data['en_erken_randevu_saati'],
            'en_gec_randevu_gunu' => (int) $data['en_gec_randevu_gunu'],
            'randevu_iptal_aktif_mi' => $request->boolean('randevu_iptal_aktif_mi'),
            'iptal_saat_limiti' => (int) $data['iptal_saat_limiti'],
            'gunluk_maksimum_randevu' => (int) $data['gunluk_maksimum_randevu'],
            'aktif_mi' => $request->boolean('aktif_mi'),
            'online_randevu_aktif' => $request->boolean('online_randevu_aktif'),
            'yuzyuze_randevu_aktif' => $request->boolean('yuzyuze_randevu_aktif'),
            'email_bildirimleri' => $request->boolean('email_bildirimleri'),
            'sms_bildirimleri' => $request->boolean('sms_bildirimleri'),
        ];

        try {
            $this->api->put('/randevu-ayarlari', $payload);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Ayarlar kaydedildi — ana platform paneli ile senkron.');
    }

    public function calismaSaatleriKaydet(Request $request)
    {
        $rows = [];

        // Main-site form: saatler[{id|gun}][gun|aktif_mi|mesai_baslangic|...]
        if ($request->has('saatler') && is_array($request->input('saatler'))) {
            foreach ($request->input('saatler') as $key => $row) {
                $gun = (int) ($row['gun'] ?? $key);
                if ($gun < 1 || $gun > 7) {
                    continue;
                }
                $rows[] = [
                    'gun' => $gun,
                    'aktif_mi' => ! empty($row['aktif_mi']),
                    'mesai_baslangic' => isset($row['mesai_baslangic']) ? substr((string) $row['mesai_baslangic'], 0, 5) : '09:00',
                    'mesai_bitis' => isset($row['mesai_bitis']) ? substr((string) $row['mesai_bitis'], 0, 5) : '17:00',
                    'ogle_arasi_aktif_mi' => ! empty($row['ogle_arasi_aktif_mi']),
                    'ogle_baslangic' => ! empty($row['ogle_baslangic']) ? substr((string) $row['ogle_baslangic'], 0, 5) : null,
                    'ogle_bitis' => ! empty($row['ogle_bitis']) ? substr((string) $row['ogle_bitis'], 0, 5) : null,
                ];
            }
        }

        // Ensure all 7 days exist
        $byGun = collect($rows)->keyBy('gun');
        $rows = [];
        for ($gun = 1; $gun <= 7; $gun++) {
            $rows[] = $byGun->get($gun) ?? [
                'gun' => $gun,
                'aktif_mi' => false,
                'mesai_baslangic' => '09:00',
                'mesai_bitis' => '17:00',
                'ogle_arasi_aktif_mi' => false,
                'ogle_baslangic' => null,
                'ogle_bitis' => null,
            ];
        }

        try {
            $this->api->put('/calisma-saatleri', ['saatler' => array_values($rows)]);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Çalışma saatleri güncellendi.');
    }
}
