@extends('panel.layouts.app')
@section('baslik', 'Site Ayarları · Sayfalar')
@section('sayfa_baslik', 'Site Ayarları · Sayfalar')

@section('icerik')
@include('panel.site-ayarlari._shell')

<div class="sa-wrap">
    <div class="sa-layout sa-layout-wide">
        <div class="sa-card">
            <div class="sa-card-head !items-center">
                <div>
                    <h3>Özel sayfalar</h3>
                    <p class="sa-hint">
                        KVKK, gizlilik, kampanya vb. serbest sayfalar. Oluşturduktan sonra
                        <strong>Menü</strong>’den “Sayfa: …” seçerek üst menüye ekleyin;
                        footera koymak için sayfada <strong>Footer’da göster</strong> işaretleyin.
                    </p>
                </div>
                <a href="{{ route('panel.site-ayarlari.sayfalar.yeni') }}" class="sa-btn sa-btn-primary sa-btn-sm">
                    + Yeni sayfa
                </a>
            </div>
            <div class="sa-card-body">
                @if($pages->isEmpty())
                    <div class="sa-callout">
                        Henüz özel sayfa yok. Örnek: “KVKK Aydınlatma Metni”, “Gizlilik Politikası”.
                        Platform (randevuajandam.com) yasal metinleri SaaS içindir; vitrin ziyaretçileri için
                        buradan kendi metinlerinizi ekleyin.
                    </div>
                @else
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-3 py-2.5">Başlık</th>
                                    <th class="px-3 py-2.5">Slug / URL</th>
                                    <th class="px-3 py-2.5">Durum</th>
                                    <th class="px-3 py-2.5">Footer</th>
                                    <th class="px-3 py-2.5 text-right">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($pages as $p)
                                    <tr class="{{ $p->aktif ? '' : 'opacity-60' }}">
                                        <td class="px-3 py-2.5 font-semibold text-slate-900">{{ $p->baslik }}</td>
                                        <td class="px-3 py-2.5 font-mono text-[11px] text-slate-600">
                                            <a href="{{ $p->publicUrl() }}" target="_blank" class="text-brand-600 hover:underline">
                                                /sayfa/{{ $p->slug }}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2.5">
                                            @if($p->aktif)
                                                <span class="sa-badge !bg-emerald-50 !text-emerald-800">Yayında</span>
                                            @else
                                                <span class="sa-badge">Taslak</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5">
                                            {{ $p->footer_goster ? 'Evet' : '—' }}
                                        </td>
                                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                            <a href="{{ route('panel.site-ayarlari.sayfalar.duzenle', $p->id) }}"
                                               class="sa-btn sa-btn-ghost sa-btn-sm">Düzenle</a>
                                            <form method="POST" action="{{ route('panel.site-ayarlari.sayfalar.sil.post', $p->id) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Bu sayfa silinsin mi?');">
                                                @csrf
                                                <button type="submit" class="sa-btn sa-btn-ghost sa-btn-sm text-red-600">Sil</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="sa-hint mt-3">
                        Menüye eklemek: <a href="{{ route('panel.site-ayarlari.menu') }}" class="font-semibold text-brand-600 underline">Site Ayarları → Menü</a>
                        → satır ekle / sistem sayfası listesinden <strong>Sayfa: …</strong> seçin.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
