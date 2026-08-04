<?php

namespace App\Support;

use App\Services\PlatformApiClient;
use Illuminate\Support\Facades\Session;

/**
 * Ana site paket.yetki ile aynı kodlar — API login/me üzerinden.
 */
class PaketOzellik
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $user = Session::get('doctor_api_user');
        if (! is_array($user)) {
            return [];
        }

        $raw = $user['features'] ?? $user['paket_ozellikleri'] ?? [];
        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $raw)));
    }

    public static function label(string $code): string
    {
        return (string) (config('paket.labels.'.$code) ?? $code);
    }

    public static function upgradeUrl(): string
    {
        return (string) config('paket.upgrade_url', '/');
    }

    /**
     * API oturumunda features listesi doluysa kesin; boşsa me() yenilemeyi dene.
     * Yerel (local) oturumda true (API yok).
     */
    public static function has(string|array $codes): bool
    {
        $auth = Session::get('panel_auth');
        if (is_array($auth) && ($auth['mode'] ?? '') === 'local') {
            return true;
        }

        $have = self::all();
        if ($have === [] && Session::get('doctor_api_token')) {
            self::refreshFromApi();
            $have = self::all();
        }

        // Hâlâ boş: katı kilit (API özellik listesi gelmeli)
        if ($have === []) {
            return ! Session::get('doctor_api_token');
        }

        $need = is_array($codes) ? $codes : [$codes];
        foreach ($need as $c) {
            if ($c !== '' && in_array($c, $have, true)) {
                return true;
            }
        }

        return false;
    }

    public static function mergeIntoUser(array $doktor): array
    {
        $features = $doktor['features'] ?? $doktor['paket_ozellikleri'] ?? [];
        if (is_array($features)) {
            $doktor['features'] = array_values(array_filter(array_map('strval', $features)));
            $doktor['paket_ozellikleri'] = $doktor['features'];
        }

        return $doktor;
    }

    public static function refreshFromApi(?PlatformApiClient $api = null): void
    {
        try {
            $api = $api ?? app(PlatformApiClient::class);
            if (! $api->token() || ! $api->isConfigured()) {
                return;
            }
            $res = $api->get('/auth/me');
            $data = $res['data'] ?? [];
            if (! is_array($data) || $data === []) {
                return;
            }
            $merged = self::mergeIntoUser(array_merge($api->user() ?? [], $data));
            $api->setUser($merged);
        } catch (\Throwable) {
            // ignore network
        }
    }

    /** Public site content array için özellik kontrolü. */
    public static function contentHas(array $doktor, string|array $codes): bool
    {
        $have = $doktor['features'] ?? $doktor['paket_ozellikleri'] ?? [];
        if (! is_array($have) || $have === []) {
            // İçerik zaten API filtreliyse boş features = kısıtlı varsayma
            return true;
        }
        $need = is_array($codes) ? $codes : [$codes];
        foreach ($need as $c) {
            if ($c !== '' && in_array($c, $have, true)) {
                return true;
            }
        }

        return false;
    }
}
