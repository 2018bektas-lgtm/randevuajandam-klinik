# Randevu Ajandam — Klinik Sitesi

Klinik kurumsal vitrin (çok hekim) + CMS. **Klinik Kurumsal** paketi gerekir.

## Kurulum

```bash
cd kliniksitesi
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --host=127.0.0.1 --port=8003
```

### Önemli .env

```env
APP_URL=http://127.0.0.1:8003
RANDEVU_API_PLATFORM=http://127.0.0.1:8001/api/v1
RANDEVU_API_BASE_URL=http://127.0.0.1:8001/api/v1/public/clinic
RANDEVU_API_KEY=
RANDEVU_API_SECRET=
RANDEVU_MEDIA_BASE=http://127.0.0.1:8001/media
WEBHOOK_RECEIVER_SECRET=
LOCAL_ADMIN_AUTO_CREATE=true
```

Kurulum rehberi: kök [`KLINIK_WEB_SITESI_KURULUM.md`](../KLINIK_WEB_SITESI_KURULUM.md)

Panel: `/yonetim` → **Site Ayarları → Temalar**
