@extends('panel.layouts.app')
@section('baslik', 'Yorumlar')
@section('sayfa_baslik', 'Hasta Yorumları')

@section('icerik')
@php $stats = $stats ?? []; @endphp
<div class="grid sm:grid-cols-4 gap-3 mb-6">
    <div class="bg-white rounded-2xl border p-4"><div class="text-[10px] font-bold uppercase text-slate-400">Toplam</div><div class="font-display text-2xl font-bold text-ink">{{ $stats['toplam'] ?? 0 }}</div></div>
    <div class="bg-white rounded-2xl border p-4"><div class="text-[10px] font-bold uppercase text-slate-400">Bekleyen</div><div class="font-display text-2xl font-bold text-amber-600">{{ $stats['beklemede'] ?? 0 }}</div></div>
    <div class="bg-white rounded-2xl border p-4"><div class="text-[10px] font-bold uppercase text-slate-400">Onaylı</div><div class="font-display text-2xl font-bold text-emerald-600">{{ $stats['onaylandi'] ?? 0 }}</div></div>
    <div class="bg-white rounded-2xl border p-4"><div class="text-[10px] font-bold uppercase text-slate-400">Ort. puan</div><div class="font-display text-2xl font-bold text-brand-600">{{ number_format((float)($stats['ortalama_puan'] ?? 0), 1) }}</div></div>
</div>

<div class="mb-4 flex flex-wrap gap-2">
    @foreach(['' => 'Tümü', 'beklemede' => 'Bekleyen', 'onaylandi' => 'Onaylı', 'reddedildi' => 'Red'] as $k => $v)
        <a href="{{ route('panel.yorumlar', $k ? ['durum' => $k] : []) }}"
           class="px-3 py-1.5 rounded-full text-xs font-bold {{ request('durum', '') === $k ? 'bg-brand-500 text-white' : 'bg-white border border-slate-200' }}">{{ $v }}</a>
    @endforeach
</div>

<div class="space-y-3">
    @forelse($items as $y)
        @php $y = is_array($y) ? $y : (array) $y; @endphp
        <div class="bg-white rounded-2xl border border-[#E5E7EB] p-5 shadow-sm">
            <div class="flex flex-wrap justify-between gap-2 mb-2">
                <div>
                    <div class="font-semibold text-ink">
                        {{ $y['ad'] ?? ($y['hasta']['ad'] ?? 'Hasta') }} {{ $y['soyad'] ?? ($y['hasta']['soyad'] ?? '') }}
                    </div>
                    <div class="text-xs text-amber-500">{{ str_repeat('★', (int)($y['puan'] ?? 5)) }}</div>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded-full bg-slate-50 border">{{ $y['onay_durumu'] ?? '—' }}</span>
            </div>
            <p class="text-sm text-slate-600">{{ $y['yorum'] ?? $y['icerik'] ?? '' }}</p>
            @if(!empty($y['doktor_yaniti']))
                <div class="mt-3 p-3 rounded-xl bg-brand-50 border border-brand-100 text-sm text-brand-700">
                    <span class="font-bold text-xs uppercase">Yanıtınız:</span> {{ $y['doktor_yaniti'] }}
                </div>
            @endif
            <div class="mt-4 flex flex-wrap gap-2 items-end">
                <form method="POST" action="{{ route('panel.yorumlar.yanit', $y['id']) }}" class="flex-1 min-w-[220px] flex gap-2">
                    @csrf
                    <input name="doktor_yaniti" placeholder="Yanıt yazın…" class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm" value="{{ $y['doktor_yaniti'] ?? '' }}">
                    <input type="hidden" name="onay_durumu" value="onaylandi">
                    <button class="px-3 py-2 rounded-xl bg-brand-500 text-white text-xs font-bold">Yanıtla</button>
                </form>
                <form method="POST" action="{{ route('panel.yorumlar.durum', $y['id']) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="onay_durumu" value="onaylandi">
                    <button class="px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold">Onayla</button>
                </form>
                <form method="POST" action="{{ route('panel.yorumlar.durum', $y['id']) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="onay_durumu" value="reddedildi">
                    <button class="px-3 py-2 rounded-xl border border-red-200 text-red-600 text-xs font-bold">Reddet</button>
                </form>
            </div>
        </div>
    @empty
        <div class="p-10 text-center text-slate-400 bg-white rounded-2xl border">Yorum yok.</div>
    @endforelse
</div>
@endsection
