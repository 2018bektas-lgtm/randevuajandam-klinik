<?php

namespace App\Services;

use App\Models\SiteOption;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Doktor sitesi API entegrasyon ayarları.
 * Anahtarlar ana sunucuda üretilir; burada yalnızca saklanır / girilir.
 * Öncelik: local DB (site_options) → .env config.
 */
class ApiConfigService
{
    public const OPT_KEY = 'api_key';

    public const OPT_SECRET = 'api_secret';

    public const OPT_PLATFORM = 'api_platform';

    public function forgetCache(): void
    {
        Cache::forget('doktorsitesi.api_config.v1');
    }

    public function all(): array
    {
        return Cache::remember('doktorsitesi.api_config.v1', 120, function () {
            return [
                'api_key' => $this->raw(self::OPT_KEY) ?: (string) config('randevu_api.api_key', ''),
                'api_secret' => $this->raw(self::OPT_SECRET) ?: (string) config('randevu_api.api_secret', ''),
                'platform' => rtrim($this->raw(self::OPT_PLATFORM) ?: (string) config('randevu_api.platform_base', ''), '/'),
                'enabled' => (bool) config('randevu_api.enabled', true),
            ];
        });
    }

    public function apiKey(): string
    {
        return (string) ($this->all()['api_key'] ?? '');
    }

    public function apiSecret(): string
    {
        return (string) ($this->all()['api_secret'] ?? '');
    }

    public function platform(): string
    {
        return (string) ($this->all()['platform'] ?? '');
    }

    public function isConfigured(): bool
    {
        $c = $this->all();

        return ($c['enabled'] ?? true)
            && filled($c['api_key'] ?? null)
            && filled($c['platform'] ?? null);
    }

    public function maskedKey(): string
    {
        $k = $this->apiKey();
        if ($k === '') {
            return '';
        }
        if (strlen($k) <= 10) {
            return str_repeat('•', max(4, strlen($k) - 2)).substr($k, -2);
        }

        return substr($k, 0, 6).str_repeat('•', 8).substr($k, -4);
    }

    /**
     * @return array{ok:bool,message:string,doktor?:array}
     */
    public function testConnection(?string $ePosta = null, ?string $sifre = null): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'message' => 'API anahtarı veya platform adresi eksik.'];
        }

        try {
            $client = app(PlatformApiClient::class);
            // Public profile — anahtar doğrulaması
            $res = $client->publicGet('/profile');
            $profile = $res['data'] ?? [];
            // Klinik: ad · Hekim: ad_soyad
            $name = trim((string) ($profile['ad'] ?? $profile['ad_soyad'] ?? ''));
            if (empty($profile) || (empty($profile['id']) && $name === '')) {
                return ['ok' => false, 'message' => 'API yanıt verdi ancak profil boş.'];
            }

            $msg = 'Bağlantı başarılı · '.($name !== '' ? $name : 'Profil');

            return ['ok' => true, 'message' => $msg, 'doktor' => $profile];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function save(array $data): void
    {
        $pairs = [];
        if (array_key_exists('api_key', $data)) {
            $pairs[self::OPT_KEY] = trim((string) $data['api_key']);
        }
        if (array_key_exists('api_secret', $data)) {
            $secret = trim((string) $data['api_secret']);
            // Boş secret = değiştirme (placeholder)
            if ($secret !== '' && ! str_contains($secret, '•')) {
                $pairs[self::OPT_SECRET] = $secret;
            }
        }
        if (array_key_exists('platform', $data)) {
            $pairs[self::OPT_PLATFORM] = rtrim(trim((string) $data['platform']), '/');
        }

        foreach ($pairs as $key => $value) {
            SiteOption::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $this->forgetCache();
        $this->syncEnvFile($pairs);
        $this->applyRuntimeConfig();

        try {
            app(SiteContentService::class)->forgetCache();
        } catch (\Throwable) {
            // ignore
        }
    }

    public function clear(): void
    {
        foreach ([self::OPT_KEY, self::OPT_SECRET, self::OPT_PLATFORM] as $key) {
            SiteOption::query()->where('key', $key)->delete();
        }
        $this->forgetCache();
        $this->syncEnvFile([
            self::OPT_KEY => '',
            self::OPT_SECRET => '',
            // platform'u silme — genelde sabittir
        ]);
        $this->applyRuntimeConfig();
    }

    /**
     * Config cache / runtime override — PlatformApiClient buradan okur.
     */
    public function applyRuntimeConfig(): void
    {
        $c = $this->all();
        if (filled($c['api_key'] ?? null)) {
            config(['randevu_api.api_key' => $c['api_key']]);
        }
        if (array_key_exists('api_secret', $c)) {
            config(['randevu_api.api_secret' => $c['api_secret'] ?? '']);
        }
        if (filled($c['platform'] ?? null)) {
            $p = rtrim((string) $c['platform'], '/');
            $publicSuffix = (string) config('randevu_api.public_path', '/public/clinic');
            $publicSuffix = '/'.trim($publicSuffix, '/');
            config([
                'randevu_api.platform_base' => $p,
                'randevu_api.base_url' => $p.$publicSuffix,
            ]);
        }
    }

    protected function raw(string $key): string
    {
        try {
            $row = SiteOption::query()->where('key', $key)->first();

            return (string) ($row?->value ?? '');
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * .env dosyasını da güncelle (deploy / artisan config için).
     *
     * @param  array<string,string>  $pairs  option keys
     */
    protected function syncEnvFile(array $pairs): void
    {
        $map = [
            self::OPT_KEY => 'RANDEVU_API_KEY',
            self::OPT_SECRET => 'RANDEVU_API_SECRET',
            self::OPT_PLATFORM => 'RANDEVU_API_PLATFORM',
        ];

        $path = base_path('.env');
        if (! File::exists($path) || ! File::isWritable($path)) {
            return;
        }

        $content = File::get($path);
        foreach ($pairs as $opt => $value) {
            $envKey = $map[$opt] ?? null;
            if (! $envKey) {
                continue;
            }
            $escaped = $this->envEscape($value);
            $pattern = '/^'.preg_quote($envKey, '/').'=.*$/m';
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $envKey.'='.$escaped, $content);
            } else {
                $content = rtrim($content)."\n{$envKey}={$escaped}\n";
            }
        }
        File::put($path, $content);
    }

    protected function envEscape(string $value): string
    {
        if ($value === '') {
            return '';
        }
        if (preg_match('/\s|#|"|\'/', $value)) {
            return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
        }

        return $value;
    }
}
