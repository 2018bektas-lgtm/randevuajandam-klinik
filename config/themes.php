<?php

/**
 * Doktor sitesi tam tasarım temaları.
 *
 * Her tema:
 *  - public/css/themes/{id}.css  → stil / layout iskeleti
 *  - resources/views/frontend/themes/{id}/...  → (opsiyonel) blade override
 *    Örn: pages/anasayfa, layouts/partials/header, layouts/partials/footer
 *
 * theme_view() önce tema blade'ini, yoksa varsayılan frontend/* kullanır.
 * Böylece sadece renk değil; header, anasayfa, kartlar, spacing hepsi temaya özel olabilir.
 */
return [
    'default' => 'klasik',

    'premium_unlocked' => (bool) env('THEMES_PREMIUM_UNLOCKED', true),

    'catalog' => [
        'klasik' => [
            'ad' => 'Klasik Klinik',
            'aciklama' => 'Tam layout: zarif serif hero, klasik menü, teal klinik dili.',
            'renk' => '#0d9488',
            'font_sans' => 'Inter',
            'font_display' => 'Cormorant Garamond',
            'google_fonts' => 'Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Inter:wght@400;500;600;700;800',
            'preview' => ['#0d9488', '#f7f5f1', '#0b1220'],
            'premium' => false,
            'layout' => 'classic', // varsayılan blade paketi
        ],
        'modern' => [
            'ad' => 'Modern Tech',
            'aciklama' => 'Tam layout: koyu sticky bar, full-bleed hero, keskin kartlar, dijital klinik.',
            'renk' => '#4f46e5',
            'font_sans' => 'DM Sans',
            'font_display' => 'Space Grotesk',
            'google_fonts' => 'Space+Grotesk:wght@500;600;700&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700',
            'preview' => ['#4f46e5', '#0f172a', '#f8fafc'],
            'premium' => false,
            'layout' => 'modern',
        ],
        'minimal' => [
            'ad' => 'Minimal',
            'aciklama' => 'Tam layout: topbar yok, ortalanmış marka, bol boşluk, sade grid.',
            'renk' => '#334155',
            'font_sans' => 'Inter',
            'font_display' => 'Inter',
            'google_fonts' => 'Inter:wght@400;500;600;700;800',
            'preview' => ['#334155', '#ffffff', '#e2e8f0'],
            'premium' => false,
            'layout' => 'minimal',
        ],
        'sicak' => [
            'ad' => 'Sıcak Premium',
            'aciklama' => 'Tam layout: bakır vurgu, yuvarlak kartlar, sıcak tipografi.',
            'renk' => '#C96A2B',
            'font_sans' => 'Outfit',
            'font_display' => 'Fraunces',
            'google_fonts' => 'Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Outfit:wght@400;500;600;700',
            'preview' => ['#C96A2B', '#FFF7ED', '#1c1917'],
            'premium' => true,
            'layout' => 'sicak',
        ],
        'ocean' => [
            'ad' => 'Ocean Blue',
            'aciklama' => 'Tam layout: sol marka şeridi, klinik mavi, editöryal grid.',
            'renk' => '#0369a1',
            'font_sans' => 'Source Sans 3',
            'font_display' => 'Libre Baskerville',
            'google_fonts' => 'Libre+Baskerville:wght@400;700&family=Source+Sans+3:wght@400;500;600;700',
            'preview' => ['#0369a1', '#f0f9ff', '#0c4a6e'],
            'premium' => true,
            'layout' => 'ocean',
        ],
        'delogis' => [
            'ad' => 'Delogis Klinik',
            'aciklama' => 'Delogis Home 3: slider hero, feature kartlar, premium klinik dili.',
            'renk' => '#B9905D',
            'font_sans' => 'Lexend',
            'font_display' => 'Castoro',
            'google_fonts' => 'Lexend:wght@300;400;500;600;700;800&family=Castoro:ital@0;1',
            'preview' => ['#B9905D', '#F6F2ED', '#293B46'],
            'premium' => true,
            'layout' => 'delogis',
        ],
    ],
];
