<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use Illuminate\Http\Request;
use RuntimeException;

class TwoFactorController extends Controller
{
    public function __construct(protected PlatformApiClient $api) {}

    public function challengeForm(Request $request)
    {
        if (! $request->session()->has('two_factor.challenge_token')) {
            return redirect()->route('panel.giris');
        }

        return view('panel.auth.two_factor_challenge');
    }

    public function challengeVerify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:20'],
        ], [
            'code.required' => 'Doğrulama kodu zorunludur.',
        ]);

        $challenge = (string) $request->session()->get('two_factor.challenge_token', '');
        $email = (string) $request->session()->get('two_factor.email', '');
        if ($challenge === '') {
            return redirect()->route('panel.giris')->with('hata', 'Oturum süresi doldu. Tekrar giriş yapın.');
        }

        try {
            $res = $this->api->verifyTwoFactor($challenge, $request->input('code'));
        } catch (RuntimeException $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }

        $token = $res['data']['token'] ?? null;
        $doktor = $res['data']['doktor'] ?? null;
        if (! $token || ! $doktor) {
            return back()->withErrors(['code' => 'Doğrulama tamamlanamadı. Tekrar deneyin.']);
        }

        $d = is_array($doktor) ? $doktor : (array) $doktor;
        $expectedDoktorId = $request->session()->get('two_factor.expected_doktor_id');
        $loginDoktorId = $d['id'] ?? null;
        if ($expectedDoktorId && $loginDoktorId && (string) $expectedDoktorId !== (string) $loginDoktorId) {
            $request->session()->forget(['two_factor.challenge_token', 'two_factor.email', 'two_factor.expected_doktor_id']);

            return redirect()->route('panel.giris')->with('hata', 'Bu hesabın bu siteye giriş yetkisi bulunmamaktadır.');
        }

        $request->session()->forget(['two_factor.challenge_token', 'two_factor.email', 'two_factor.expected_doktor_id']);
        $this->api->setToken($token);
        $this->api->setUser($d);

        session([
            'panel_auth' => [
                'mode' => 'api',
                'name' => $d['ad_soyad'] ?? 'Hekim',
                'email' => $d['e_posta'] ?? $email,
                'at' => now()->toIso8601String(),
            ],
        ]);

        return redirect()->route('panel.dashboard')->with('basari', 'Hoş geldiniz. Platform ile senkron.');
    }

    public function challengeCancel(Request $request)
    {
        $request->session()->forget(['two_factor.challenge_token', 'two_factor.email']);

        return redirect()->route('panel.giris');
    }

    public function setupForm()
    {
        try {
            $status = $this->api->get('/two-factor')['data'] ?? [];
            $enabled = (bool) ($status['enabled'] ?? false);
            $setup = null;
            if (! $enabled) {
                $setup = $this->api->post('/two-factor/setup')['data'] ?? null;
            }
        } catch (RuntimeException $e) {
            if ($e->getCode() === 401) {
                return redirect()->route('panel.giris')->with('hata', $e->getMessage());
            }

            return view('panel.two_factor.setup', [
                'error' => $e->getMessage(),
                'enabled' => false,
                'setup' => null,
                'status' => [],
            ]);
        }

        return view('panel.two_factor.setup', [
            'error' => null,
            'enabled' => $enabled,
            'setup' => $setup,
            'status' => $status,
        ]);
    }

    public function setupConfirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:12'],
        ]);

        try {
            $res = $this->api->post('/two-factor/confirm', [
                'code' => $request->input('code'),
            ]);
        } catch (RuntimeException $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }

        $recovery = $res['data']['recovery_codes'] ?? [];

        return redirect()
            ->route('panel.two-factor')
            ->with('basari', $res['message'] ?? 'İki adımlı doğrulama açıldı.')
            ->with('two_factor_recovery_codes', $recovery);
    }

    public function disable(Request $request)
    {
        $request->validate([
            'sifre' => ['required', 'string'],
            'code' => ['required', 'string', 'min:6', 'max:20'],
        ]);

        try {
            $res = $this->api->post('/two-factor/disable', [
                'sifre' => $request->input('sifre'),
                'code' => $request->input('code'),
            ]);
        } catch (RuntimeException $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }

        return redirect()
            ->route('panel.two-factor')
            ->with('basari', $res['message'] ?? 'İki adımlı doğrulama kapatıldı.');
    }

    public function regenerateRecovery(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:20'],
        ]);

        try {
            $res = $this->api->post('/two-factor/recovery', [
                'code' => $request->input('code'),
            ]);
        } catch (RuntimeException $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }

        $recovery = $res['data']['recovery_codes'] ?? [];

        return redirect()
            ->route('panel.two-factor')
            ->with('basari', $res['message'] ?? 'Yeni yedek kodlar oluşturuldu.')
            ->with('two_factor_recovery_codes', $recovery);
    }
}
