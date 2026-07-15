@extends('panel.layouts.app')
@section('baslik', 'Site Ayarları · İletişim')
@section('sayfa_baslik', 'Site Ayarları · İletişim')

@section('icerik')
@include('panel.site-ayarlari._shell')

<form method="POST" action="{{ route('panel.site-ayarlari.iletisim.kaydet') }}" class="sa-wrap">
    @csrf
    <div class="sa-layout">
        <div class="sa-card">
            <div class="sa-card-head">
                <div>
                    <h3>Sayfa metinleri</h3>
                    <p class="sa-hint">İletişim / online randevu sayfasının üst başlık alanı.</p>
                </div>
                <span class="sa-badge">İletişim</span>
            </div>
            <div class="sa-card-body">
                <div class="sa-field">
                    <label class="sa-label">Sayfa başlığı</label>
                    <input type="text" name="baslik" value="{{ $ayarlar['baslik'] }}" class="sa-input"
                           placeholder="İletişim & online randevu">
                </div>
                <div class="sa-field">
                    <label class="sa-label">Alt metin</label>
                    <textarea name="alt_metin" rows="3" class="sa-textarea"
                              placeholder="Hesap oluşturmadan randevu talebi bırakabilirsiniz.">{{ $ayarlar['alt_metin'] }}</textarea>
                </div>
            </div>
        </div>

        <div class="sa-card">
            <div class="sa-card-head">
                <div>
                    <h3>Görünür bloklar</h3>
                    <p class="sa-hint">Sayfada hangi bileşenler gösterilsin?</p>
                </div>
            </div>
            <div class="sa-card-body space-y-2.5">
                <label class="sa-toggle-card {{ $ayarlar['form_goster'] ? 'is-on' : '' }}">
                    <span class="sa-toggle-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <span class="flex-1 min-w-0">
                        <strong>Misafir randevu formu</strong>
                        <span class="desc">Kayıtsız randevu talebi formu</span>
                    </span>
                    <span class="sa-switch">
                        <input type="checkbox" name="form_goster" value="1" @checked($ayarlar['form_goster'])>
                        <span></span>
                    </span>
                </label>
                <label class="sa-toggle-card {{ $ayarlar['harita_goster'] ? 'is-on' : '' }}">
                    <span class="sa-toggle-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <span class="flex-1 min-w-0">
                        <strong>Harita</strong>
                        <span class="desc">Google Maps gömülü harita</span>
                    </span>
                    <span class="sa-switch">
                        <input type="checkbox" name="harita_goster" value="1" @checked($ayarlar['harita_goster'])>
                        <span></span>
                    </span>
                </label>
                <label class="sa-toggle-card {{ $ayarlar['saatler_goster'] ? 'is-on' : '' }}">
                    <span class="sa-toggle-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="flex-1 min-w-0">
                        <strong>Çalışma saatleri</strong>
                        <span class="desc">API’den gelen mesai tablosu</span>
                    </span>
                    <span class="sa-switch">
                        <input type="checkbox" name="saatler_goster" value="1" @checked($ayarlar['saatler_goster'])>
                        <span></span>
                    </span>
                </label>
            </div>
        </div>
    </div>

    <div class="sa-actions">
        <p class="sa-hint m-0">İletişim sayfası ayarları yerel veritabanında saklanır.</p>
        <button type="submit" class="sa-btn sa-btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Kaydet
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>document.addEventListener('DOMContentLoaded', () => window.saBindToggleCards?.());</script>
@endpush
