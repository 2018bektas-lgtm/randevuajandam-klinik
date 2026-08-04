<?php

/**
 * Ana platform (randevuajandam-site) paket matrisi ile aynı kod seti.
 * features oturumda API login/me ile gelir.
 */
return [
    /** Ana sitede paket yükseltme (hekim/klinik paneli). */
    'upgrade_url' => env('PLATFORM_PAKET_URL', env('RANDEVU_SITE_URL', 'http://127.0.0.1:8000').'/hekim/paket-sec?degistir=1'),

    /** Public sitede paket yoksa ana platform. */
    'platform_url' => env('RANDEVU_SITE_URL', 'http://127.0.0.1:8000'),

    /** Site sahibi için zorunlu paket özelliği (klinik sitesi). */
    'required_site_feature' => 'klinik_web_sitesi',

    'labels' => [
        'randevu_talebi_goruntule' => 'Randevu taleplerini görme',
        'randevu_talepleri' => 'Randevu taleplerini yönetme',
        'online_takvim' => 'Online randevu takvimi',
        'bekleme_listesi' => 'Bekleme listesi',
        'hizli_slot' => 'Hızlı slot kapatma',
        'seri_randevu' => 'Seri randevu',
        'ical_export' => 'Takvim dışa aktarma',
        'email_bildirim' => 'E-posta bildirimi',
        'sms_hatirlatma' => 'SMS hatırlatma',
        'sms_baslik' => 'SMS başlığı',
        'no_show_mesaj' => 'No-show mesajı',
        'hasta_kartlari' => 'Hasta kartları',
        'hasta_not_dosya' => 'Hasta not / dosya',
        'tedavi_gecmisi' => 'Tedavi geçmişi',
        'onam_formu' => 'Onam formu',
        'hasta_export' => 'Hasta Excel aktarma',
        'profil_sayfasi' => 'Hekim profili',
        'dogrulanmis_rozet' => 'Doğrulanmış rozet',
        'iletisim_profilde' => 'Profil iletişim bilgisi',
        'hakkimda' => 'Hakkımda / özgeçmiş',
        'galeri' => 'Fotoğraf galerisi',
        'dis_baglanti' => 'Dış bağlantılar',
        'oncelikli_liste' => 'Öne çıkarma',
        'yorum_gorunur' => 'Yorum görünürlüğü',
        'yorum_yanit' => 'Yorum yanıtlama',
        'yorum_davet' => 'Yorum daveti',
        'finans' => 'Finans yönetimi',
        'hasta_bakiyeleri' => 'Hasta bakiyeleri',
        'finans_rapor' => 'Finans raporu',
        'blog' => 'Blog / makale',
        'faq' => 'S.S.S. yönetimi',
        'egitimler' => 'Eğitimler',
        'online_gorusme' => 'Online görüntülü görüşme',
        'web_sitesi' => 'Kişisel web sitesi',
        'klinik_web_sitesi' => 'Klinik web sitesi',
        'destek_email' => 'E-posta destek',
        'destek_oncelikli' => 'Öncelikli destek',
        'veri_tasima' => 'Veri taşıma',
    ],
];
