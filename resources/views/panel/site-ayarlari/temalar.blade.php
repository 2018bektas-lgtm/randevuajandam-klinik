@extends('panel.layouts.app')
@section('baslik', 'Site Ayarları · Temalar')
@section('sayfa_baslik', 'Site Ayarları · Temalar')

@section('icerik')
@include('panel.site-ayarlari._shell')

@if(session('basari'))
    <div class="sa-wrap mb-4">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('basari') }}
        </div>
    </div>
@endif
@if(session('hata'))
    <div class="sa-wrap mb-4">
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
            {{ session('hata') }}
        </div>
    </div>
@endif

<div class="sa-wrap">
    <div class="sa-card mb-5">
        <div class="sa-card-head">
            <div>
                <h3>Hazır site temaları</h3>
                <p class="sa-hint">Tema değişince yalnızca renk değil; header, anasayfa düzeni, kartlar ve footer da değişir (tam layout paketi). Premium temalar klinik web sitesi paketinde yer alır.</p>
            </div>
            <span class="sa-badge">{{ count($temalar) }} tema</span>
        </div>
    </div>

    <form method="POST" action="{{ route('panel.site-ayarlari.temalar.kaydet') }}" id="temaForm">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
            @foreach($temalar as $id => $t)
                @php
                    $selected = $aktif_tema === $id;
                    $preview = $t['preview'] ?? [$t['renk'] ?? '#0d9488', '#f8fafc', '#0f172a'];
                    $isPremium = (bool) ($t['premium'] ?? false);
                    $available = theme_is_available($id);
                @endphp
                <label class="relative block {{ $available ? 'cursor-pointer' : 'cursor-not-allowed opacity-75' }} group">
                    <input type="radio" name="tema_id" value="{{ $id }}" class="peer sr-only" @checked($selected)
                           @disabled(! $available)
                           @if($available) onchange="onTemaPick('{{ $id }}', '{{ $t['renk'] ?? '#0d9488' }}')" @endif>
                    <div class="h-full rounded-2xl border-2 bg-white p-4 transition-all
                                peer-checked:border-brand-500 peer-checked:shadow-lg peer-checked:shadow-brand-500/15
                                border-slate-200 {{ $available ? 'group-hover:border-brand-500/40' : 'border-dashed' }}">
                        <div class="flex h-24 rounded-xl overflow-hidden mb-3 border border-black/5">
                            <div class="w-2/5" style="background:{{ $preview[0] ?? '#0d9488' }}"></div>
                            <div class="w-2/5" style="background:{{ $preview[1] ?? '#f8fafc' }}"></div>
                            <div class="w-1/5" style="background:{{ $preview[2] ?? '#0f172a' }}"></div>
                        </div>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-sm font-bold font-display text-ink m-0">{{ $t['ad'] ?? $id }}</h4>
                                    @if($isPremium)
                                        <span class="inline-flex px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-800 text-[9px] font-extrabold uppercase tracking-wide border border-amber-200">Premium</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed m-0">{{ $t['aciklama'] ?? '' }}</p>
                            </div>
                            @if($available)
                                <span class="shrink-0 w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-brand-500 peer-checked:bg-brand-500 flex items-center justify-center
                                             group-has-[:checked]:border-brand-500 group-has-[:checked]:bg-brand-500">
                                    <svg class="w-3 h-3 text-white opacity-0 group-has-[:checked]:opacity-100" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            @else
                                <span class="shrink-0 text-slate-400" title="Paket gerekli">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                </span>
                            @endif
                        </div>
                        @if($selected)
                            <span class="inline-flex mt-3 px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 text-[10px] font-extrabold uppercase tracking-wide border border-brand-100">Aktif</span>
                        @elseif(! $available)
                            <span class="inline-flex mt-3 px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-extrabold uppercase tracking-wide border border-slate-200">Web paketi gerekir</span>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>

        <div class="sa-card mb-5">
            <div class="sa-card-head">
                <div>
                    <h3>Vurgu rengi</h3>
                    <p class="sa-hint">Buton ve link rengi. “Tema rengini kullan” ile temanın önerdiği renge döner.</p>
                </div>
            </div>
            <div class="sa-card-body">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="sa-color-wrap">
                        <input type="color" id="tema_renk_picker" value="{{ $tema_renk }}"
                               oninput="document.getElementById('tema_renk_text').value=this.value; document.getElementById('tema_renk_hidden').value=this.value; document.getElementById('renk_temadan').checked=false">
                        <input type="text" id="tema_renk_text" value="{{ $tema_renk }}" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$"
                               oninput="if(/^#[0-9A-Fa-f]{6}$/.test(this.value)){document.getElementById('tema_renk_picker').value=this.value;document.getElementById('tema_renk_hidden').value=this.value;document.getElementById('renk_temadan').checked=false}">
                        <input type="hidden" name="tema_renk" id="tema_renk_hidden" value="{{ $tema_renk }}">
                    </div>
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                        <input type="checkbox" name="renk_temadan" id="renk_temadan" value="1" class="rounded border-slate-300 text-brand-600">
                        Tema varsayılan rengini kullan
                    </label>
                </div>
            </div>
        </div>

        <div class="sa-actions">
            <p class="sa-hint m-0">Kaydettikten sonra public siteyi yenileyin (cache ~1 dk).</p>
            <div class="flex gap-2">
                <a href="{{ route('frontend.anasayfa') }}" target="_blank" class="sa-btn sa-btn-ghost">Önizle</a>
                <button type="submit" class="sa-btn sa-btn-primary">Temayı uygula</button>
            </div>
        </div>
    </form>
</div>

<script>
const temaRenkleri = @json(collect($temalar)->mapWithKeys(fn ($t, $id) => [$id => $t['renk'] ?? '#0d9488']));
function onTemaPick(id, renk) {
    if (document.getElementById('renk_temadan').checked || true) {
        // Yeni tema seçilince varsayılan rengi öner
        document.getElementById('tema_renk_picker').value = renk;
        document.getElementById('tema_renk_text').value = renk;
        document.getElementById('tema_renk_hidden').value = renk;
        document.getElementById('renk_temadan').checked = true;
    }
}
</script>
@endsection
