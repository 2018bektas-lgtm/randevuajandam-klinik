<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Klinik misafir randevu proxy — /public/clinic endpoints.
 */
class BookingProxyController extends Controller
{
    public function __construct(protected PlatformApiClient $api) {}

    public function services(Request $request): JsonResponse
    {
        $q = [];
        if ($request->filled('doktor_id')) {
            $q['doktor_id'] = (int) $request->query('doktor_id');
        }

        return $this->forward('GET', '/services', $q);
    }

    public function doctors(): JsonResponse
    {
        return $this->forward('GET', '/doctors');
    }

    public function slots(Request $request): JsonResponse
    {
        $date = (string) $request->query('date', '');
        $doktorId = (int) $request->query('doktor_id', 0);
        if ($date === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return response()->json(['success' => false, 'message' => 'Geçerli tarih (YYYY-MM-DD) gerekli.'], 422);
        }
        if ($doktorId < 1) {
            return response()->json(['success' => false, 'message' => 'Hekim seçimi zorunludur.'], 422);
        }

        return $this->forward('GET', '/slots', ['date' => $date, 'doktor_id' => $doktorId]);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telefon' => ['required', 'string', 'max:30'],
            'doktor_id' => ['required', 'integer', 'min:1'],
        ]);

        return $this->forward('POST', '/otp/send', $data);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telefon' => ['required', 'string', 'max:30'],
            'kod' => ['required', 'string', 'max:10'],
            'doktor_id' => ['required', 'integer', 'min:1'],
        ]);

        return $this->forward('POST', '/otp/verify', $data);
    }

    public function storeAppointment(Request $request): JsonResponse
    {
        if (filled($request->input('website_url'))) {
            return response()->json(['success' => true, 'message' => 'Talebiniz alındı.', 'data' => []]);
        }

        $captcha = app(\App\Services\RecaptchaService::class)->verify(
            $request->input('recaptcha_token'),
            'randevu',
            $request->ip()
        );
        if (! ($captcha['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $captcha['message'] ?? 'Güvenlik doğrulaması başarısız.',
            ], 422);
        }

        $data = $request->validate([
            'doktor_id' => ['required', 'integer', 'min:1'],
            'hizmet_id' => ['required', 'integer', 'min:1'],
            'tarih' => ['required', 'date_format:Y-m-d'],
            'saat' => ['required', 'string', 'max:10'],
            'ad' => ['required', 'string', 'max:100'],
            'soyad' => ['required', 'string', 'max:100'],
            'telefon' => ['required', 'string', 'max:30'],
            'e_posta' => ['nullable', 'email', 'max:255'],
            'not' => ['nullable', 'string', 'max:1000'],
            'gorusme_tipi' => ['nullable', 'in:yuz_yuze,online'],
            'kvkk_onay' => ['required', 'accepted'],
            'otp_kod' => ['nullable', 'string', 'max:10'],
            'recaptcha_token' => ['nullable', 'string'],
        ], [
            'doktor_id.required' => 'Hekim seçimi zorunludur.',
            'kvkk_onay.accepted' => 'KVKK onayı zorunludur.',
        ]);

        $payload = [
            'doktor_id' => (int) $data['doktor_id'],
            'hizmet_id' => (int) $data['hizmet_id'],
            'tarih' => $data['tarih'],
            'saat' => $data['saat'],
            'ad' => $data['ad'],
            'soyad' => $data['soyad'],
            'telefon' => $data['telefon'],
            'e_posta' => $data['e_posta'] ?? null,
            'not' => $data['not'] ?? null,
            'gorusme_tipi' => ($data['gorusme_tipi'] ?? 'yuz_yuze') === 'online' ? 'online' : 'yuz_yuze',
            'kvkk_onay' => 1,
            'website_url' => '',
            'otp_kod' => $data['otp_kod'] ?? null,
            'recaptcha_token' => $data['recaptcha_token'] ?? null,
        ];

        return $this->forward('POST', '/appointments', $payload);
    }

    public function status(): JsonResponse
    {
        if (! $this->api->isConfigured()) {
            return response()->json(['ok' => false, 'message' => 'API yapılandırılmamış.']);
        }
        try {
            [$status] = $this->api->publicProxy('GET', '/profile');
            $ok = $status >= 200 && $status < 300;

            return response()->json(['ok' => $ok, 'message' => $ok ? 'bağlı' : 'hata'], $ok ? 200 : 503);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 503);
        }
    }

    protected function forward(string $method, string $path, array $data = []): JsonResponse
    {
        [$status, $body] = $this->api->publicProxy($method, $path, $data);

        return response()->json($body, $status >= 100 && $status < 600 ? $status : 502);
    }
}
