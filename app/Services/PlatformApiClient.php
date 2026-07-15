<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use RuntimeException;

/**
 * Bidirectional client to Randevu Ajandam API (api/ project).
 * Requires X-Api-Key (+ X-Api-Secret when configured) and Bearer for panel.
 * Credentials: ApiConfigService (DB) → .env fallback.
 */
class PlatformApiClient
{
    public function __construct(protected ?ApiConfigService $config = null)
    {
        $this->config = $config ?? app(ApiConfigService::class);
        $this->config->applyRuntimeConfig();
    }

    public function apiRoot(): string
    {
        $raw = $this->config->platform()
            ?: (string) (config('randevu_api.platform_base') ?: config('randevu_api.base_url'));
        $raw = rtrim($raw, '/');
        $raw = preg_replace('#/(public|doctor)$#', '', $raw) ?? $raw;

        return $raw;
    }

    public function publicBase(): string
    {
        // Klinik sitesi: /public/clinic ; hekim sitesi: /public
        $suffix = (string) config('randevu_api.public_path', '/public/clinic');
        $suffix = '/'.trim($suffix, '/');

        return $this->apiRoot().$suffix;
    }

    public function doctorBase(): string
    {
        // Klinik sitesi: /clinic/doctor ; hekim sitesi: /doctor
        $suffix = (string) config('randevu_api.doctor_path', '/clinic/doctor');
        $suffix = '/'.trim($suffix, '/');

        return $this->apiRoot().$suffix;
    }

    public function apiKey(): string
    {
        return $this->config->apiKey();
    }

    public function apiSecret(): string
    {
        return $this->config->apiSecret();
    }

    public function isConfigured(): bool
    {
        return $this->config->isConfigured();
    }

    public function token(): ?string
    {
        return Session::get('doctor_api_token');
    }

    public function setToken(?string $token): void
    {
        if ($token) {
            Session::put('doctor_api_token', $token);
        } else {
            Session::forget('doctor_api_token');
        }
    }

    public function setUser(array $doktor): void
    {
        Session::put('doctor_api_user', $doktor);
    }

    public function user(): ?array
    {
        return Session::get('doctor_api_user');
    }

    public function http(bool $withBearer = false): PendingRequest
    {
        $headers = [
            'X-Api-Key' => $this->apiKey(),
            'Accept' => 'application/json',
        ];

        $secret = $this->apiSecret();
        if ($secret !== '') {
            $headers['X-Api-Secret'] = $secret;
        }

        $req = Http::acceptJson()
            ->timeout(25)
            ->withHeaders($headers);

        if ($withBearer && $this->token()) {
            $req = $req->withToken($this->token());
        }

        return $req;
    }

    public function login(string $ePosta, string $sifre): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('API anahtarı yapılandırılmamış. Ana sunucudan aldığınız key/secret bilgisini Entegrasyon sayfasına girin.', 0);
        }

        $res = $this->http()->post($this->doctorBase().'/auth/login', [
            'e_posta' => $ePosta,
            'sifre' => $sifre,
        ]);

        return $this->decode($res);
    }

    public function logout(): void
    {
        try {
            if ($this->token() && $this->isConfigured()) {
                $this->http(true)->post($this->doctorBase().'/auth/logout');
            }
        } catch (\Throwable) {
            // ignore
        }
        $this->setToken(null);
        Session::forget('doctor_api_user');
    }

    public function get(string $path, array $query = []): array
    {
        $this->assertReady();
        $res = $this->http(true)->get($this->doctorBase().$path, $query);

        return $this->decode($res);
    }

    public function put(string $path, array $data = []): array
    {
        $this->assertReady();
        $res = $this->http(true)->put($this->doctorBase().$path, $data);

        return $this->decode($res);
    }

    public function post(string $path, array $data = []): array
    {
        $this->assertReady();
        $res = $this->http(true)->post($this->doctorBase().$path, $data);

        return $this->decode($res);
    }

    public function delete(string $path): array
    {
        $this->assertReady();
        $res = $this->http(true)->delete($this->doctorBase().$path);

        return $this->decode($res);
    }

    public function postMultipart(string $path, array $fields = [], array $files = []): array
    {
        $this->assertReady();
        $multipart = [];
        foreach ($this->flattenMultipartFields($fields) as $name => $value) {
            $multipart[] = ['name' => $name, 'contents' => (string) $value];
        }

        foreach ($files as $name => $file) {
            if (is_array($file)) {
                foreach ($file as $i => $f) {
                    if (! $f) {
                        continue;
                    }
                    $multipart[] = [
                        'name' => $name.'['.$i.']',
                        'contents' => fopen($f->getRealPath(), 'r'),
                        'filename' => $f->getClientOriginalName(),
                    ];
                }
            } elseif ($file) {
                $multipart[] = [
                    'name' => $name,
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ];
            }
        }

        $res = $this->http(true)->asMultipart()->post($this->doctorBase().$path, $multipart);

        return $this->decode($res);
    }

    /**
     * Flatten nested fields for multipart (alanlar[0][etiket]=...).
     *
     * @param  array<string, mixed>  $fields
     * @return array<string, string|int|float>
     */
    protected function flattenMultipartFields(array $fields, string $prefix = ''): array
    {
        $out = [];
        foreach ($fields as $key => $value) {
            $name = $prefix === '' ? (string) $key : $prefix.'['.$key.']';
            if (is_bool($value)) {
                $out[$name] = $value ? '1' : '0';
            } elseif ($value === null) {
                continue;
            } elseif (is_array($value)) {
                $out = array_merge($out, $this->flattenMultipartFields($value, $name));
            } else {
                $out[$name] = $value;
            }
        }

        return $out;
    }

    public function publicGet(string $path, array $query = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('API anahtarı yapılandırılmamış.', 0);
        }
        $res = $this->http()->get($this->publicBase().$path, $query);

        return $this->decode($res);
    }

    public function publicPost(string $path, array $data = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('API anahtarı yapılandırılmamış.', 0);
        }
        $res = $this->http()->post($this->publicBase().$path, $data);

        return $this->decode($res);
    }

    /**
     * Public API call that preserves HTTP status for proxying to browser.
     * Does not throw on 4xx — returns [status, body].
     *
     * @return array{0:int,1:array}
     */
    public function publicProxy(string $method, string $path, array $queryOrBody = []): array
    {
        if (! $this->isConfigured()) {
            return [503, ['success' => false, 'message' => 'API entegrasyonu yapılandırılmamış.']];
        }

        $url = $this->publicBase().$path;
        $req = $this->http();

        try {
            $res = match (strtoupper($method)) {
                'GET' => $req->get($url, $queryOrBody),
                'POST' => $req->post($url, $queryOrBody),
                default => throw new RuntimeException('Desteklenmeyen metod.'),
            };
        } catch (\Throwable $e) {
            return [502, ['success' => false, 'message' => 'Ana sunucuya bağlanılamadı: '.$e->getMessage()]];
        }

        $json = $res->json();
        if (! is_array($json)) {
            $json = ['success' => $res->successful(), 'message' => $res->body() ?: 'Boş yanıt'];
        }

        return [$res->status(), $json];
    }

    protected function assertReady(): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('API entegrasyonu yok. Ana sunucudan aldığınız anahtarları Entegrasyon sayfasına girin.', 0);
        }
        if (! $this->token()) {
            throw new RuntimeException('Platform oturumu yok. API anahtarları kaydedildikten sonra e-posta/şifre ile yeniden giriş yapın.', 401);
        }
    }

    protected function decode(Response $res): array
    {
        $json = $res->json() ?? [];

        if ($res->status() === 401) {
            $msg = $json['message'] ?? 'Oturum sona erdi veya API anahtarı geçersiz.';
            // Geçersiz API anahtarı → token sil ama paneli düşürme (middleware yönlendirir)
            if (str_contains(mb_strtolower($msg), 'api anahtar') || str_contains(mb_strtolower($msg), 'api key')) {
                $this->setToken(null);
                throw new RuntimeException($msg, 403);
            }
            $this->setToken(null);
            throw new RuntimeException($msg, 401);
        }

        if (! $res->successful()) {
            $msg = $json['message']
                ?? (isset($json['errors']) ? collect($json['errors'])->flatten()->implode(' ') : null)
                ?? ('API hatası ('.$res->status().')');
            throw new RuntimeException($msg, $res->status());
        }

        return $json;
    }
}
