@php
    $ysbDash = [
        'href' => route('panel.dashboard'),
        'match' => 'panel.dashboard',
        'title' => 'Panel ozeti',
        'sub' => 'Genel bakis',
    ];

    $ysbGroups = [
        [
            'id' => 'hekim-randevu',
            'label' => 'Hekim · Randevu',
            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'items' => [
                ['href' => route('panel.randevular'), 'match' => 'panel.randevular', 'label' => 'Takvimim'],
                ['href' => route('panel.randevular.talepler'), 'match' => 'panel.randevular.talepler', 'label' => 'Randevu Talepleri'],
                ['href' => route('panel.hastalar'), 'match' => 'panel.hastalar', 'label' => 'Hasta Kayitlari'],
                ['href' => route('panel.randevu-ayarlari'), 'match' => 'panel.randevu-ayarlari*', 'label' => 'Randevu Ayarlari'],
            ],
        ],
        [
            'id' => 'hekim-icerik',
            'label' => 'Hekim · Icerik',
            'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
            'items' => [
                ['href' => route('panel.hizmetler'), 'match' => 'panel.hizmetler*', 'label' => 'Hizmet ve Tedaviler'],
                ['href' => route('panel.bloglar'), 'match' => 'panel.bloglar*', 'label' => 'Blog'],
                ['href' => route('panel.egitimler.index'), 'match' => 'panel.egitimler.index|panel.egitimler.create|panel.egitimler.edit', 'label' => 'Egitimler'],
                ['href' => route('panel.egitimler.basvurular.tumu'), 'match' => 'panel.egitimler.basvurular*|panel.egitimler.basvuru.*', 'label' => 'Egitim basvurulari'],
                ['href' => route('panel.yorumlar'), 'match' => 'panel.yorumlar*', 'label' => 'Yorumlar'],
                ['href' => route('panel.faqs'), 'match' => 'panel.faqs*', 'label' => 'SSS'],
                ['href' => route('panel.galeri'), 'match' => 'panel.galeri*', 'label' => 'Galeri'],
                ['href' => route('panel.hakkimda'), 'match' => 'panel.hakkimda*', 'label' => 'Hakkimda'],
            ],
        ],
        [
            'id' => 'hekim-finans',
            'label' => 'Hekim · Finans',
            'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'items' => [
                ['href' => route('panel.finans'), 'match' => 'panel.finans', 'label' => 'Genel Bakis'],
                ['href' => route('panel.finans.gelirler'), 'match' => 'panel.finans.gelirler', 'label' => 'Gelirler'],
                ['href' => route('panel.finans.giderler'), 'match' => 'panel.finans.giderler', 'label' => 'Giderler'],
                ['href' => route('panel.finans.hasta-bakiyeleri'), 'match' => 'panel.finans.hasta-bakiyeleri', 'label' => 'Hasta Bakiyeleri'],
                ['href' => route('panel.finans.kategoriler'), 'match' => 'panel.finans.kategoriler', 'label' => 'Kategoriler'],
            ],
        ],
        [
            'id' => 'hekim-hesap',
            'label' => 'Hekim · Hesabim',
            'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
            'items' => [
                ['href' => route('panel.profil'), 'match' => 'panel.profil', 'label' => 'Profil'],
                ['href' => route('panel.sifre'), 'match' => 'panel.sifre*', 'label' => 'Sifre'],
                ['href' => route('panel.two-factor'), 'match' => 'panel.two-factor', 'label' => '2FA Guvenlik'],
            ],
        ],
        [
            'id' => 'klinik-site',
            'label' => 'Site Ayarlari',
            'icon' => 'M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3',
            'items' => [
                ['href' => route('panel.site-ayarlari.genel'), 'match' => 'panel.site-ayarlari.genel', 'label' => 'Genel'],
                ['href' => route('panel.site-ayarlari.temalar'), 'match' => 'panel.site-ayarlari.temalar', 'label' => 'Temalar'],
                ['href' => route('panel.site-ayarlari.menu'), 'match' => 'panel.site-ayarlari.menu', 'label' => 'Menu'],
                ['href' => route('panel.site-ayarlari.slider'), 'match' => 'panel.site-ayarlari.slider', 'label' => 'Slider'],
                ['href' => route('panel.site-ayarlari.anasayfa'), 'match' => 'panel.site-ayarlari.anasayfa', 'label' => 'Ana Sayfa'],
                ['href' => route('panel.site-ayarlari.seo'), 'match' => 'panel.site-ayarlari.seo', 'label' => 'SEO'],
                ['href' => route('panel.site-ayarlari.iletisim'), 'match' => 'panel.site-ayarlari.iletisim', 'label' => 'Iletisim'],
                ['href' => route('panel.api-entegrasyon'), 'match' => 'panel.api-entegrasyon*', 'label' => 'API Entegrasyon'],
            ],
        ],
    ];

    $ysbSectionLabel = 'Menu';
    $ysbExtraHtml = '<a href="'.e(route('frontend.anasayfa')).'" target="_blank" class="ysb-dash" style="margin-top:.25rem">'
        .'<span class="ysb-dash-text"><span class="ysb-dash-title">Public siteyi ac</span>'
        .'<span class="ysb-dash-sub">Yeni sekme</span></span></a>';
@endphp
@include('panel.partials.sidebar-ysb-nav')
