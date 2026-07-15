<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Platform API kökü (ayrı api/ projesi)
    |--------------------------------------------------------------------------
    | Örn: http://127.0.0.1:8001/api/v1
    */
    'platform_base' => rtrim(env('RANDEVU_API_PLATFORM', env('RANDEVU_API_BASE_URL', 'http://127.0.0.1:8001/api/v1')), '/'),

    // Klinik public API kökü: .../api/v1/public/clinic
    'base_url' => rtrim(env('RANDEVU_API_BASE_URL', 'http://127.0.0.1:8001/api/v1/public/clinic'), '/'),

    // PlatformApiClient publicBase suffix (platform_base + this)
    'public_path' => env('RANDEVU_API_PUBLIC_PATH', '/public/clinic'),

    // Hekim paneli API: klinik sitesinde kliniğe bağlı hekimler
    // (bireysel doktor sitesi: /doctor)
    'doctor_path' => env('RANDEVU_API_DOCTOR_PATH', '/clinic/doctor'),

    /*
    | Klinik sitesi API anahtarları (Kurumsal paket — api_keys.klinik_id)
    | api_key  → X-Api-Key
    | secret   → X-Api-Secret
    */
    'api_key' => env('RANDEVU_API_KEY', ''),
    'api_secret' => env('RANDEVU_API_SECRET', ''),
    'webhook_receiver_secret' => env('WEBHOOK_RECEIVER_SECRET', ''),

    'enabled' => (bool) env('RANDEVU_API_ENABLED', true),

    /*
    | Medya kökü — API shared public proxy
    | Örn: http://127.0.0.1:8001/media  →  /media/uploads/hizmet/x.jpg
    | Dosyalar site/public (SHARED_PUBLIC_PATH) üzerinden servis edilir.
    */
    'media_base' => rtrim(env('RANDEVU_MEDIA_BASE', 'http://127.0.0.1:8001/media'), '/'),
];
