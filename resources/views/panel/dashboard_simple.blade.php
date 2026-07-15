@extends('panel.layouts.app')
@section('baslik', 'Panel Özeti')
@section('sayfa_baslik', 'Panel Özeti')

@section('icerik')
<div class="max-w-3xl space-y-5">
    <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0
                {{ !empty($apiMissing) ? 'bg-amber-50 text-amber-600' : (!empty($needLogin) ? 'bg-sky-50 text-sky-600' : 'bg-red-50 text-red-600') }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h2 class="text-lg font-bold font-display text-ink">
                    @if(!empty($apiMissing))
                        Platform entegrasyonu gerekli
                    @elseif(!empty($needLogin))
                        Platform oturumu gerekli
                    @else
                        Platform verisi alınamadı
                    @endif
                </h2>
                <p class="text-sm text-slate-500 mt-1.5 leading-relaxed">
                    {{ $message ?? $error ?? 'Bir sorun oluştu.' }}
                </p>
                @if(!empty($error) && empty($message))
                    <p class="text-xs text-red-600 mt-2 font-mono bg-red-50 border border-red-100 rounded-lg p-2">{{ $error }}</p>
                @endif

                <div class="flex flex-wrap gap-2 mt-5">
                    <a href="{{ route('panel.api-entegrasyon') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#C96A2B] text-white text-xs font-bold font-display hover:bg-[#B55A20]">
                        API Entegrasyonu
                    </a>
                    <a href="{{ route('panel.site-ayarlari.genel') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E5E7EB] text-xs font-bold hover:bg-slate-50">
                        Site ayarları (yerel)
                    </a>
                    @if(!empty($needLogin))
                        <a href="{{ route('panel.giris') }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-sky-200 text-sky-700 text-xs font-bold hover:bg-sky-50">
                            Yeniden giriş
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 text-xs text-slate-600 leading-relaxed">
        <strong class="text-ink">Not:</strong>
        Klinik site ayarları (logo, menü, slider, SEO) bu sitenin yerel veritabanındadır.
        <strong>Hekim menüsü</strong> (randevu, hasta, hizmet, blog, galeri, finans, profil) platform hekim hesabınıza aittir;
        kliniğe bağlı e-posta/şifrenizle giriş yapmanız gerekir.
    </div>
</div>
@endsection
