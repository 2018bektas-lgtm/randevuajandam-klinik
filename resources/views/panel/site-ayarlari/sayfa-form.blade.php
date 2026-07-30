@extends('panel.layouts.app')
@php $edit = $page !== null; @endphp
@section('baslik', $edit ? 'Sayfa düzenle' : 'Yeni sayfa')
@section('sayfa_baslik', $edit ? 'Sayfa düzenle' : 'Yeni sayfa')

@section('icerik')
@include('panel.site-ayarlari._shell')

<form method="POST"
      action="{{ $edit ? route('panel.site-ayarlari.sayfalar.guncelle', $page->id) : route('panel.site-ayarlari.sayfalar.kaydet') }}"
      class="sa-wrap">
    @csrf
    <div class="sa-layout">
        <div class="sa-card">
            <div class="sa-card-head">
                <div>
                    <h3>{{ $edit ? 'Sayfayı düzenle' : 'Yeni özel sayfa' }}</h3>
                    <p class="sa-hint">İçerik sade metin olarak kaydedilir (satır sonları korunur).</p>
                </div>
                <a href="{{ route('panel.site-ayarlari.sayfalar') }}" class="sa-btn sa-btn-ghost sa-btn-sm">← Liste</a>
            </div>
            <div class="sa-card-body space-y-4">
                <div class="sa-field">
                    <label class="sa-label">Başlık *</label>
                    <input type="text" name="baslik" required class="sa-input"
                           value="{{ old('baslik', $page->baslik ?? '') }}"
                           placeholder="Örn. KVKK Aydınlatma Metni">
                </div>
                <div class="sa-field">
                    <label class="sa-label">Slug (URL)</label>
                    <input type="text" name="slug" class="sa-input font-mono text-xs"
                           value="{{ old('slug', $page->slug ?? '') }}"
                           placeholder="Boş bırakırsanız başlıktan üretilir (kvkk-aydinlatma-metni)">
                    <p class="sa-hint !mb-0">Adres: <code class="text-[11px]">/sayfa/{{ $page->slug ?? '…' }}</code></p>
                </div>
                <div class="sa-field">
                    <label class="sa-label">İçerik</label>
                    <textarea name="icerik" rows="16" class="sa-textarea font-mono text-xs"
                              placeholder="Sayfa metni…">{{ old('icerik', $page->icerik ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="sa-toggle-card {{ old('aktif', $page->aktif ?? true) ? 'is-on' : '' }}">
                        <span class="flex-1 min-w-0">
                            <strong>Yayında</strong>
                            <span class="desc">Public’te erişilebilir</span>
                        </span>
                        <span class="sa-switch">
                            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $page->aktif ?? true))>
                            <span></span>
                        </span>
                    </label>
                    <label class="sa-toggle-card {{ old('footer_goster', $page->footer_goster ?? false) ? 'is-on' : '' }}">
                        <span class="flex-1 min-w-0">
                            <strong>Footer’da göster</strong>
                            <span class="desc">Site altındaki yasal linkler</span>
                        </span>
                        <span class="sa-switch">
                            <input type="checkbox" name="footer_goster" value="1" @checked(old('footer_goster', $page->footer_goster ?? false))>
                            <span></span>
                        </span>
                    </label>
                    <div class="sa-field !mb-0">
                        <label class="sa-label">Sıra (footer)</label>
                        <input type="number" name="sira" min="0" max="9999" class="sa-input"
                               value="{{ old('sira', $page->sira ?? 0) }}">
                    </div>
                </div>
            </div>
            <div class="sa-card-foot flex flex-wrap gap-2">
                <button type="submit" class="sa-btn sa-btn-primary">{{ $edit ? 'Güncelle' : 'Oluştur' }}</button>
                <a href="{{ route('panel.site-ayarlari.sayfalar') }}" class="sa-btn sa-btn-ghost">İptal</a>
            </div>
        </div>
    </div>
</form>
@endsection
