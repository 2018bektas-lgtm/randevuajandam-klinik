<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use App\Services\SiteContentService;
use App\Support\ApiData;
use Illuminate\Http\Request;
use RuntimeException;

class ProfilController extends Controller
{
    public function __construct(
        protected PlatformApiClient $api,
        protected SiteContentService $siteContent,
    ) {}

    public function edit()
    {
        try {
            $res = $this->api->get('/auth/me');
            $raw = $res['data'] ?? [];
            $this->api->setUser(is_array($raw) ? $raw : (array) $raw);
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $doktor = $this->mapDoktor($raw);

        // Ana site profil formundaki unvan listesi
        $unvanlar = collect([
            'Uzm. Dr.', 'Op. Dr.', 'Prof. Dr.', 'Doç. Dr.', 'Dr.',
            'Dt.', 'Uzm. Dt.', 'Uzm. Psk.', 'Psk.', 'Dyt.', 'Fzt.',
        ])->map(fn ($ad) => (object) ['ad' => $ad]);

        // Mevcut unvan listede yoksa ekle
        if (! empty($doktor->unvan) && ! $unvanlar->contains(fn ($u) => $u->ad === $doktor->unvan)) {
            $unvanlar->prepend((object) ['ad' => $doktor->unvan]);
        }

        return view('panel.profil', compact('doktor', 'unvanlar'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'ad_soyad' => ['required', 'string', 'max:255'],
            'unvan' => ['nullable', 'string', 'max:100'],
            'telefon' => ['required', 'string', 'max:40'],
            'uzmanlik_alani' => ['nullable', 'string', 'max:255'],
            'biyografi' => ['nullable', 'string', 'max:10000'],
            'adres' => ['nullable', 'string', 'max:500'],
            'klinik_adi' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'youtube' => ['nullable', 'string', 'max:255'],
            'web_sitesi' => ['nullable', 'string', 'max:255'],
            'enlem' => ['nullable', 'numeric'],
            'boylam' => ['nullable', 'numeric'],
            'il' => ['nullable', 'string', 'max:100'],
            'ilce' => ['nullable', 'string', 'max:100'],
            'profil_resmi' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        // API only accepts known profile fields
        $payload = collect($data)->only([
            'ad_soyad', 'unvan', 'telefon', 'uzmanlik_alani', 'biyografi', 'adres',
            'klinik_adi', 'instagram', 'facebook', 'twitter', 'linkedin', 'youtube',
            'web_sitesi', 'enlem', 'boylam',
        ])->filter(fn ($v) => $v !== null)->all();

        try {
            if ($request->hasFile('profil_resmi')) {
                $res = $this->api->postMultipart('/profile', $payload, [
                    'profil_resmi' => $request->file('profil_resmi'),
                ]);
            } else {
                $res = $this->api->put('/profile', $payload);
            }
            $me = $this->api->get('/auth/me');
            $this->api->setUser($me['data'] ?? []);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->withInput()->with('hata', $e->getMessage());
        }

        return back()->with('basari', $res['message'] ?? 'Profil güncellendi.');
    }

    public function hakkimda()
    {
        try {
            $res = $this->api->get('/auth/me');
            $raw = $res['data'] ?? [];
            $this->api->setUser(is_array($raw) ? $raw : (array) $raw);
            $branslarRaw = $this->api->get('/branslar')['data'] ?? [];
        } catch (RuntimeException $e) {
            return redirect()->route('panel.giris')->with('hata', $e->getMessage());
        }

        $doktor = $this->mapDoktor($raw);
        $branslar = collect($branslarRaw)->map(fn ($b) => (object) (is_array($b) ? $b : (array) $b));

        return view('panel.hakkimda', compact('doktor', 'branslar'));
    }

    public function hakkimdaGuncelle(Request $request)
    {
        $data = $request->validate([
            'biyografi' => ['nullable', 'string', 'max:10000'],
            'klinik_adi' => ['nullable', 'string', 'max:255'],
            'uzmanlik_alani' => ['nullable', 'string', 'max:500'],
            'mezuniyet' => ['nullable', 'array'],
            'mezuniyet.*' => ['nullable', 'string', 'max:255'],
            'branslar' => ['nullable', 'array'],
            'branslar.*' => ['integer'],
        ]);

        $payload = [
            'biyografi' => $data['biyografi'] ?? null,
            'klinik_adi' => $data['klinik_adi'] ?? null,
            'uzmanlik_alani' => $data['uzmanlik_alani'] ?? null,
            'mezuniyet' => array_values(array_filter($data['mezuniyet'] ?? [], fn ($x) => filled($x))),
            'branslar' => $data['branslar'] ?? [],
        ];

        try {
            $res = $this->api->put('/hakkimda', $payload);
            $me = $this->api->get('/auth/me');
            $this->api->setUser($me['data'] ?? []);
            $this->siteContent->forgetCache();
        } catch (RuntimeException $e) {
            return back()->withInput()->with('hata', $e->getMessage());
        }

        return back()->with('basari', $res['message'] ?? 'Hakkımda bilgileri güncellendi.');
    }

    public function sifreFormu()
    {
        return view('panel.sifre');
    }

    public function sifreGuncelle(Request $request)
    {
        $data = $request->validate([
            'mevcut_sifre' => ['required', 'string'],
            'sifre' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $res = $this->api->put('/password', $data);
        } catch (RuntimeException $e) {
            return back()->with('hata', $e->getMessage());
        }

        return back()->with('basari', $res['message'] ?? 'Şifre güncellendi.');
    }

    /**
     * Blade (ana site kopyası) object erişimi bekliyor ($doktor->ad_soyad).
     */
    protected function mapDoktor(array|object $raw): object
    {
        $d = is_array($raw) ? $raw : (array) $raw;
        $obj = ApiData::obj($d);

        // Nested il/ilce for blade: $doktor->il?->ad
        $ilAd = $d['il'] ?? null;
        $ilceAd = $d['ilce'] ?? null;
        if (is_string($ilAd)) {
            $obj->il = (object) ['ad' => $ilAd];
        } elseif (is_array($ilAd)) {
            $obj->il = (object) $ilAd;
        } else {
            $obj->il = null;
        }
        if (is_string($ilceAd)) {
            $obj->ilce = (object) ['ad' => $ilceAd];
        } elseif (is_array($ilceAd)) {
            $obj->ilce = (object) $ilceAd;
        } else {
            $obj->ilce = null;
        }

        // Ensure string fields exist
        foreach (['ad_soyad', 'unvan', 'e_posta', 'telefon', 'adres', 'instagram', 'facebook', 'twitter', 'linkedin', 'youtube', 'web_sitesi', 'uzmanlik_alani', 'biyografi', 'klinik_adi', 'profil_resmi'] as $f) {
            if (! isset($obj->$f)) {
                $obj->$f = $d[$f] ?? null;
            }
        }

        $mez = $d['mezuniyet'] ?? [];
        if (is_string($mez)) {
            $decoded = json_decode($mez, true);
            $mez = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode("\n", $mez)));
        }
        $obj->mezuniyet = is_array($mez) ? array_values($mez) : [];

        $branslar = $d['branslar'] ?? [];
        $obj->branslar = collect(is_array($branslar) ? $branslar : [])->map(function ($b) {
            $b = is_array($b) ? $b : (array) $b;

            return (object) $b;
        })->values();

        return $obj;
    }
}
