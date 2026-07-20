<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApiConfigService;
use App\Services\PlatformApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(
        protected PlatformApiClient $api,
        protected ApiConfigService $apiConfig,
    ) {}

    public function girisFormu()
    {
        if (session('panel_auth') || session('doctor_api_token')) {
            return redirect()->route('panel.dashboard');
        }

        $this->ensureLocalAdmin();

        $apiLive = false;
        $apiError = null;
        if ($this->apiConfig->isConfigured()) {
            $test = $this->apiConfig->testConnection();
            $apiLive = (bool) ($test['ok'] ?? false);
            if (! $apiLive) {
                $apiError = $test['message'] ?? 'API anahtarı doğrulanamadı.';
            }
        }

        return view('panel.auth.giris', [
            'apiConfigured' => $this->apiConfig->isConfigured(),
            'apiLive' => $apiLive,
            'apiError' => $apiError,
            'apiMasked' => $this->apiConfig->maskedKey(),
            'showLocalHints' => $this->shouldShowLocalHints(),
            'localAdminEmail' => $this->localAdminEmail(),
        ]);
    }

    public function giris(Request $request)
    {
        $request->validate([
            'e_posta' => ['required', 'email'],
            'sifre' => ['required', 'string'],
        ], [
            'e_posta.required' => 'E-posta zorunludur.',
            'sifre.required' => 'Şifre zorunludur.',
        ]);

        $this->ensureLocalAdmin();

        $email = strtolower(trim($request->e_posta));
        $sifre = $request->sifre;

        $apiOk = false;
        $apiError = null;
        $doktor = null;
        $keyBroken = false;

        if ($this->apiConfig->isConfigured()) {
            $probe = $this->apiConfig->testConnection();
            if (! ($probe['ok'] ?? false)) {
                $keyBroken = true;
                $apiError = $probe['message'] ?? 'Geçersiz veya pasif API anahtarı.';
            }
        }

        if ($this->apiConfig->isConfigured() && ! $keyBroken) {
            try {
                $res = $this->api->login($email, $sifre);
                $data = $res['data'] ?? [];

                // 2FA gerekli — token henüz yok
                if (! empty($data['requires_two_factor']) && ! empty($data['challenge_token'])) {
                    session([
                        'two_factor.challenge_token' => $data['challenge_token'],
                        'two_factor.email' => $email,
                    ]);

                    return redirect()->route('panel.two-factor.challenge');
                }

                $token = $data['token'] ?? null;
                $doktor = $data['doktor'] ?? null;
                if ($token && $doktor) {
                    $this->api->setToken($token);
                    $this->api->setUser(is_array($doktor) ? $doktor : (array) $doktor);
                    $apiOk = true;
                    $this->syncLocalUser($email, $sifre, $doktor);
                }
            } catch (RuntimeException $e) {
                $apiError = $e->getMessage();
                $this->api->setToken(null);
                if ($this->isApiKeyError($apiError)) {
                    $keyBroken = true;
                }
            }
        }

        if (! $apiOk) {
            $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
            if (! $user || ! Hash::check($sifre, $user->password)) {
                if ($keyBroken || $this->isApiKeyError((string) $apiError)) {
                    return back()->withInput()->with(
                        'hata',
                        'API anahtarı geçersiz veya pasif. Platform girişi yapılamaz. '
                        .'Yerel yönetici hesabıyla girip API Entegrasyonu sayfasından anahtarı güncelleyin.'
                    );
                }

                if ($apiError && ! $this->isApiKeyError($apiError)) {
                    return back()->withInput()->with('hata', $apiError);
                }

                return back()->withInput()->with('hata', 'E-posta veya şifre hatalı.');
            }

            session([
                'panel_auth' => [
                    'mode' => 'local',
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'at' => now()->toIso8601String(),
                ],
            ]);
            session(['doctor_api_user' => [
                'ad_soyad' => $user->name,
                'e_posta' => $user->email,
            ]]);
            $this->api->setToken(null);

            if (! $this->apiConfig->isConfigured() || $keyBroken) {
                return redirect()
                    ->route('panel.api-entegrasyon')
                    ->with('uyari', $apiError
                        ?: 'Platform entegrasyonu yok veya anahtar geçersiz. Ana sunucuda üretilen Key/Secret bilgisini buraya girin.');
            }

            return redirect()->route('panel.dashboard')
                ->with('basari', 'Yerel oturum açıldı. Hekim işlemleri (randevu, hasta, finans) için platform hekim hesabıyla giriş yapın.');
        }

        $d = is_array($doktor) ? $doktor : (array) $doktor;
        session([
            'panel_auth' => [
                'mode' => 'api',
                'name' => $d['ad_soyad'] ?? 'Hekim',
                'email' => $d['e_posta'] ?? $email,
                'at' => now()->toIso8601String(),
            ],
        ]);

        return redirect()->route('panel.dashboard')
            ->with('basari', 'Hoş geldiniz. Hekim menüsündeki tüm işlemler sizin hesabınıza bağlıdır.');
    }

    public function cikis()
    {
        $this->api->logout();
        session()->forget(['panel_auth', 'doctor_api_user', 'doctor_api_token']);

        return redirect()->route('panel.giris')->with('basari', 'Çıkış yapıldı.');
    }

    protected function shouldShowLocalHints(): bool
    {
        return app()->environment(['local', 'development', 'testing']);
    }

    protected function localAdminEmail(): string
    {
        return (string) env('LOCAL_ADMIN_EMAIL', 'admin@site.local');
    }

    protected function localAdminPassword(): string
    {
        return (string) env('LOCAL_ADMIN_PASSWORD', 'admin123');
    }

    protected function ensureLocalAdmin(): void
    {
        // Yalnızca local/testing — production ve staging'de asla
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        if (! filter_var(env('LOCAL_ADMIN_AUTO_CREATE', true), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        try {
            $email = $this->localAdminEmail();
            $user = User::query()->where('email', $email)->first();
            if (! $user) {
                User::query()->create([
                    'name' => 'Site Yönetici',
                    'email' => $email,
                    'password' => Hash::make($this->localAdminPassword()),
                ]);
            }
        } catch (\Throwable) {
            // ignore
        }
    }

    protected function isApiKeyError(string $msg): bool
    {
        $m = mb_strtolower($msg);

        return str_contains($m, 'api anahtar')
            || str_contains($m, 'api key')
            || str_contains($m, 'pasif')
            || str_contains($m, 'geçersiz veya');
    }

    protected function syncLocalUser(string $email, string $sifre, mixed $doktor): void
    {
        try {
            $d = is_array($doktor) ? $doktor : (array) $doktor;
            $name = $d['ad_soyad'] ?? ($d['name'] ?? 'Hekim');
            $user = User::query()->firstOrNew(['email' => $email]);
            $user->name = $name;
            $user->password = Hash::make($sifre);
            $user->save();
        } catch (\Throwable) {
            // ignore
        }
    }
}
