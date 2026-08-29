#!/usr/bin/env bash
# randevuajandam-klinik — production dagitim / dogrulama betigi
#
# Kullanim (sunucuda proje kok dizininde):
#   bash deploy/production-optimize.sh

set -euo pipefail

echo "==> Proje dizini: $(pwd)"

if [[ ! -f artisan ]]; then
  echo "HATA: artisan bulunamadi. Laravel kok dizinine gidin."
  exit 1
fi

if [[ ! -f .env ]]; then
  echo "HATA: .env yok."
  exit 1
fi

echo "==> Git pull (varsa)"
if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  git pull --ff-only origin main || git pull --ff-only origin master || git pull --ff-only || true
  git log -1 --oneline || true
fi

echo "==> Composer (production)"
if ! command -v composer >/dev/null 2>&1; then
  echo "HATA: composer bulunamadi. Autoloader uretilemez, dagitim durduruldu."
  exit 1
fi
composer install --no-dev --optimize-autoloader --no-interaction
composer dump-autoload --optimize --no-interaction

echo "==> Derlenmis varliklar (Vite)"
# Panel Tailwind ile derleniyor; sunucuda Node olmadigi icin public/build
# yerelde uretilip commitleniyor. Manifest yoksa panel @vite ile patlar.
if [[ ! -f public/build/manifest.json ]]; then
  echo "HATA: public/build/manifest.json yok."
  echo "      Yerelde 'npm ci && npm run build' calistirip public/build'i commitleyin."
  exit 1
fi
php -r '
$m = json_decode(file_get_contents("public/build/manifest.json"), true);
$eksik = [];
foreach (["resources/css/panel.css"] as $giris) {
    if (empty($m[$giris]["file"]) || ! is_file("public/build/".$m[$giris]["file"])) {
        $eksik[] = $giris;
    }
}
if ($eksik) {
    fwrite(STDERR, "HATA: derlenmis varlik eksik -> ".implode(", ", $eksik)."
");
    exit(1);
}
echo "  panel.css derlenmis
";
'

echo "==> .env production sertlestirme"
if grep -q '^APP_ENV=' .env; then
  sed -i.bak 's/^APP_ENV=.*/APP_ENV=production/' .env
else
  echo 'APP_ENV=production' >> .env
fi
if grep -q '^APP_DEBUG=' .env; then
  sed -i.bak 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env
else
  echo 'APP_DEBUG=false' >> .env
fi
rm -f .env.bak 2>/dev/null || true

echo "==> Storage dizinleri"
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs storage/app/public bootstrap/cache
php artisan storage:link 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

echo "==> Migrate"
php artisan migrate --force

echo "==> Onbellekler"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize 2>/dev/null || true

echo ""
php artisan about 2>/dev/null | head -n 30 || true
echo ""
echo "Bitti. Kontrol: APP_DEBUG=false, public/build guncel mi."
