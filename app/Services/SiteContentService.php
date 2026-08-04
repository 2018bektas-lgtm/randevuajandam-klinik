<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Klinik web sitesi içerik = platform clinic public API + yerel CMS.
 */
class SiteContentService
{
    public function __construct(protected PlatformApiClient $api) {}

    /**
     * Unified bundle for views — uses $doktor key for layout compatibility
     * but represents the clinic brand + multi-doctor data.
     */
    public function doktor(): array
    {
        return $this->klinik();
    }

    public function klinik(): array
    {
        if (! $this->api->isConfigured()) {
            return array_merge($this->emptySkeleton(), [
                'api_synced' => false,
                'api_error' => 'Klinik API anahtarı yapılandırılmamış. Kurumsal panelden key girin.',
                'ad_soyad' => 'Klinik',
                'klinik_adi' => 'Klinik',
            ]);
        }

        try {
            $data = Cache::remember($this->cacheKey(), 20, function () {
                $profile = $this->api->publicGet('/profile')['data'] ?? [];
                $doctors = $this->api->publicGet('/doctors')['data'] ?? [];
                $services = $this->api->publicGet('/services')['data'] ?? [];
                $content = $this->api->publicGet('/site-content')['data'] ?? [];

                if (empty($profile) || empty($profile['id'])) {
                    throw new \RuntimeException('Klinik API profili boş.');
                }

                return $this->fromApi($profile, is_array($doctors) ? $doctors : [], is_array($services) ? $services : [], $content);
            });

            return $this->applyLocalSettings($data);
        } catch (Throwable $e) {
            Log::warning('kliniksitesi API failed: '.$e->getMessage());

            return $this->applyLocalSettings(array_merge($this->emptySkeleton(), [
                'api_synced' => false,
                'api_error' => $e->getMessage(),
                'ad_soyad' => 'Klinik',
                'klinik_adi' => 'Klinik',
                'kisa_bio' => 'Ana sunucu verisi alınamadı. API anahtarını ve platformu kontrol edin.',
            ]));
        }
    }

    public function forgetCache(): void
    {
        Cache::forget('kliniksitesi.profile.v1');
        try {
            $key = (string) config('randevu_api.api_key', '');
            if ($key !== '') {
                Cache::forget('kliniksitesi.profile.v1.'.md5($key));
            }
        } catch (Throwable) {
        }
    }

    protected function cacheKey(): string
    {
        return 'kliniksitesi.profile.v1.'.md5((string) config('randevu_api.api_key', 'none'));
    }

    protected function emptySkeleton(): array
    {
        return [
            'is_klinik' => true,
            'unvan' => '',
            'ad_soyad' => '',
            'klinik_adi' => '',
            'uzmanlik' => 'Klinik',
            'branslar' => [],
            'slogan' => '',
            'kisa_bio' => '',
            'bio' => '',
            'bio_html' => '',
            'telefon' => '',
            'telefon_raw' => '',
            'whatsapp' => '',
            'e_posta' => '',
            'adres' => '',
            'il' => '',
            'ilce' => '',
            'profil_resmi' => null,
            'logo' => null,
            'favicon' => null,
            'maps_embed' => '',
            'calisma_saatleri' => [],
            'sosyal' => [],
            'istatistikler' => [],
            'slider' => [],
            'hekimler' => [],
            'hizmetler' => [],
            'bloglar' => [],
            'sss' => [],
            'galeri' => [],
            'yorumlar' => [],
            'ozellikler' => [],
            'surec' => [],
            'features' => [],
            'api_synced' => false,
        ];
    }

    protected function fromApi(array $profile, array $doctors, array $services, array $content): array
    {
        $out = $this->emptySkeleton();
        $out['api_synced'] = true;
        $out['id'] = $profile['id'] ?? null;
        $features = $profile['features'] ?? $profile['paket_ozellikleri'] ?? $content['features'] ?? [];
        $out['features'] = is_array($features) ? array_values(array_filter(array_map('strval', $features))) : [];
        $out['klinik_adi'] = (string) ($profile['ad'] ?? 'Klinik');
        $out['ad_soyad'] = $out['klinik_adi']; // layout brand
        $out['unvan'] = '';
        $out['telefon'] = (string) ($profile['telefon'] ?? '');
        $out['e_posta'] = (string) ($profile['e_posta'] ?? '');
        $out['adres'] = (string) ($profile['adres'] ?? '');
        $out['il'] = (string) ($profile['il'] ?? '');
        $out['ilce'] = (string) ($profile['ilce'] ?? '');
        $out['bio'] = strip_tags((string) ($profile['aciklama'] ?? ''));
        $out['bio_html'] = (string) ($profile['aciklama'] ?? '');
        $out['kisa_bio'] = Str::limit($out['bio'], 220);
        $out['slogan'] = 'Uzman kadro ile güvenilir sağlık hizmeti';
        $out['uzmanlik'] = 'Çok branşlı klinik';
        $out['logo'] = $profile['logo'] ?? null;
        $out['profil_resmi'] = $profile['logo'] ?? null;

        if ($out['telefon'] !== '') {
            $raw = preg_replace('/\D+/', '', $out['telefon']) ?: '';
            $out['telefon_raw'] = $raw;
            $wa = ltrim($raw, '0');
            if (! str_starts_with($wa, '90') && strlen($wa) === 10) {
                $wa = '90'.$wa;
            }
            $out['whatsapp'] = $wa;
        }

        if (! empty($profile['enlem']) && ! empty($profile['boylam'])) {
            $out['maps_embed'] = 'https://maps.google.com/maps?q='.urlencode($profile['enlem'].','.$profile['boylam']).'&z=15&output=embed';
        } elseif ($out['adres'] !== '') {
            $out['maps_embed'] = 'https://maps.google.com/maps?q='.urlencode($out['adres']).'&z=15&output=embed';
        }

        $cs = $profile['calisma_saatleri'] ?? [];
        if (is_array($cs)) {
            // Normalize associative or list
            $out['calisma_saatleri'] = $cs;
        }

        $sosyal = $profile['sosyal'] ?? [];
        if (is_array($sosyal)) {
            $out['sosyal'] = array_filter($sosyal, fn ($v) => filled($v));
        }

        $out['hekimler'] = collect($doctors)->map(function ($d) {
            $d = is_array($d) ? $d : (array) $d;
            $slug = $d['slug'] ?? Str::slug(($d['ad_soyad'] ?? 'hekim').'-'.($d['id'] ?? uniqid()));

            return [
                'id' => $d['id'] ?? null,
                'slug' => $slug,
                'ad_soyad' => $d['ad_soyad'] ?? '',
                'unvan' => $d['unvan'] ?? '',
                'uzmanlik' => $d['uzmanlik_alani'] ?? '',
                'branslar' => $d['branslar'] ?? [],
                'profil_resmi' => $d['profil_resmi'] ?? null,
                'kisa_bio' => $d['kisa_bio'] ?? '',
                'randevuya_acik_mi' => (bool) ($d['randevuya_acik_mi'] ?? true),
            ];
        })->values()->all();

        $fallbackImgs = [
            'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=900&q=80',
            'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=900&q=80',
        ];
        $out['hizmetler'] = collect($services)->values()->map(function ($h, $i) use ($fallbackImgs) {
            $h = is_array($h) ? $h : (array) $h;
            $baslik = (string) ($h['ad'] ?? $h['baslik'] ?? 'Hizmet');
            $slug = $h['slug'] ?? Str::slug($baslik) ?: ('hizmet-'.($h['id'] ?? $i));

            return [
                'id' => $h['id'] ?? null,
                'baslik' => $baslik,
                'kisa' => Str::limit(strip_tags((string) ($h['aciklama'] ?? '')), 120),
                'aciklama' => $h['aciklama'] ?? '',
                'sure' => isset($h['sure']) ? ((int) $h['sure']).' dk' : null,
                'fiyat' => isset($h['fiyat']) && $h['fiyat'] !== null && $h['fiyat'] !== ''
                    ? number_format((float) $h['fiyat'], 0, ',', '.').' ₺'
                    : null,
                'slug' => $slug,
                'image' => ! empty($h['resim']) ? $h['resim'] : $fallbackImgs[$i % count($fallbackImgs)],
                'doktor_id' => $h['doktor_id'] ?? null,
                'doktor_adi' => $h['doktor_adi'] ?? null,
                'madde' => [],
            ];
        })->values()->all();

        if (! empty($content['bloglar'])) {
            $out['bloglar'] = collect($content['bloglar'])->map(function ($b) {
                $b = is_array($b) ? $b : (array) $b;
                $plain = strip_tags((string) ($b['icerik'] ?? ''));

                return [
                    'slug' => $b['slug'] ?? ('yazi-'.($b['id'] ?? uniqid())),
                    'baslik' => $b['baslik'] ?? '',
                    'ozet' => $b['ozet'] ?? Str::limit($plain, 160),
                    'tarih' => $b['tarih'] ?? '',
                    'okuma' => max(3, (int) ceil(max(1, str_word_count($plain)) / 180)).' dk',
                    'kategori' => 'Blog',
                    'image' => $b['resim'] ?? 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=1000&q=80',
                    'icerik' => array_values(array_filter(array_map('trim', preg_split('/\n\s*\n/', $plain) ?: [$plain]))),
                    'icerik_html' => $b['icerik'] ?? '',
                ];
            })->values()->all();
        }

        if (! empty($content['faqs'])) {
            $out['sss'] = collect($content['faqs'])->map(fn ($f) => [
                'soru' => is_array($f) ? ($f['soru'] ?? '') : '',
                'cevap' => is_array($f) ? ($f['cevap'] ?? '') : '',
            ])->filter(fn ($f) => $f['soru'] !== '')->values()->all();
        }

        if (! empty($content['galeri'])) {
            $out['galeri'] = collect($content['galeri'])->map(fn ($g) => [
                'baslik' => is_array($g) ? ($g['baslik'] ?? 'Galeri') : 'Galeri',
                'etiket' => 'Klinik',
                'image' => is_array($g) ? ($g['image'] ?? null) : null,
            ])->filter(fn ($g) => ! empty($g['image']))->values()->all();
        }

        if (! empty($content['yorumlar'])) {
            $out['yorumlar'] = collect($content['yorumlar'])->map(fn ($y) => [
                'ad' => is_array($y) ? ($y['ad'] ?? 'Hasta') : 'Hasta',
                'metin' => is_array($y) ? ($y['metin'] ?? '') : '',
                'puan' => is_array($y) ? (int) ($y['puan'] ?? 5) : 5,
                'hizmet' => 'Değerlendirme',
            ])->filter(fn ($y) => $y['metin'] !== '')->values()->all();
        }

        $out['istatistikler'] = array_values(array_filter([
            ['deger' => count($out['hekimler']), 'suffix' => '', 'etiket' => 'Uzman Hekim', 'aciklama' => 'Kadromuz'],
            ['deger' => count($out['hizmetler']), 'suffix' => '', 'etiket' => 'Hizmet', 'aciklama' => 'Aktif'],
            ['deger' => count($out['yorumlar']), 'suffix' => '', 'etiket' => 'Yorum', 'aciklama' => 'Danışan'],
            ['deger' => count($out['bloglar']), 'suffix' => '', 'etiket' => 'Blog', 'aciklama' => 'İçerik'],
        ]));

        $out['surec'] = [
            ['adim' => '01', 'baslik' => 'Hekim seçin', 'aciklama' => 'Uzman kadromuzdan size uygun hekimi seçin.'],
            ['adim' => '02', 'baslik' => 'Randevu oluşturun', 'aciklama' => 'Uygun gün ve saati online talep edin.'],
            ['adim' => '03', 'baslik' => 'Onay', 'aciklama' => 'Klinik ekibimiz talebinizi inceler ve bilgilendirir.'],
            ['adim' => '04', 'baslik' => 'Muayene & takip', 'aciklama' => 'Planınıza uygun tedavi ve kontrol süreci.'],
        ];
        $out['ozellikler'] = [
            ['baslik' => 'Çok hekimli kadro', 'aciklama' => 'Branşınıza uygun uzman hekimle randevu.'],
            ['baslik' => 'Merkezi randevu', 'aciklama' => 'Tüm randevular klinik paneline anında düşer.'],
            ['baslik' => 'Modern klinik sitesi', 'aciklama' => 'Kurumsal paket ile tam entegre vitrin.'],
        ];

        return $out;
    }

    protected function applyLocalSettings(array $out): array
    {
        try {
            $settings = app(SiteSettingsService::class)->frontendBundle();
        } catch (Throwable) {
            return $out;
        }

        $genel = $settings['genel'] ?? [];
        $menu = $settings['menu'] ?? [];
        $footer = $settings['footer'] ?? [];
        $slider = $settings['slider'] ?? [];
        $anasayfa = $settings['anasayfa'] ?? [];
        $seo = $settings['seo'] ?? [];
        $iletisim = $settings['iletisim'] ?? [];

        $out['site_settings'] = $settings;

        if (! empty($genel['slogan_override'])) {
            $out['slogan'] = $genel['slogan_override'];
        }
        if (! empty($genel['footer_metin'])) {
            $out['footer_metin'] = $genel['footer_metin'];
        }
        if (! empty($genel['tema_renk'])) {
            $out['tema_renk'] = $genel['tema_renk'];
        }
        $temaId = (string) ($genel['tema_id'] ?? config('themes.default', 'klasik'));
        $tema = resolve_site_theme($temaId);
        $out['tema_id'] = $tema['id'];
        $out['tema'] = $tema;
        if (empty($genel['tema_renk']) && ! empty($tema['renk'])) {
            $out['tema_renk'] = $tema['renk'];
        }
        if (! empty($genel['site_baslik_ek'])) {
            $out['site_baslik_ek'] = $genel['site_baslik_ek'];
        }
        if (! empty($genel['vitrin_badge'])) {
            $out['vitrin_badge'] = $genel['vitrin_badge'];
        }
        if (! empty($genel['logo_url'])) {
            $out['logo'] = $genel['logo_url'];
        }
        $out['favicon'] = $genel['favicon_url'] ?? null;
        $out['whatsapp_goster'] = (bool) ($genel['whatsapp_goster'] ?? true);
        $out['hekim_girisi_goster'] = (bool) ($genel['hekim_girisi_goster'] ?? true);

        if (! empty($menu['items']) && is_array($menu['items'])) {
            $out['menu'] = collect($menu['items'])
                ->filter(fn ($i) => ! empty($i['aktif']))
                ->sortBy('sira')
                ->values()
                ->all();
        }

        if (! empty($footer['items']) && is_array($footer['items'])) {
            $out['footer_menu'] = collect($footer['items'])
                ->filter(fn ($i) => ! empty($i['aktif']))
                ->sortBy('sira')
                ->values()
                ->all();
        }

        if (! empty($slider['slides']) && is_array($slider['slides'])) {
            $out['slider'] = array_values(array_filter(
                $slider['slides'],
                fn ($s) => ! empty($s['baslik']) || ! empty($s['image'])
            ));
        } else {
            $out['slider'] = [];
        }

        $defaultBolumler = [
            'slider' => true, 'istatistik' => true, 'ozellikler' => true, 'hakkimda' => true,
            'hekimler' => true, 'hizmetler' => true, 'surec' => true, 'galeri' => true,
            'yorumlar' => true, 'blog' => true, 'cta' => true,
        ];
        $out['anasayfa_bolumler'] = array_merge($defaultBolumler, $anasayfa['bolumler'] ?? []);
        $out['anasayfa_sira'] = ! empty($anasayfa['sira']) && is_array($anasayfa['sira'])
            ? array_values($anasayfa['sira'])
            : array_keys($defaultBolumler);
        $out['bolum_basliklar'] = $anasayfa['basliklar'] ?? [];
        $out['seo'] = $seo;
        $out['iletisim_sayfa'] = $iletisim;

        return $out;
    }
}
