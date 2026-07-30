<?php

if (! function_exists('media_url')) {
    /**
     * Resolve upload/media paths against the platform media host (API /media).
     * Relative DB paths like "uploads/hizmet/x.jpg" live on site/public and are
     * exposed by the API at http://127.0.0.1:8001/media/uploads/...
     */
    function media_url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        // Already absolute
        if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'data:')) {
            // Rewrite stale :8000 site URLs to media base
            if (preg_match('#^https?://[^/]+(?::\d+)?/(uploads|storage)/(.+)$#i', $path, $m)) {
                return rtrim(media_base(), '/').'/'.$m[1].'/'.$m[2];
            }
            // Already on /media/ host — keep
            return $path;
        }

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        // Strip leading "media/" if present
        if (str_starts_with($path, 'media/')) {
            $path = substr($path, 6);
        }

        return rtrim(media_base(), '/').'/'.$path;
    }
}

if (! function_exists('media_base')) {
    function media_base(): string
    {
        $base = (string) config('randevu_api.media_base', '');
        if ($base !== '') {
            return rtrim($base, '/');
        }

        // Derive from API platform: http://host:8001/api/v1 → http://host:8001/media
        $platform = (string) config('randevu_api.platform_base', 'http://127.0.0.1:8001/api/v1');
        $platform = preg_replace('#/api(?:/v1)?/?$#', '', rtrim($platform, '/'));

        return rtrim($platform, '/').'/media';
    }
}

if (! function_exists('media_asset')) {
    function media_asset(?string $path): ?string
    {
        return media_url($path);
    }
}

if (! function_exists('site_themes')) {
    /**
     * @return array<string, array<string, mixed>>
     */
    function site_themes(): array
    {
        return (array) config('themes.catalog', []);
    }
}

if (! function_exists('themes_premium_unlocked')) {
    function themes_premium_unlocked(): bool
    {
        return (bool) config('themes.premium_unlocked', true);
    }
}

if (! function_exists('theme_is_available')) {
    function theme_is_available(string $id): bool
    {
        $catalog = site_themes();
        if (! isset($catalog[$id])) {
            return false;
        }
        $premium = (bool) ($catalog[$id]['premium'] ?? false);

        return ! $premium || themes_premium_unlocked();
    }
}

if (! function_exists('resolve_site_theme')) {
    /**
     * @return array{id: string, ad: string, aciklama?: string, renk: string, font_sans?: string, font_display?: string, google_fonts?: string, preview?: array, premium?: bool}
     */
    function resolve_site_theme(?string $id = null): array
    {
        $catalog = site_themes();
        $default = (string) config('themes.default', 'klasik');
        $key = ($id && isset($catalog[$id])) ? $id : $default;

        if (isset($catalog[$key]) && ! theme_is_available($key)) {
            $key = $default;
        }

        if (! isset($catalog[$key])) {
            $key = array_key_first($catalog) ?: 'klasik';
            $catalog[$key] = $catalog[$key] ?? [
                'ad' => 'Varsayılan',
                'renk' => '#0b5ed7',
                'google_fonts' => 'Inter:wght@400;600;700',
            ];
        }

        return array_merge(['id' => $key], $catalog[$key]);
    }
}

if (! function_exists('current_theme_id')) {
    function current_theme_id(?array $doktor = null): string
    {
        if (is_array($doktor)) {
            $id = $doktor['tema_id'] ?? ($doktor['tema']['id'] ?? null);

            return resolve_site_theme(is_string($id) ? $id : null)['id'];
        }

        try {
            $fromView = view()->shared('doktor');
            if (is_array($fromView)) {
                return current_theme_id($fromView);
            }
        } catch (\Throwable) {
            //
        }

        return resolve_site_theme(null)['id'];
    }
}

if (! function_exists('theme_pack_id')) {
    function theme_pack_id(?string $themeId = null): string
    {
        $themeId = $themeId ?: current_theme_id();
        $meta = resolve_site_theme($themeId);

        return (string) ($meta['layout'] ?? $themeId);
    }
}

if (! function_exists('theme_layout')) {
    function theme_layout(?array $doktor = null): string
    {
        $themeId = $doktor ? current_theme_id($doktor) : current_theme_id();
        $pack = theme_pack_id($themeId);
        $name = 'frontend.themes.'.$pack.'.layouts.app';
        if (view()->exists($name)) {
            return $name;
        }
        if (view()->exists('frontend.themes.klasik.layouts.app')) {
            return 'frontend.themes.klasik.layouts.app';
        }

        return 'frontend.layouts.app';
    }
}

if (! function_exists('theme_view_name')) {
    function theme_view_name(string $name, ?string $themeId = null): string
    {
        $name = ltrim(str_replace(['/', '\\'], '.', $name), '.');
        if (str_starts_with($name, 'frontend.')) {
            $name = substr($name, strlen('frontend.'));
        }

        $themeId = $themeId ?: current_theme_id();
        $pack = theme_pack_id($themeId);

        $primary = 'frontend.themes.'.$pack.'.'.$name;
        if (view()->exists($primary)) {
            return $primary;
        }
        if ($themeId !== $pack) {
            $byId = 'frontend.themes.'.$themeId.'.'.$name;
            if (view()->exists($byId)) {
                return $byId;
            }
        }
        if ($pack !== 'klasik') {
            $klasik = 'frontend.themes.klasik.'.$name;
            if (view()->exists($klasik)) {
                return $klasik;
            }
        }

        return 'frontend.'.$name;
    }
}

if (! function_exists('theme_view')) {
    /**
     * @param  array<string, mixed>  $data
     */
    function theme_view(string $name, array $data = [], ?string $themeId = null): \Illuminate\Contracts\View\View
    {
        if (isset($data['doktor']) && is_array($data['doktor'])) {
            $themeId = $themeId ?: current_theme_id($data['doktor']);
        }

        return view(theme_view_name($name, $themeId), $data);
    }
}

if (! function_exists('site_nav')) {
    /**
     * @return array<int, array{href: string, label: string, match: ?string, external: bool, route?: mixed}>
     */
    function site_nav(?array $doktor = null): array
    {
        if ($doktor === null) {
            try {
                $shared = view()->shared('doktor');
                $doktor = is_array($shared) ? $shared : [];
            } catch (\Throwable) {
                $doktor = [];
            }
        }

        $matchMap = [
            'anasayfa' => 'frontend.anasayfa',
            'hakkimda' => 'frontend.hakkimda',
            'hekimler' => 'frontend.hekim*',
            'hizmetler' => 'frontend.hizmet*',
            'egitimler' => 'frontend.egitim*',
            'galeri' => 'frontend.galeri',
            'blog' => 'frontend.blog*',
            'sss' => 'frontend.sss',
            'iletisim' => 'frontend.iletisim',
        ];

        if (! empty($doktor['menu']) && is_array($doktor['menu'])) {
            return collect($doktor['menu'])->map(function ($item) use ($matchMap) {
                $key = $item['key'] ?? '';
                $href = function_exists('nav_href') ? nav_href($item) : ($item['url'] ?? '#');
                $isExternal = filled($item['url'] ?? null)
                    && (str_starts_with((string) $item['url'], 'http') || str_starts_with((string) $item['url'], '//'));

                return [
                    'href' => $href,
                    'label' => $item['label'] ?? $key,
                    'match' => $matchMap[$key] ?? ($item['route'] ?? null),
                    'external' => $isExternal,
                    'route' => $item['route'] ?? null,
                ];
            })->values()->all();
        }

        $nav = [
            ['href' => route('frontend.anasayfa'), 'label' => 'Ana Sayfa', 'match' => 'frontend.anasayfa', 'external' => false],
        ];
        if (\Illuminate\Support\Facades\Route::has('frontend.hakkimda')) {
            $nav[] = ['href' => route('frontend.hakkimda'), 'label' => 'Hakkımızda', 'match' => 'frontend.hakkimda', 'external' => false];
        }
        if (\Illuminate\Support\Facades\Route::has('frontend.hekimler')) {
            $nav[] = ['href' => route('frontend.hekimler'), 'label' => 'Hekimler', 'match' => 'frontend.hekim*', 'external' => false];
        }
        if (\Illuminate\Support\Facades\Route::has('frontend.hizmetler')) {
            $nav[] = ['href' => route('frontend.hizmetler'), 'label' => 'Hizmetler', 'match' => 'frontend.hizmet*', 'external' => false];
        }
        if (\Illuminate\Support\Facades\Route::has('frontend.galeri')) {
            $nav[] = ['href' => route('frontend.galeri'), 'label' => 'Galeri', 'match' => 'frontend.galeri', 'external' => false];
        }
        if (\Illuminate\Support\Facades\Route::has('frontend.blog')) {
            $nav[] = ['href' => route('frontend.blog'), 'label' => 'Blog', 'match' => 'frontend.blog*', 'external' => false];
        }
        if (\Illuminate\Support\Facades\Route::has('frontend.sss')) {
            $nav[] = ['href' => route('frontend.sss'), 'label' => 'S.S.S.', 'match' => 'frontend.sss', 'external' => false];
        }
        if (\Illuminate\Support\Facades\Route::has('frontend.iletisim')) {
            $nav[] = ['href' => route('frontend.iletisim'), 'label' => 'İletişim', 'match' => 'frontend.iletisim', 'external' => false];
        }

        return $nav;
    }
}

if (! function_exists('theme_palette')) {
    /**
     * Generate brand CSS variables from a hex color (tema_renk).
     *
     * @return array<string, string>
     */
    function theme_palette(?string $hex): array
    {
        $hex = trim((string) $hex);
        if (! preg_match('/^#([0-9A-Fa-f]{6})$/', $hex, $m)) {
            $hex = '#0d9488';
            $m = [1 => '0d9488'];
        }
        $n = hexdec($m[1]);
        $r = ($n >> 16) & 255;
        $g = ($n >> 8) & 255;
        $b = $n & 255;

        $mix = static function (int $r, int $g, int $b, int $tr, int $tg, int $tb, float $t): string {
            $rr = (int) round($r + ($tr - $r) * $t);
            $gg = (int) round($g + ($tg - $g) * $t);
            $bb = (int) round($b + ($tb - $b) * $t);

            return sprintf('#%02x%02x%02x', $rr, $gg, $bb);
        };

        return [
            '50' => $mix($r, $g, $b, 255, 255, 255, 0.92),
            '100' => $mix($r, $g, $b, 255, 255, 255, 0.82),
            '200' => $mix($r, $g, $b, 255, 255, 255, 0.65),
            '400' => $mix($r, $g, $b, 255, 255, 255, 0.25),
            '500' => $mix($r, $g, $b, 255, 255, 255, 0.08),
            '600' => $hex,
            '700' => $mix($r, $g, $b, 0, 0, 0, 0.18),
            '800' => $mix($r, $g, $b, 0, 0, 0, 0.32),
            '900' => $mix($r, $g, $b, 0, 0, 0, 0.45),
        ];
    }
}

if (! function_exists('nav_href')) {
    /**
     * Resolve menu item to href (external url or named route).
     */
    function nav_href(array $item): string
    {
        $url = trim((string) ($item['url'] ?? ''));
        if ($url !== '') {
            return $url;
        }
        $route = (string) ($item['route'] ?? 'frontend.anasayfa');
        // Özel sayfa: page.kvkk → /sayfa/kvkk
        if (str_starts_with($route, 'page.')) {
            $slug = substr($route, 5);

            return $slug !== '' ? route('frontend.sayfa', ['slug' => $slug]) : url('/');
        }
        try {
            return route($route);
        } catch (\Throwable) {
            return url('/');
        }
    }
}

if (! function_exists('site_footer_pages')) {
    /**
     * Footer’da gösterilecek özel sayfalar.
     *
     * @return array<int, array{baslik: string, href: string, slug: string}>
     */
    function site_footer_pages(): array
    {
        try {
            return \App\Models\SitePage::query()
                ->footer()
                ->get()
                ->map(fn ($p) => [
                    'baslik' => $p->baslik,
                    'href' => $p->publicUrl(),
                    'slug' => $p->slug,
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
