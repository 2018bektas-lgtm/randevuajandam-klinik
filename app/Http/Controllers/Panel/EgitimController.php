<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use App\Services\SiteContentService;
use App\Support\ApiData;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Hekim paneli — eğitim CRUD + başvurular (Platform API senkron).
 * Ana site hekim.egitim paneli ile aynı işlevler.
 */
class EgitimController extends Controller
{
    public function __construct(
        protected PlatformApiClient $api,
        protected SiteContentService $siteContent,
    ) {}

    public function index(Request $request)
    {
        try {
            $res = $this->api->get('/egitimler', [
                'page' => (int) $request->input('page', 1),
                'per_page' => 12,
            ]);
            $items = $res['data']['items'] ?? [];
            $meta = $res['data']['meta'] ?? [];
        } catch (RuntimeException $e) {
            return $this->apiFail($e);
        }

        $egitimler = ApiData::paginate($items, $meta, 12);

        return view('panel.egitim.index', compact('egitimler'));
    }

    public function create()
    {
        return view('panel.egitim.form', ['egitim' => null]);
    }

    public function store(Request $request)
    {
        $this->validateLocal($request);

        try {
            $fields = $this->payload($request);
            $files = $request->hasFile('kapak') ? ['kapak' => $request->file('kapak')] : [];
            $res = $this->api->postMultipart('/egitimler', $fields, $files);
            $this->siteContent->forgetCache();
            $id = (int) ($res['data']['id'] ?? 0);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('hata', $e->getMessage());
        }

        if ($id > 0) {
            return redirect()
                ->route('panel.egitimler.edit', $id)
                ->with('basari', 'Eğitim kaydedildi. Form alanlarını düzenleyip yayınlayabilirsiniz.');
        }

        return redirect()->route('panel.egitimler.index')->with('basari', 'Eğitim kaydedildi.');
    }

    public function edit(int $id)
    {
        try {
            $res = $this->api->get('/egitimler/'.$id);
            $data = $res['data'] ?? null;
            if (! $data) {
                return redirect()->route('panel.egitimler.index')->with('hata', 'Eğitim bulunamadı.');
            }
        } catch (RuntimeException $e) {
            return $this->apiFail($e);
        }

        $egitim = $this->hydrateEgitim($data);

        return view('panel.egitim.form', compact('egitim'));
    }

    public function update(Request $request, int $id)
    {
        $this->validateLocal($request);

        try {
            $fields = $this->payload($request);
            $fields['_method'] = 'PUT';
            $files = $request->hasFile('kapak') ? ['kapak' => $request->file('kapak')] : [];
            $this->api->postMultipart('/egitimler/'.$id, $fields, $files);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->withInput()->with('hata', $e->getMessage());
        }

        return redirect()->back()->with('basari', 'Eğitim güncellendi.');
    }

    public function destroy(int $id)
    {
        try {
            $this->api->delete('/egitimler/'.$id);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return redirect()->route('panel.egitimler.index')->with('basari', 'Eğitim silindi.');
    }

    public function basvurularTumu(Request $request)
    {
        try {
            $res = $this->api->get('/egitimler/basvurular', $request->only(['page', 'durum', 'ucret', 'egitim_id', 'q']));
            $payload = $res['data'] ?? [];
        } catch (RuntimeException $e) {
            return $this->apiFail($e);
        }

        return view('panel.egitim.basvurular', $this->basvuruViewData($payload, true));
    }

    public function basvurular(Request $request, int $id)
    {
        try {
            $res = $this->api->get('/egitimler/'.$id.'/basvurular', $request->only(['page', 'durum', 'ucret', 'q']));
            $payload = $res['data'] ?? [];
        } catch (RuntimeException $e) {
            return $this->apiFail($e);
        }

        return view('panel.egitim.basvurular', $this->basvuruViewData($payload, false));
    }

    public function basvuruDurum(Request $request, int $id, int $basvuruId)
    {
        $request->validate([
            'durum' => ['required', 'in:beklemede,onaylandi,reddedildi,iptal'],
            'hekim_notu' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->api->post('/egitimler/'.$id.'/basvurular/'.$basvuruId.'/durum', [
                'durum' => $request->input('durum'),
                'hekim_notu' => $request->input('hekim_notu'),
            ]);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Başvuru durumu güncellendi.');
    }

    public function basvuruOdeme(Request $request, int $id, int $basvuruId)
    {
        $request->validate([
            'odenen_tutar' => ['required', 'numeric', 'min:0.01'],
            'odeme_yontemi' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            $this->api->post('/egitimler/'.$id.'/basvurular/'.$basvuruId.'/odeme', [
                'odenen_tutar' => $request->input('odenen_tutar'),
                'odeme_yontemi' => $request->input('odeme_yontemi'),
            ]);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', 'Ödeme kaydedildi ve finans gelirlerine yansıtıldı (kategori: Eğitim).');
    }

    protected function validateLocal(Request $request): void
    {
        $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'ozet' => ['nullable', 'string', 'max:2000'],
            'icerik' => ['nullable', 'string'],
            'kapak' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'tip' => ['required', 'in:yuz_yuze,online,hibrit'],
            'baslangic_at' => ['nullable', 'date'],
            'bitis_at' => ['nullable', 'date'],
            'mekan' => ['nullable', 'string', 'max:255'],
            'online_url' => ['nullable', 'string', 'max:500'],
            'fiyat' => ['nullable', 'numeric', 'min:0'],
            'odeme_notu' => ['nullable', 'string', 'max:500'],
            'kontenjan' => ['nullable', 'integer', 'min:1'],
            'basvuru_bitis_at' => ['nullable', 'date'],
            'durum' => ['required', 'in:taslak,yayinda,arsiv'],
            'meta_baslik' => ['nullable', 'string', 'max:255'],
            'meta_aciklama' => ['nullable', 'string', 'max:500'],
            'meta_anahtar_kelimeler' => ['nullable', 'string', 'max:255'],
            'sira' => ['nullable', 'integer', 'min:0'],
            'alanlar' => ['nullable', 'array'],
        ], [
            'baslik.required' => 'Eğitim başlığı zorunludur.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(Request $request): array
    {
        $fields = [
            'baslik' => $request->input('baslik'),
            'ozet' => $request->input('ozet'),
            'icerik' => $request->input('icerik'),
            'tip' => $request->input('tip'),
            'baslangic_at' => $request->input('baslangic_at') ?: null,
            'bitis_at' => $request->input('bitis_at') ?: null,
            'mekan' => $request->input('mekan'),
            'online_url' => $request->input('online_url'),
            'fiyat' => $request->input('fiyat') !== null && $request->input('fiyat') !== ''
                ? $request->input('fiyat')
                : null,
            'odeme_notu' => $request->input('odeme_notu'),
            'kontenjan' => $request->input('kontenjan') !== null && $request->input('kontenjan') !== ''
                ? $request->input('kontenjan')
                : null,
            'basvuru_bitis_at' => $request->input('basvuru_bitis_at') ?: null,
            'durum' => $request->input('durum'),
            'meta_baslik' => $request->input('meta_baslik'),
            'meta_aciklama' => $request->input('meta_aciklama'),
            'meta_anahtar_kelimeler' => $request->input('meta_anahtar_kelimeler'),
            'sira' => (int) $request->input('sira', 0),
            'basvuru_acik_mi' => $request->boolean('basvuru_acik_mi'),
            'kapak_sil' => $request->boolean('kapak_sil'),
            'alanlar' => $request->input('alanlar', []),
        ];

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function hydrateEgitim(array $data): object
    {
        $egitim = ApiData::obj($data);

        // Form view expects formAlanlari collection-like
        $alanlar = $data['form_alanlari'] ?? [];
        $egitim->formAlanlari = collect($alanlar)->map(function ($a) {
            $o = ApiData::obj($a);
            // secenekler as string for form JS (API already flattens to newline string for panel)
            if (is_array($o->secenekler ?? null)) {
                $o->secenekler = implode("\n", $o->secenekler);
            }

            return $o;
        });

        return $egitim;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function basvuruViewData(array $payload, bool $tumu): array
    {
        $items = $payload['items'] ?? [];
        $meta = $payload['meta'] ?? [];
        $basvurular = ApiData::paginate($items, $meta, 20);

        // cevaplar associative map must stay array (ApiData deep-converts nested arrays to objects)
        $basvurular->getCollection()->transform(function ($b) use ($items) {
            $raw = collect($items)->firstWhere('id', $b->id ?? null);
            $cevaplar = is_array($raw) ? ($raw['cevaplar'] ?? []) : [];
            $b->cevaplar = is_array($cevaplar) ? $cevaplar : (array) $cevaplar;

            return $b;
        });

        $egitim = ! empty($payload['egitim']) ? ApiData::obj($payload['egitim']) : null;
        $egitimler = collect($payload['egitimler'] ?? [])->map(fn ($e) => ApiData::obj($e));

        $alanEtiketleri = collect($payload['alan_etiketleri'] ?? [])->mapWithKeys(function ($a, $key) {
            $o = ApiData::obj($a);

            return [(string) ($o->id ?? $key) => $o];
        });

        return [
            'egitim' => $egitim,
            'basvurular' => $basvurular,
            'egitimler' => $egitimler,
            'ozet' => $payload['ozet'] ?? [
                'toplam' => 0,
                'beklemede' => 0,
                'onaylandi' => 0,
                'odeme_bekleyen' => 0,
            ],
            'alanEtiketleri' => $alanEtiketleri,
            'tumu' => $tumu || (bool) ($payload['tumu'] ?? false),
        ];
    }

    protected function apiFail(RuntimeException $e)
    {
        if ($e->getCode() === 401) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        return redirect()->route('panel.dashboard')->with('hata', $e->getMessage());
    }
}
