<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * @param  array{site_key?: string|null, secret_key?: string|null}  $override
     */
    public function siteKey(array $override = []): string
    {
        $key = trim((string) ($override['site_key'] ?? ''));
        if ($key !== '') {
            return $key;
        }

        try {
            $fromSettings = trim((string) app(SiteSettingsService::class)->option('seo_recaptcha_site_key', ''));
            if ($fromSettings !== '') {
                return $fromSettings;
            }
        } catch (\Throwable) {
            //
        }

        return trim((string) config('recaptcha.site_key', ''));
    }

    /**
     * @param  array{site_key?: string|null, secret_key?: string|null}  $override
     */
    public function secretKey(array $override = []): string
    {
        $key = trim((string) ($override['secret_key'] ?? ''));
        if ($key !== '') {
            return $key;
        }

        try {
            $fromSettings = trim((string) app(SiteSettingsService::class)->option('seo_recaptcha_secret_key', ''));
            if ($fromSettings !== '') {
                return $fromSettings;
            }
        } catch (\Throwable) {
            //
        }

        return trim((string) config('recaptcha.secret_key', ''));
    }

    public function isEnabled(array $override = []): bool
    {
        if (! config('recaptcha.enabled', true)) {
            return false;
        }

        try {
            $flag = app(SiteSettingsService::class)->option('seo_recaptcha_enabled', '1');
            if ($flag === '0' || $flag === 0 || $flag === false) {
                return false;
            }
        } catch (\Throwable) {
            //
        }

        return $this->siteKey($override) !== '' && $this->secretKey($override) !== '';
    }

    /**
     * @param  array{site_key?: string|null, secret_key?: string|null}  $override
     * @return array{ok: bool, score?: float, action?: string, message?: string, skipped?: bool}
     */
    public function verify(?string $token, string $expectedAction = '', ?string $remoteIp = null, array $override = []): array
    {
        if (! config('recaptcha.enabled', true)) {
            return ['ok' => true, 'skipped' => true];
        }

        $secret = $this->secretKey($override);
        if ($secret === '') {
            if (config('recaptcha.soft_fail_when_unconfigured', true)) {
                return ['ok' => true, 'skipped' => true];
            }

            return ['ok' => false, 'message' => 'reCAPTCHA yapılandırılmamış.'];
        }

        $token = trim((string) $token);
        if ($token === '') {
            return ['ok' => false, 'message' => 'Güvenlik doğrulaması eksik. Sayfayı yenileyip tekrar deneyin.'];
        }

        try {
            $data = Http::asForm()->timeout(8)->post((string) config('recaptcha.verify_url'), array_filter([
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $remoteIp,
            ]))->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA network error', ['error' => $e->getMessage()]);
            if (config('recaptcha.soft_fail_when_unconfigured', true)) {
                return ['ok' => true, 'skipped' => true];
            }

            return ['ok' => false, 'message' => 'Güvenlik doğrulaması yapılamadı.'];
        }

        if (empty($data['success'])) {
            return ['ok' => false, 'message' => 'Güvenlik doğrulaması başarısız.'];
        }

        $score = isset($data['score']) ? (float) $data['score'] : 1.0;
        $threshold = (float) config('recaptcha.score_threshold', 0.5);
        if ($score < $threshold) {
            return [
                'ok' => false,
                'score' => $score,
                'message' => 'Güvenlik kontrolü başarısız. Lütfen daha sonra tekrar deneyin.',
            ];
        }

        return ['ok' => true, 'score' => $score, 'action' => (string) ($data['action'] ?? '')];
    }
}
