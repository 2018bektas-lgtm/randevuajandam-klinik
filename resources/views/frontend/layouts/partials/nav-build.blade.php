@php
    $matchMap = [
        'anasayfa' => 'frontend.anasayfa',
        'hakkimda' => 'frontend.hakkimda',
        'hizmetler' => 'frontend.hizmet*',
        'egitimler' => 'frontend.egitim*',
        'galeri' => 'frontend.galeri',
        'blog' => 'frontend.blog*',
        'sss' => 'frontend.sss',
        'iletisim' => 'frontend.iletisim',
    ];
    if (! empty($doktor['menu']) && is_array($doktor['menu'])) {
        $nav = collect($doktor['menu'])->map(function ($item) use ($matchMap) {
            $key = $item['key'] ?? '';
            $href = nav_href($item);
            $isExternal = filled($item['url'] ?? null)
                && (str_starts_with($item['url'], 'http') || str_starts_with($item['url'], '//'));

            return [
                'href' => $href,
                'label' => $item['label'] ?? $key,
                'match' => $matchMap[$key] ?? ($item['route'] ?? null),
                'external' => $isExternal,
                'route' => $item['route'] ?? null,
            ];
        })->values()->all();
    } else {
        $nav = [
            ['href' => route('frontend.anasayfa'), 'label' => 'Ana Sayfa', 'match' => 'frontend.anasayfa', 'external' => false],
            ['href' => route('frontend.hakkimda'), 'label' => 'Hakkımda', 'match' => 'frontend.hakkimda', 'external' => false],
            ['href' => route('frontend.hizmetler'), 'label' => 'Hizmetler', 'match' => 'frontend.hizmet*', 'external' => false],
            ['href' => route('frontend.egitimler'), 'label' => 'Eğitimler', 'match' => 'frontend.egitim*', 'external' => false],
            ['href' => route('frontend.galeri'), 'label' => 'Galeri', 'match' => 'frontend.galeri', 'external' => false],
            ['href' => route('frontend.blog'), 'label' => 'Blog', 'match' => 'frontend.blog*', 'external' => false],
            ['href' => route('frontend.sss'), 'label' => 'S.S.S.', 'match' => 'frontend.sss', 'external' => false],
            ['href' => route('frontend.iletisim'), 'label' => 'İletişim', 'match' => 'frontend.iletisim', 'external' => false],
        ];
        if (empty($doktor['egitimler'])) {
            $nav = array_values(array_filter($nav, fn ($i) => ($i['match'] ?? '') !== 'frontend.egitim*'));
        }
    }
@endphp
