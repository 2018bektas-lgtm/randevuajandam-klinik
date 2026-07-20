# Klinik sitesi deploy

Ana platform: `randevuajandam-site` + `randevuajandam-api`.  
Bu repo: klinik kurumsal vitrin + CMS (çok hekim, API key ile).

## Sunucu

```powershell
cd deploy
.\deploy.ps1 -Target klinik
```

Laravel kök: `/home/u195737737/apps/randevuajandam-klinik`

## .env kritik

```env
RANDEVU_API_PLATFORM=https://api.DOMAIN/api/v1
# Klinik public/doctor path config/randevu_api.php içinde
RANDEVU_API_KEY=...
RANDEVU_API_SECRET=...
```

Panel: `/yonetim` → API entegrasyon + Site Ayarları.
