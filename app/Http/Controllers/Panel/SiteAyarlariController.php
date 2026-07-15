<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\SiteHomepageSection;
use App\Models\SiteMenuItem;
use App\Models\SiteSliderSlide;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;

class SiteAyarlariController extends Controller
{
    public function __construct(protected SiteSettingsService $settings) {}

    public function index()
    {
        return redirect()->route('panel.site-ayarlari.genel');
    }

    public function genel()
    {
        $logo = (string) $this->settings->option('site_logo', '');
        $favicon = (string) $this->settings->option('site_favicon', '');

        return view('panel.site-ayarlari.genel', [
            'group' => 'genel',
            'ayarlar' => [
                'site_baslik_ek' => $this->settings->option('site_baslik_ek', ''),
                'slogan_override' => $this->settings->option('slogan_override', ''),
                'footer_metin' => $this->settings->option('footer_metin', ''),
                'tema_renk' => $this->settings->option('tema_renk', '#0b5ed7'),
                'tema_id' => $this->settings->option('tema_id', (string) config('themes.default', 'klasik')),
                'vitrin_badge' => $this->settings->option('vitrin_badge', ''),
                'logo' => $logo,
                'logo_url' => $this->settings->mediaUrl($logo),
                'favicon' => $favicon,
                'favicon_url' => $this->settings->mediaUrl($favicon),
                'whatsapp_goster' => $this->settings->boolOption('whatsapp_goster', true),
                'hekim_girisi_goster' => $this->settings->boolOption('hekim_girisi_goster', true),
            ],
        ]);
    }

    public function menu()
    {
        $this->syncSystemMenuItems();

        return view('panel.site-ayarlari.menu', [
            'group' => 'menu',
            'items' => $this->settings->menuItems(),
            'pageOptions' => $this->internalPageOptions(),
        ]);
    }

    public function slider()
    {
        $slides = $this->settings->sliderSlides()->map(function (SiteSliderSlide $s) {
            $meta = is_array($s->meta) ? $s->meta : [];
            $imageUrl = $this->settings->mediaUrl($s->image) ?: $s->image;

            // Panel JS için ek alanlar (JSON'a girsin)
            $s->setAttribute('image_url', $imageUrl);
            $s->setAttribute('cta_link_type', $meta['cta_link_type'] ?? $this->guessLinkType($s->cta_url));
            $s->setAttribute('cta_route', $meta['cta_route'] ?? $this->guessRoute($s->cta_url));
            $s->setAttribute('cta2_link_type', $meta['cta2_link_type'] ?? $this->guessLinkType($s->cta2_url));
            $s->setAttribute('cta2_route', $meta['cta2_route'] ?? $this->guessRoute($s->cta2_url));
            $s->setAttribute('baslik_vurgulu', $meta['baslik_vurgulu'] ?? '');
            $s->setAttribute('float_1_baslik', $meta['float_1_baslik'] ?? '');
            $s->setAttribute('float_1_aciklama', $meta['float_1_aciklama'] ?? '');
            $s->setAttribute('float_2_baslik', $meta['float_2_baslik'] ?? '');
            $s->setAttribute('float_2_aciklama', $meta['float_2_aciklama'] ?? '');
            $s->setAttribute('istatistikler', $meta['istatistikler'] ?? []);

            return $s;
        });

        return view('panel.site-ayarlari.slider', [
            'group' => 'slider',
            'slides' => $slides,
            'pageOptions' => $this->internalPageOptions(),
        ]);
    }

    public function anasayfa()
    {
        return view('panel.site-ayarlari.anasayfa', [
            'group' => 'anasayfa',
            'sections' => $this->settings->homepageSections(),
        ]);
    }

    public function seo()
    {
        return view('panel.site-ayarlari.seo', [
            'group' => 'seo',
            'ayarlar' => [
                'meta_baslik' => $this->settings->option('seo_meta_baslik', ''),
                'meta_aciklama' => $this->settings->option('seo_meta_aciklama', ''),
                'meta_anahtar' => $this->settings->option('seo_meta_anahtar', ''),
                'gtm_container_id' => $this->settings->option('seo_gtm_container_id', ''),
                'ga4_measurement_id' => $this->settings->option('seo_ga4_measurement_id', ''),
                'meta_pixel_id' => $this->settings->option('seo_meta_pixel_id', ''),
                'google_ads_id' => $this->settings->option('seo_google_ads_id', ''),
                'recaptcha_site_key' => $this->settings->option('seo_recaptcha_site_key', ''),
                'recaptcha_secret_key' => $this->settings->option('seo_recaptcha_secret_key', ''),
                'recaptcha_enabled' => $this->settings->option('seo_recaptcha_enabled', '1') !== '0',
            ],
        ]);
    }

    public function iletisim()
    {
        return view('panel.site-ayarlari.iletisim', [
            'group' => 'iletisim',
            'ayarlar' => [
                'baslik' => $this->settings->option('iletisim_baslik', ''),
                'alt_metin' => $this->settings->option('iletisim_alt_metin', ''),
                'form_goster' => $this->settings->boolOption('iletisim_form_goster', true),
                'harita_goster' => $this->settings->boolOption('iletisim_harita_goster', true),
                'saatler_goster' => $this->settings->boolOption('iletisim_saatler_goster', true),
            ],
        ]);
    }

    public function temalar()
    {
        $aktif = (string) $this->settings->option('tema_id', (string) config('themes.default', 'klasik'));
        $renk = (string) $this->settings->option('tema_renk', '');
        $resolved = resolve_site_theme($aktif);

        return view('panel.site-ayarlari.temalar', [
            'group' => 'temalar',
            'temalar' => site_themes(),
            'aktif_tema' => $resolved['id'],
            'tema_renk' => $renk !== '' ? $renk : ($resolved['renk'] ?? '#0b5ed7'),
            'premium_unlocked' => themes_premium_unlocked(),
        ]);
    }

    public function kaydetTema(Request $request)
    {
        $ids = array_keys(site_themes());
        $data = $request->validate([
            'tema_id' => ['required', 'string', 'in:'.implode(',', $ids)],
            'tema_renk' => ['nullable', 'string', 'max:20'],
            'renk_temadan' => ['nullable', 'boolean'],
        ]);

        if (! theme_is_available($data['tema_id'])) {
            return back()->with('hata', 'Bu premium tema klinik web sitesi paketinde yer alır. Lütfen paketinizi yükseltin veya ücretsiz bir tema seçin.');
        }

        $tema = resolve_site_theme($data['tema_id']);
        $renk = $data['tema_renk'] ?? null;
        if ($request->boolean('renk_temadan') || ! is_string($renk) || ! preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $renk)) {
            $renk = $tema['renk'] ?? '#0b5ed7';
        }

        $this->settings->setOptions([
            'tema_id' => $tema['id'],
            'tema_renk' => $renk,
        ]);

        return back()->with('basari', 'Tema uygulandı: '.$tema['ad']);
    }

    public function kaydetGenel(Request $request)
    {
        $request->validate([
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg,gif', 'max:4096'],
            'favicon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,ico,gif,svg', 'max:1024'],
            'tema_renk' => ['nullable', 'string', 'max:20'],
            'tema_id' => ['nullable', 'string', 'max:40'],
        ]);

        $temaId = (string) $request->input('tema_id', config('themes.default', 'klasik'));
        $tema = resolve_site_theme($temaId);
        $temaRenk = $request->input('tema_renk');
        if (! is_string($temaRenk) || ! preg_match('/^#[0-9A-Fa-f]{6}$/', $temaRenk)) {
            $temaRenk = $tema['renk'] ?? '#0b5ed7';
        }

        $this->settings->setOptions([
            'site_baslik_ek' => $request->input('site_baslik_ek', ''),
            'slogan_override' => $request->input('slogan_override', ''),
            'footer_metin' => $request->input('footer_metin', ''),
            'tema_renk' => $temaRenk,
            'tema_id' => $tema['id'],
            'vitrin_badge' => $request->input('vitrin_badge', ''),
            'whatsapp_goster' => $request->boolean('whatsapp_goster'),
            'hekim_girisi_goster' => $request->boolean('hekim_girisi_goster'),
        ]);

        // Logo
        if ($request->boolean('logo_sil')) {
            $this->settings->deleteMediaIfOwned($this->settings->option('site_logo', ''));
            $this->settings->setOption('site_logo', '');
        } elseif ($request->hasFile('logo')) {
            $old = $this->settings->option('site_logo', '');
            $path = $request->file('logo')->store('site', 'public');
            if ($path) {
                $this->settings->deleteMediaIfOwned($old);
                $this->settings->setOption('site_logo', $path);
            }
        }

        // Favicon
        if ($request->boolean('favicon_sil')) {
            $this->settings->deleteMediaIfOwned($this->settings->option('site_favicon', ''));
            $this->settings->setOption('site_favicon', '');
        } elseif ($request->hasFile('favicon')) {
            $old = $this->settings->option('site_favicon', '');
            $path = $request->file('favicon')->store('site', 'public');
            if ($path) {
                $this->settings->deleteMediaIfOwned($old);
                $this->settings->setOption('site_favicon', $path);
            }
        }

        return back()->with('basari', 'Genel ayarlar kaydedildi.');
    }

    public function kaydetSeo(Request $request)
    {
        $request->validate([
            'meta_baslik' => ['nullable', 'string', 'max:120'],
            'meta_aciklama' => ['nullable', 'string', 'max:300'],
            'meta_anahtar' => ['nullable', 'string', 'max:500'],
            'gtm_container_id' => ['nullable', 'string', 'max:40', 'regex:/^GTM-[A-Z0-9]+$/i'],
            'ga4_measurement_id' => ['nullable', 'string', 'max:40', 'regex:/^G-[A-Z0-9]+$/i'],
            'meta_pixel_id' => ['nullable', 'string', 'max:40', 'regex:/^[0-9]*$/'],
            'google_ads_id' => ['nullable', 'string', 'max:40', 'regex:/^AW-[0-9]+$/i'],
            'recaptcha_site_key' => ['nullable', 'string', 'max:100'],
            'recaptcha_secret_key' => ['nullable', 'string', 'max:100'],
            'recaptcha_enabled' => ['nullable', 'boolean'],
        ], [
            'gtm_container_id.regex' => 'GTM kodu GTM-XXXX formatında olmalı.',
            'ga4_measurement_id.regex' => 'GA4 kodu G-XXXX formatında olmalı.',
            'meta_pixel_id.regex' => 'Meta Pixel yalnızca rakam olmalı.',
            'google_ads_id.regex' => 'Google Ads AW-XXXXXXXXXX formatında olmalı.',
        ]);

        $this->settings->setOptions([
            'seo_meta_baslik' => $request->input('meta_baslik', ''),
            'seo_meta_aciklama' => $request->input('meta_aciklama', ''),
            'seo_meta_anahtar' => $request->input('meta_anahtar', ''),
            'seo_gtm_container_id' => strtoupper(trim((string) $request->input('gtm_container_id', ''))),
            'seo_ga4_measurement_id' => strtoupper(trim((string) $request->input('ga4_measurement_id', ''))),
            'seo_meta_pixel_id' => trim((string) $request->input('meta_pixel_id', '')),
            'seo_google_ads_id' => strtoupper(trim((string) $request->input('google_ads_id', ''))),
            'seo_recaptcha_site_key' => trim((string) $request->input('recaptcha_site_key', '')),
            'seo_recaptcha_secret_key' => trim((string) $request->input('recaptcha_secret_key', '')),
            'seo_recaptcha_enabled' => $request->boolean('recaptcha_enabled') ? '1' : '0',
        ]);

        return back()->with('basari', 'SEO, analitik ve reCAPTCHA ayarları kaydedildi.');
    }

    public function kaydetIletisim(Request $request)
    {
        $this->settings->setOptions([
            'iletisim_baslik' => $request->input('baslik', ''),
            'iletisim_alt_metin' => $request->input('alt_metin', ''),
            'iletisim_form_goster' => $request->boolean('form_goster'),
            'iletisim_harita_goster' => $request->boolean('harita_goster'),
            'iletisim_saatler_goster' => $request->boolean('saatler_goster'),
        ]);

        return back()->with('basari', 'İletişim sayfası ayarları kaydedildi.');
    }

    public function menuKaydet(Request $request)
    {
        $ids = $request->input('id', []);
        $labels = $request->input('label', []);
        $urls = $request->input('url', []);
        $routes = $request->input('route', []);
        $linkTypes = $request->input('link_type', []);
        $aktif = $request->input('aktif', []);
        $allowedRoutes = array_keys($this->internalPageOptions());

        foreach ($ids as $i => $id) {
            $type = (string) ($linkTypes[$i] ?? 'route');
            if (! in_array($type, ['route', 'url'], true)) {
                $type = 'route';
            }

            $route = (string) ($routes[$i] ?? 'frontend.anasayfa');
            if (! in_array($route, $allowedRoutes, true)) {
                $route = 'frontend.anasayfa';
            }

            $url = null;
            if ($type === 'url') {
                $url = trim((string) ($urls[$i] ?? ''));
                if ($url !== '' && ! str_starts_with($url, 'http') && ! str_starts_with($url, '/') && ! str_starts_with($url, 'mailto:') && ! str_starts_with($url, 'tel:')) {
                    $url = 'https://'.$url;
                }
                if ($url === '') {
                    $url = null;
                    $type = 'route';
                }
            }

            $label = trim((string) ($labels[$i] ?? ''));
            if ($label === '' && $type === 'route') {
                $label = $this->internalPageOptions()[$route] ?? $route;
            }

            SiteMenuItem::query()->where('id', (int) $id)->update([
                'label' => $label,
                'route' => $route,
                'url' => $type === 'url' ? $url : null,
                'aktif' => ! empty($aktif[$i]),
            ]);
        }
        $this->settings->forgetCache();

        return back()->with('basari', 'Menü kaydedildi.');
    }

    /**
     * Sistemdeki tüm public sayfaları menü tablosuna ekle (eksik olanlar).
     */
    protected function syncSystemMenuItems(): void
    {
        $pages = $this->internalPageOptions();
        $max = (int) SiteMenuItem::query()->max('sira');

        foreach ($pages as $route => $label) {
            $key = str_replace(['frontend.', '.'], ['', '_'], $route);
            $exists = SiteMenuItem::query()
                ->where(function ($q) use ($key, $route) {
                    $q->where('key', $key)->orWhere('route', $route);
                })
                ->exists();

            if ($exists) {
                continue;
            }

            $max++;
            SiteMenuItem::query()->create([
                'key' => $key,
                'label' => $label,
                'route' => $route,
                'url' => null,
                'aktif' => true,
                'sira' => $max,
            ]);
        }
    }

    public function anasayfaKaydet(Request $request)
    {
        $ids = $request->input('id', []);
        $basliklar = $request->input('baslik', []);
        $altlar = $request->input('alt_metin', []);
        $aktif = $request->input('aktif', []);

        foreach ($ids as $i => $id) {
            SiteHomepageSection::query()->where('id', (int) $id)->update([
                'baslik' => $basliklar[$i] ?? null,
                'alt_metin' => $altlar[$i] ?? null,
                'aktif' => ! empty($aktif[$i]),
            ]);
        }
        $this->settings->forgetCache();

        return back()->with('basari', 'Ana sayfa bölümleri kaydedildi.');
    }

    public function sliderStore(Request $request)
    {
        $data = $this->validateSlideRequest($request);
        $imagePath = $this->resolveSlideImage($request, null);
        $cta = $this->resolveLinkFields($request, 'cta', 'Randevu Al', 'frontend.randevu');
        $cta2 = $this->resolveLinkFields($request, 'cta2', 'İletişim', 'frontend.iletisim');
        $extraMeta = $this->resolveExtraSlideMeta($request);

        $max = (int) SiteSliderSlide::query()->max('sira');
        SiteSliderSlide::query()->create([
            'baslik' => $data['baslik'] ?? 'Yeni slayt',
            'alt' => $data['alt'] ?? '',
            'etiket' => $data['etiket'] ?? '',
            'badge' => $data['badge'] ?? '',
            'image' => $imagePath,
            'thumb' => $imagePath,
            'cta' => $cta['label'],
            'cta_url' => $cta['url'],
            'cta2' => $cta2['label'],
            'cta2_url' => $cta2['url'],
            'meta' => array_merge($cta['meta'], $cta2['meta'], $extraMeta),
            'aktif' => true,
            'sira' => $max + 1,
        ]);
        $this->settings->forgetCache();

        return back()->with('basari', 'Slayt eklendi.');
    }

    public function sliderUpdate(Request $request, int $id)
    {
        $slide = SiteSliderSlide::query()->findOrFail($id);
        $data = $this->validateSlideRequest($request, true);
        $imagePath = $this->resolveSlideImage($request, $slide);
        $cta = $this->resolveLinkFields($request, 'cta', $slide->cta ?: 'Randevu Al', 'frontend.randevu');
        $cta2 = $this->resolveLinkFields($request, 'cta2', $slide->cta2 ?: 'İletişim', 'frontend.iletisim');
        $oldMeta = is_array($slide->meta) ? $slide->meta : [];
        $extraMeta = $this->resolveExtraSlideMeta($request);

        $slide->update([
            'baslik' => $data['baslik'] ?? $slide->baslik,
            'alt' => $data['alt'] ?? $slide->alt,
            'etiket' => $data['etiket'] ?? $slide->etiket,
            'badge' => $data['badge'] ?? $slide->badge,
            'image' => $imagePath,
            'thumb' => $imagePath,
            'cta' => $cta['label'],
            'cta_url' => $cta['url'],
            'cta2' => $cta2['label'],
            'cta2_url' => $cta2['url'],
            'meta' => array_merge($oldMeta, $cta['meta'], $cta2['meta'], $extraMeta),
            'aktif' => $request->boolean('aktif', (bool) $slide->aktif),
        ]);
        $this->settings->forgetCache();

        return back()->with('basari', 'Slayt güncellendi.');
    }


    public function sliderDestroy(int $id)
    {
        $slide = SiteSliderSlide::query()->find($id);
        if ($slide) {
            $this->settings->deleteMediaIfOwned($slide->image);
            $slide->delete();
        }
        $this->settings->forgetCache();

        return back()->with('basari', 'Slayt silindi.');
    }

    /** @return array<string,string> route => label */
    protected function internalPageOptions(): array
    {
        return [
            'frontend.anasayfa' => 'Ana Sayfa',
            'frontend.hakkimda' => 'Hakkımızda',
            'frontend.hekimler' => 'Hekimlerimiz',
            'frontend.hizmetler' => 'Hizmetler',
            'frontend.galeri' => 'Galeri',
            'frontend.blog' => 'Blog',
            'frontend.sss' => 'S.S.S.',
            'frontend.iletisim' => 'İletişim',
            'frontend.randevu' => 'Randevu',
        ];
    }

    protected function validateSlideRequest(Request $request, bool $update = false): array
    {
        return $request->validate([
            'baslik' => ['required', 'string', 'max:255'],
            'baslik_vurgulu' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string', 'max:2000'],
            'etiket' => ['nullable', 'string', 'max:100'],
            'badge' => ['nullable', 'string', 'max:100'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'image_sil' => ['nullable'],
            'cta' => ['nullable', 'string', 'max:100'],
            'cta_link_type' => ['nullable', 'in:route,url,none'],
            'cta_route' => ['nullable', 'string', 'max:100'],
            'cta_custom_url' => ['nullable', 'string', 'max:500'],
            'cta2' => ['nullable', 'string', 'max:100'],
            'cta2_link_type' => ['nullable', 'in:route,url,none'],
            'cta2_route' => ['nullable', 'string', 'max:100'],
            'cta2_custom_url' => ['nullable', 'string', 'max:500'],
            'float_1_baslik' => ['nullable', 'string', 'max:120'],
            'float_1_aciklama' => ['nullable', 'string', 'max:180'],
            'float_2_baslik' => ['nullable', 'string', 'max:120'],
            'float_2_aciklama' => ['nullable', 'string', 'max:180'],
            'stat_sayi' => ['nullable', 'array', 'max:3'],
            'stat_sayi.*' => ['nullable', 'string', 'max:20'],
            'stat_suffix' => ['nullable', 'array', 'max:3'],
            'stat_suffix.*' => ['nullable', 'string', 'max:10'],
            'stat_etiket' => ['nullable', 'array', 'max:3'],
            'stat_etiket.*' => ['nullable', 'string', 'max:80'],
            'aktif' => ['nullable'],
        ]);
    }

    /**
     * Yılmaz Kıran tarzı ekstra alanlar → meta JSON
     */
    protected function resolveExtraSlideMeta(Request $request): array
    {
        $stats = [];
        $sayilar = $request->input('stat_sayi', []);
        $suffixes = $request->input('stat_suffix', []);
        $etiketler = $request->input('stat_etiket', []);
        for ($i = 0; $i < 3; $i++) {
            $etiket = trim((string) ($etiketler[$i] ?? ''));
            $sayiRaw = trim((string) ($sayilar[$i] ?? ''));
            if ($etiket === '' && $sayiRaw === '') {
                continue;
            }
            $stats[] = [
                'sayi' => (int) preg_replace('/\D/', '', $sayiRaw),
                'suffix' => trim((string) ($suffixes[$i] ?? '')),
                'etiket' => $etiket !== '' ? $etiket : 'İstatistik',
            ];
        }

        return [
            'baslik_vurgulu' => trim((string) $request->input('baslik_vurgulu', '')),
            'float_1_baslik' => trim((string) $request->input('float_1_baslik', '')),
            'float_1_aciklama' => trim((string) $request->input('float_1_aciklama', '')),
            'float_2_baslik' => trim((string) $request->input('float_2_baslik', '')),
            'float_2_aciklama' => trim((string) $request->input('float_2_aciklama', '')),
            'istatistikler' => $stats,
        ];
    }

    protected function resolveSlideImage(Request $request, ?SiteSliderSlide $existing): ?string
    {
        $current = $existing?->image;

        if ($request->boolean('image_sil')) {
            $this->settings->deleteMediaIfOwned($current);

            return null;
        }

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('site/slider', 'public');
            if ($path) {
                $this->settings->deleteMediaIfOwned($current);

                return $path;
            }
        }

        $url = trim((string) $request->input('image_url', ''));
        if ($url !== '') {
            // Harici URL veya mevcut storage path
            if ($current && $url !== $current && $url !== $this->settings->mediaUrl($current)) {
                // Yeni harici URL — eski upload'u temizle
                if (! str_starts_with($url, 'http') && $url !== $current) {
                    // relative path kept
                } elseif (str_starts_with($url, 'http')) {
                    $this->settings->deleteMediaIfOwned($current);

                    return $url;
                }
            }
            if (str_starts_with($url, 'http')) {
                if ($current && $current !== $url) {
                    $this->settings->deleteMediaIfOwned($current);
                }

                return $url;
            }
        }

        return $current;
    }

    /**
     * @return array{label:string,url:?string,meta:array}
     */
    protected function resolveLinkFields(Request $request, string $prefix, string $defaultLabel, string $defaultRoute): array
    {
        $label = trim((string) $request->input($prefix, $defaultLabel));
        $type = (string) $request->input($prefix.'_link_type', 'route');
        $route = (string) $request->input($prefix.'_route', $defaultRoute);
        $custom = trim((string) $request->input($prefix.'_custom_url', ''));
        $pages = $this->internalPageOptions();

        if ($type === 'none' || $label === '') {
            return [
                'label' => $label,
                'url' => null,
                'meta' => [
                    $prefix.'_link_type' => 'none',
                    $prefix.'_route' => null,
                ],
            ];
        }

        if ($type === 'url') {
            $url = $custom !== '' ? $custom : '#';
            if ($url !== '#' && ! str_starts_with($url, 'http') && ! str_starts_with($url, '/') && ! str_starts_with($url, 'mailto:') && ! str_starts_with($url, 'tel:')) {
                $url = 'https://'.$url;
            }

            return [
                'label' => $label ?: $defaultLabel,
                'url' => $url,
                'meta' => [
                    $prefix.'_link_type' => 'url',
                    $prefix.'_route' => null,
                ],
            ];
        }

        // route (site içi)
        if (! array_key_exists($route, $pages)) {
            $route = $defaultRoute;
        }
        try {
            $url = route($route, [], false);
        } catch (\Throwable) {
            $url = '/';
        }

        return [
            'label' => $label ?: $defaultLabel,
            'url' => $url,
            'meta' => [
                $prefix.'_link_type' => 'route',
                $prefix.'_route' => $route,
            ],
        ];
    }

    protected function guessLinkType(?string $url): string
    {
        if (! filled($url)) {
            return 'none';
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
            return 'url';
        }

        return 'route';
    }

    protected function guessRoute(?string $url): string
    {
        $map = [
            '/' => 'frontend.anasayfa',
            '/hakkimda' => 'frontend.hakkimda',
            '/hizmetler' => 'frontend.hizmetler',
            '/galeri' => 'frontend.galeri',
            '/blog' => 'frontend.blog',
            '/sss' => 'frontend.sss',
            '/iletisim' => 'frontend.iletisim',
            '/randevu' => 'frontend.randevu',
        ];
        $path = parse_url((string) $url, PHP_URL_PATH) ?: (string) $url;
        $path = '/'.ltrim($path, '/');
        if ($path === '//') {
            $path = '/';
        }

        return $map[$path] ?? 'frontend.randevu';
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:menu,slider,anasayfa'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $this->settings->reorder($data['type'], $data['ids']);

        return response()->json(['success' => true, 'message' => 'Sıralama kaydedildi.']);
    }

    public function toggle(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:menu,slider,anasayfa'],
            'id' => ['required', 'integer'],
            'aktif' => ['required', 'boolean'],
        ]);

        $model = match ($data['type']) {
            'menu' => SiteMenuItem::class,
            'slider' => SiteSliderSlide::class,
            'anasayfa' => SiteHomepageSection::class,
        };

        $model::query()->where('id', $data['id'])->update(['aktif' => $data['aktif']]);
        $this->settings->forgetCache();

        return response()->json(['success' => true]);
    }

    /** Legacy catch-all for old kaydet route */
    public function kaydet(Request $request, string $group)
    {
        return match ($group) {
            'genel' => $this->kaydetGenel($request),
            'seo' => $this->kaydetSeo($request),
            'iletisim' => $this->kaydetIletisim($request),
            'menu' => $this->menuKaydet($request),
            'anasayfa' => $this->anasayfaKaydet($request),
            default => back()->with('hata', 'Bilinmeyen grup'),
        };
    }
}
