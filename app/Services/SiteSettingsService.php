<?php

namespace App\Services;

use App\Models\SiteHomepageSection;
use App\Models\SiteFooterItem;
use App\Models\SiteMenuItem;
use App\Models\SiteOption;
use App\Models\SiteSliderSlide;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Doktor sitesi vitrin ayarları — local SQLite, ayrı tablolar.
 */
class SiteSettingsService
{
    public function forgetCache(): void
    {
        Cache::forget('kliniksitesi.site_settings.v2');
        Cache::forget('kliniksitesi.site_settings.v3');
        try {
            app(SiteContentService::class)->forgetCache();
        } catch (\Throwable) {
            // ignore
        }
    }

    public function option(string $key, mixed $default = null): mixed
    {
        try {
            $row = SiteOption::query()->where('key', $key)->first();

            return $row?->value ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    public function setOption(string $key, mixed $value): void
    {
        SiteOption::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? '')]
        );
        $this->forgetCache();
    }

    public function setOptions(array $pairs): void
    {
        DB::transaction(function () use ($pairs) {
            foreach ($pairs as $key => $value) {
                SiteOption::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? '')]
                );
            }
        });
        $this->forgetCache();
    }

    public function boolOption(string $key, bool $default = true): bool
    {
        $v = $this->option($key, $default ? '1' : '0');

        return in_array((string) $v, ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Public disk path → full URL (or absolute URL passthrough).
     */
    public function mediaUrl(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }
        if (str_starts_with($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    public function deleteMediaIfOwned(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '' || str_starts_with($path, 'http')) {
            return;
        }
        $relative = preg_replace('#^/?storage/#', '', $path) ?: $path;
        try {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($relative)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($relative);
            }
        } catch (\Throwable) {
            // ignore
        }
    }

    public function menuItems()
    {
        return SiteMenuItem::query()->orderBy('sira')->orderBy('id')->get();
    }

    public function footerItems()
    {
        return SiteFooterItem::query()->orderBy('sira')->orderBy('id')->get();
    }

    public function sliderSlides()
    {
        return SiteSliderSlide::query()->orderBy('sira')->orderBy('id')->get();
    }

    public function homepageSections()
    {
        return SiteHomepageSection::query()->orderBy('sira')->orderBy('id')->get();
    }

    public function reorder(string $type, array $ids): void
    {
        $model = match ($type) {
            'menu' => SiteMenuItem::class,
            'footer' => SiteFooterItem::class,
            'slider' => SiteSliderSlide::class,
            'anasayfa' => SiteHomepageSection::class,
            default => throw new \InvalidArgumentException('Geçersiz tip'),
        };

        DB::transaction(function () use ($model, $ids) {
            foreach (array_values($ids) as $i => $id) {
                $model::query()->where('id', (int) $id)->update(['sira' => $i + 1]);
            }
        });
        $this->forgetCache();
    }

    /**
     * Frontend bundle (cached).
     */
    public function frontendBundle(): array
    {
        return Cache::remember('kliniksitesi.site_settings.v3', 60, function () {
            return [
                'genel' => [
                    'site_baslik_ek' => $this->option('site_baslik_ek', ''),
                    'slogan_override' => $this->option('slogan_override', ''),
                    'footer_metin' => $this->option('footer_metin', ''),
                    'tema_renk' => $this->option('tema_renk', '#0b5ed7'),
                    'tema_id' => $this->option('tema_id', (string) config('themes.default', 'klasik')),
                    'vitrin_badge' => $this->option('vitrin_badge', ''),
                    'logo' => $this->option('site_logo', ''),
                    'favicon' => $this->option('site_favicon', ''),
                    'logo_url' => $this->mediaUrl($this->option('site_logo', '')),
                    'favicon_url' => $this->mediaUrl($this->option('site_favicon', '')),
                    'whatsapp_goster' => $this->boolOption('whatsapp_goster', true),
                    'hekim_girisi_goster' => $this->boolOption('hekim_girisi_goster', true),
                ],
                'menu' => [
                    'items' => $this->menuItems()->map(fn ($m) => [
                        'id' => $m->id,
                        'parent_id' => $m->parent_id ? (int) $m->parent_id : null,
                        'key' => $m->key,
                        'label' => $m->label,
                        'route' => $m->route,
                        'url' => $m->url,
                        'aktif' => (bool) $m->aktif,
                        'sira' => (int) $m->sira,
                    ])->all(),
                ],
                'footer' => [
                    'items' => $this->footerItems()->map(fn ($m) => [
                        'id' => $m->id,
                        'key' => $m->key,
                        'label' => $m->label,
                        'route' => $m->route,
                        'url' => $m->url,
                        'aktif' => (bool) $m->aktif,
                        'sira' => (int) $m->sira,
                    ])->all(),
                ],
                'slider' => (function () {
                    // Yalnızca panelden eklenen slaytlar (otomatik API yok)
                    $slides = $this->sliderSlides()->filter(fn ($s) => (bool) $s->aktif)->values();

                    return [
                        'slides' => $slides->map(function ($s, $i) {
                            $img = $this->mediaUrl($s->image) ?: $s->image;
                            $thumb = $this->mediaUrl($s->thumb) ?: ($s->thumb ?: $img);
                            $meta = is_array($s->meta) ? $s->meta : [];

                            return [
                                'id' => $s->id,
                                'no' => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                                'baslik' => $s->baslik,
                                'baslik_vurgulu' => $meta['baslik_vurgulu'] ?? '',
                                'alt' => $s->alt,
                                'etiket' => $s->etiket,
                                'badge' => $s->badge,
                                'image' => $img,
                                'thumb' => $thumb,
                                'cta' => $s->cta,
                                'cta_url' => $s->cta_url,
                                'cta2' => $s->cta2,
                                'cta2_url' => $s->cta2_url,
                                'float_1_baslik' => $meta['float_1_baslik'] ?? '',
                                'float_1_aciklama' => $meta['float_1_aciklama'] ?? '',
                                'float_2_baslik' => $meta['float_2_baslik'] ?? '',
                                'float_2_aciklama' => $meta['float_2_aciklama'] ?? '',
                                'istatistikler' => $meta['istatistikler'] ?? [],
                                'meta' => $meta,
                            ];
                        })->all(),
                    ];
                })(),
                'anasayfa' => (function () {
                    $sections = $this->homepageSections();

                    return [
                        'bolumler' => $sections->mapWithKeys(fn ($s) => [
                            $s->key => (bool) $s->aktif,
                        ])->all(),
                        'sira' => $sections->pluck('key')->values()->all(),
                        'basliklar' => $sections->mapWithKeys(fn ($s) => [
                            $s->key => ['baslik' => $s->baslik, 'alt' => $s->alt_metin],
                        ])->all(),
                    ];
                })(),
                'seo' => [
                    'meta_baslik' => $this->option('seo_meta_baslik', ''),
                    'meta_aciklama' => $this->option('seo_meta_aciklama', ''),
                    'meta_anahtar' => $this->option('seo_meta_anahtar', ''),
                    'gtm_container_id' => $this->option('seo_gtm_container_id', ''),
                    'ga4_measurement_id' => $this->option('seo_ga4_measurement_id', ''),
                    'meta_pixel_id' => $this->option('seo_meta_pixel_id', ''),
                    'google_ads_id' => $this->option('seo_google_ads_id', ''),
                    'recaptcha_site_key' => $this->option('seo_recaptcha_site_key', ''),
                    'recaptcha_enabled' => $this->option('seo_recaptcha_enabled', '1') !== '0',
                ],
                'iletisim' => [
                    'baslik' => $this->option('iletisim_baslik', 'İletişim & online randevu'),
                    'alt_metin' => $this->option('iletisim_alt_metin', ''),
                    'form_goster' => $this->boolOption('iletisim_form_goster', true),
                    'harita_goster' => $this->boolOption('iletisim_harita_goster', true),
                    'saatler_goster' => $this->boolOption('iletisim_saatler_goster', true),
                ],
            ];
        });
    }
}
