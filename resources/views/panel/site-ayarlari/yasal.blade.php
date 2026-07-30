@extends('panel.layouts.app')
@section('baslik', 'Site Ayarları · Yasal Metinler')
@section('sayfa_baslik', 'Site Ayarları · Yasal')

@section('icerik')
@include('panel.site-ayarlari._shell')

<form method="POST" action="{{ route('panel.site-ayarlari.yasal.kaydet') }}" class="sa-wrap">
    @csrf
    <div class="sa-layout">
        <div class="sa-card">
            <div class="sa-card-head">
                <div>
                    <h3>Klinik sitesi yasal metinleri</h3>
                    <p class="sa-hint">
                        Bu metinler <strong>sizin klinik sitenizin</strong> ziyaretçileri ve randevu formları içindir.
                        Randevu Ajandam platformunun (randevuajandam.com) yasal sayfalarından
                        <strong>ayrıdır</strong> — oradaki metinler SaaS aboneliği içindir.
                    </p>
                </div>
                <span class="sa-badge">KVKK</span>
            </div>
            <div class="sa-card-body space-y-4">
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-3 text-[11px] text-amber-950 leading-relaxed">
                    <strong>Öneri:</strong> Veri sorumlusu olarak kliniğinizin unvanı, iletişim bilgisi ve
                    hangi verileri (ad, telefon, randevu notu vb.) hangi amaçla işlediğinizi yazın.
                    Boş bırakırsanız sitede kısa bir varsayılan metin gösterilir; lütfen kendi metninizi kaydedin.
                </div>

                <div class="sa-field">
                    <label class="sa-label">KVKK aydınlatma metni</label>
                    <p class="sa-hint !mt-0 !mb-1.5">Public: <a href="{{ $publicUrls['kvkk'] }}" target="_blank" class="text-brand-600 font-semibold underline">{{ $publicUrls['kvkk'] }}</a></p>
                    <textarea name="kvkk" rows="12" class="sa-textarea font-mono text-xs"
                              placeholder="KVKK Aydınlatma Metni…">{{ old('kvkk', $ayarlar['kvkk']) }}</textarea>
                </div>

                <div class="sa-field">
                    <label class="sa-label">Gizlilik politikası</label>
                    <p class="sa-hint !mt-0 !mb-1.5">Public: <a href="{{ $publicUrls['gizlilik'] }}" target="_blank" class="text-brand-600 font-semibold underline">{{ $publicUrls['gizlilik'] }}</a></p>
                    <textarea name="gizlilik" rows="10" class="sa-textarea font-mono text-xs"
                              placeholder="Gizlilik politikası…">{{ old('gizlilik', $ayarlar['gizlilik']) }}</textarea>
                </div>

                <div class="sa-field">
                    <label class="sa-label">Kullanım koşulları (opsiyonel)</label>
                    <p class="sa-hint !mt-0 !mb-1.5">Public: <a href="{{ $publicUrls['kullanim'] }}" target="_blank" class="text-brand-600 font-semibold underline">{{ $publicUrls['kullanim'] }}</a></p>
                    <textarea name="kullanim" rows="8" class="sa-textarea font-mono text-xs"
                              placeholder="Site kullanım koşulları…">{{ old('kullanim', $ayarlar['kullanim']) }}</textarea>
                </div>
            </div>
            <div class="sa-card-foot">
                <button type="submit" class="sa-btn sa-btn-primary">Yasal metinleri kaydet</button>
            </div>
        </div>
    </div>
</form>
@endsection
