<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Yerel panel oturumu (API entegrasyonundan bağımsız).
 * session('panel_auth') veya legacy doctor_api_token.
 */
class DoctorPanelAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $hasPanel = session()->has('panel_auth') || session()->has('doctor_api_token');

        if (! $hasPanel) {
            return redirect()
                ->route('panel.giris')
                ->with('hata', 'Yönetim paneline erişmek için giriş yapın.');
        }

        // Legacy: yalnızca API token varsa panel_auth yok → hafif oturum oluştur
        if (! session()->has('panel_auth') && session()->has('doctor_api_token')) {
            session([
                'panel_auth' => [
                    'mode' => 'api',
                    'name' => session('doctor_api_user.ad_soyad') ?? 'Hekim',
                    'email' => session('doctor_api_user.e_posta') ?? '',
                    'at' => now()->toIso8601String(),
                ],
            ]);
        }

        return $next($request);
    }
}
