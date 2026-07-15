@extends('panel.layouts.app')
@section('baslik', 'API Entegrasyonu')
@section('sayfa_baslik', 'API Entegrasyonu')

@section('icerik')
<div class="space-y-6 max-w-4xl">
    @if(session('uyari'))
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-sm">{{ session('uyari') }}</div>
    @endif

    {{-- Durum kartı --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Entegrasyon</p>
            <p class="mt-1 text-sm font-bold {{ $configured ? 'text-emerald-600' : 'text-amber-600' }}">
                {{ $configured ? 'Yapılandırıldı' : 'Eksik' }}
            </p>
        </div>
        <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Bağlantı testi</p>
            <p class="mt-1 text-sm font-bold {{ ($status['ok'] ?? false) ? 'text-emerald-600' : 'text-slate-500' }}">
                @if(!$configured)
                    —
                @elseif($status['ok'] ?? false)
                    Başarılı
                @else
                    Başarısız
                @endif
            </p>
        </div>
        <div class="bg-white border border-[#E5E7EB] rounded-2xl p-5 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Platform oturumu</p>
            <p class="mt-1 text-sm font-bold {{ $hasToken ? 'text-emerald-600' : 'text-amber-600' }}">
                {{ $hasToken ? 'Açık (Bearer)' : 'Yok — yeniden giriş' }}
            </p>
        </div>
    </div>

    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-orange-50/50 to-white">
            <h2 class="text-lg font-bold font-display text-ink">Ana sunucu API anahtarları</h2>
            <p class="text-sm text-slate-500 mt-1 leading-relaxed">
                API anahtarları <strong>doktor sitesinde üretilmez</strong>.
                Randevu Ajandam ana sunucusundaki hekim panelinden (Web Sitesi / API) oluşturup
                buraya yapıştırın. Bu site yalnızca anahtarları saklar ve senkron için kullanır.
            </p>
        </div>

        <form method="POST" action="{{ route('panel.api-entegrasyon.kaydet') }}" class="p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Platform API adresi</label>
                <input type="url" name="platform" required
                       value="{{ old('platform', $platform ?: 'http://127.0.0.1:8001/api/v1') }}"
                       placeholder="http://127.0.0.1:8001/api/v1"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-sm focus:outline-none focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/15">
                <p class="text-[11px] text-slate-400 mt-1">Sonu <code class="bg-slate-100 px-1 rounded">/api/v1</code> olmalı (public/doctor eklemeyin).</p>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">API Key (X-Api-Key)</label>
                <input type="text" name="api_key" required
                       value="{{ old('api_key', $api_key) }}"
                       placeholder="rk_..."
                       autocomplete="off"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-sm font-mono focus:outline-none focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/15">
                @if($api_key_masked)
                    <p class="text-[11px] text-slate-400 mt-1">Kayıtlı: <code>{{ $api_key_masked }}</code></p>
                @endif
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                    API Secret (X-Api-Secret) — klinik Kurumsal key
                    @if($api_secret_set)
                        <span class="text-emerald-600 font-semibold normal-case tracking-normal">· kayıtlı</span>
                    @endif
                </label>
                <input type="password" name="api_secret"
                       value=""
                       placeholder="{{ $api_secret_set ? 'Değiştirmek için yeni secret yazın (boş = aynı kalsın)' : 'sk_... veya plain secret' }}"
                       autocomplete="new-password"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-[#E5E7EB] text-sm font-mono focus:outline-none focus:border-[#C96A2B] focus:ring-2 focus:ring-[#C96A2B]/15">
            </div>

            @if($status && !($status['ok'] ?? false) && $configured)
                <div class="p-3 rounded-xl bg-red-50 border border-red-100 text-red-700 text-xs">
                    Son test: {{ $status['message'] ?? 'Başarısız' }}
                </div>
            @endif
            @if($status && ($status['ok'] ?? false))
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs">
                    {{ $status['message'] }}
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold font-display">
                    Anahtarları kaydet
                </button>
                @if($configured)
                    <button type="submit" form="testForm" class="px-5 py-2.5 rounded-xl border border-[#E5E7EB] text-xs font-bold hover:bg-slate-50">
                        Bağlantıyı test et
                    </button>
                    <button type="submit" form="clearForm" class="px-5 py-2.5 rounded-xl border border-red-100 text-red-600 text-xs font-bold hover:bg-red-50"
                            onclick="return confirm('API anahtarları silinsin mi?')">
                        Anahtarları temizle
                    </button>
                @endif
            </div>
        </form>
    </div>

    <form id="testForm" method="POST" action="{{ route('panel.api-entegrasyon.test') }}">@csrf</form>
    <form id="clearForm" method="POST" action="{{ route('panel.api-entegrasyon.temizle') }}">@csrf</form>

    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 text-sm text-slate-600 space-y-2">
        <p class="font-bold text-ink font-display text-sm">Nasıl alınır?</p>
        <ol class="list-decimal pl-5 space-y-1.5 text-xs leading-relaxed">
            <li>Ana sunucu (Randevu Ajandam) hekim paneline giriş yapın.</li>
            <li><strong>Web Sitesi / API</strong> bölümünden domain kaydı ve API anahtarı oluşturun.</li>
            <li>Gösterilen <code class="bg-white px-1 rounded border">API Key</code> ve <code class="bg-white px-1 rounded border">Secret</code> değerlerini kopyalayın.</li>
            <li>Bu sayfaya yapıştırıp kaydedin; ardından platform e-posta/şifrenizle panel girişi yapın.</li>
        </ol>
        <p class="text-[11px] text-slate-400 pt-1">
            Site ayarları (logo, menü, slider…) yerel veritabanındadır ve API olmadan da yönetilebilir.
            Randevu, hizmet, blog ve finans için platform oturumu gerekir.
        </p>
    </div>
</div>
@endsection
