<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PlatformApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AsistanController extends Controller
{
    public function __construct(protected PlatformApiClient $api) {}

    public function mesaj(Request $request): JsonResponse
    {
        $request->validate([
            'mesaj'              => 'sometimes|string|max:500',
            'gecmis'             => 'sometimes|array|max:20',
            'onay'               => 'sometimes|array',
            'onay.fonksiyon'     => 'required_with:onay|string',
            'onay.parametreler'  => 'required_with:onay|array',
            'secim'              => 'sometimes|string|in:sadece_kapat,kapat_ve_iptal,kapat_iptal_sms,kapat_bekleme,vazgec',
            'secim_parametreler' => 'required_with:secim|array',
        ]);

        try {
            $payload = array_filter([
                'mesaj'              => $request->input('mesaj'),
                'gecmis'             => $request->input('gecmis'),
                'onay'               => $request->input('onay'),
                'secim'              => $request->input('secim'),
                'secim_parametreler' => $request->input('secim_parametreler'),
            ], fn ($v) => $v !== null);

            $res = $this->api->post('/asistan/mesaj', $payload);

            return response()->json([
                'yanit'         => $res['yanit']         ?? ($res['message'] ?? 'Bir hata oluştu.'),
                'onay_gerekli'  => $res['onay_gerekli']  ?? null,
                'secim_gerekli' => $res['secim_gerekli'] ?? null,
            ]);
        } catch (RuntimeException $e) {
            if ($e->getCode() === 403) {
                return response()->json(['yanit' => 'AI Asistan bu paket için mevcut değil.', 'onay_gerekli' => null], 403);
            }
            return response()->json(['yanit' => 'Asistan şu an yanıt veremiyor.', 'onay_gerekli' => null], 500);
        }
    }
}
