@extends('panel.layouts.app')
@section('baslik', 'Finans')
@section('sayfa_baslik', 'Finans Yönetimi')

@section('icerik')
@php
    $ozet = $ozet ?? [];
    $gelir = $gelir ?? [];
    $gider = $gider ?? [];
    $kategoriler = $kategoriler ?? [];
    $katList = is_array($kategoriler) ? $kategoriler : [];
@endphp

<div class="grid sm:grid-cols-3 gap-4 mb-8">
    <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <div class="text-xs font-bold uppercase text-slate-400">Toplam gelir</div>
        <div class="mt-1 font-display text-2xl font-bold text-emerald-600">{{ number_format((float)($ozet['toplam_gelir'] ?? $ozet['gelir'] ?? 0), 2, ',', '.') }} ₺</div>
    </div>
    <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <div class="text-xs font-bold uppercase text-slate-400">Toplam gider</div>
        <div class="mt-1 font-display text-2xl font-bold text-red-500">{{ number_format((float)($ozet['toplam_gider'] ?? $ozet['gider'] ?? 0), 2, ',', '.') }} ₺</div>
    </div>
    <div class="p-5 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
        <div class="text-xs font-bold uppercase text-slate-400">Net</div>
        <div class="mt-1 font-display text-2xl font-bold text-ink">{{ number_format((float)($ozet['net'] ?? (($ozet['toplam_gelir'] ?? 0) - ($ozet['toplam_gider'] ?? 0))), 2, ',', '.') }} ₺</div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-2xl border p-5 shadow-sm">
        <h3 class="font-display font-bold text-ink mb-3">Gelir ekle</h3>
        <form method="POST" action="{{ route('panel.finans.gelir.store') }}" class="grid sm:grid-cols-2 gap-3 text-sm">
            @csrf
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Tutar</label>
                <input type="number" step="0.01" name="tutar" required class="mt-1 w-full rounded-xl border px-3 py-2">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Ödenen</label>
                <input type="number" step="0.01" name="odenen_tutar" class="mt-1 w-full rounded-xl border px-3 py-2">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Yöntem</label>
                <select name="odeme_yontemi" class="mt-1 w-full rounded-xl border px-3 py-2">
                    @foreach(['nakit','kredi_karti','havale','online'] as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Tarih</label>
                <input type="date" name="odeme_tarihi" value="{{ date('Y-m-d') }}" required class="mt-1 w-full rounded-xl border px-3 py-2">
            </div>
            <div class="sm:col-span-2">
                <label class="text-xs font-bold uppercase text-slate-500">Açıklama</label>
                <input name="aciklama" class="mt-1 w-full rounded-xl border px-3 py-2">
            </div>
            <div class="sm:col-span-2">
                <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold">Kaydet</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border p-5 shadow-sm">
        <h3 class="font-display font-bold text-ink mb-3">Gider ekle</h3>
        <form method="POST" action="{{ route('panel.finans.gider.store') }}" class="grid sm:grid-cols-2 gap-3 text-sm">
            @csrf
            <div class="sm:col-span-2">
                <label class="text-xs font-bold uppercase text-slate-500">Başlık</label>
                <input name="baslik" required class="mt-1 w-full rounded-xl border px-3 py-2">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Tutar</label>
                <input type="number" step="0.01" name="tutar" required class="mt-1 w-full rounded-xl border px-3 py-2">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Tarih</label>
                <input type="date" name="tarih" value="{{ date('Y-m-d') }}" required class="mt-1 w-full rounded-xl border px-3 py-2">
            </div>
            <div class="sm:col-span-2">
                <label class="text-xs font-bold uppercase text-slate-500">Açıklama</label>
                <input name="aciklama" class="mt-1 w-full rounded-xl border px-3 py-2">
            </div>
            <div class="sm:col-span-2">
                <button class="px-4 py-2 rounded-xl bg-red-500 text-white text-sm font-bold">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b font-display font-bold text-ink text-sm">Son gelirler</div>
        <div class="divide-y max-h-80 overflow-y-auto">
            @forelse($gelir as $g)
                @php $g = is_array($g) ? $g : (array) $g; @endphp
                <div class="px-4 py-3 flex justify-between gap-2 text-sm">
                    <div>
                        <div class="font-medium text-ink">{{ $g['aciklama'] ?? 'Gelir' }}</div>
                        <div class="text-xs text-slate-400">{{ $g['odeme_tarihi'] ?? $g['tarih'] ?? '' }} · {{ $g['odeme_yontemi'] ?? '' }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-emerald-600">{{ number_format((float)($g['tutar'] ?? 0), 2, ',', '.') }} ₺</span>
                        <form method="POST" action="{{ route('panel.finans.gelir.destroy', $g['id']) }}" onsubmit="return confirm('Sil?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 font-bold">Sil</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-slate-400 text-sm">Kayıt yok</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b font-display font-bold text-ink text-sm">Son giderler</div>
        <div class="divide-y max-h-80 overflow-y-auto">
            @forelse($gider as $g)
                @php $g = is_array($g) ? $g : (array) $g; @endphp
                <div class="px-4 py-3 flex justify-between gap-2 text-sm">
                    <div>
                        <div class="font-medium text-ink">{{ $g['baslik'] ?? 'Gider' }}</div>
                        <div class="text-xs text-slate-400">{{ $g['tarih'] ?? '' }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-red-500">{{ number_format((float)($g['tutar'] ?? 0), 2, ',', '.') }} ₺</span>
                        <form method="POST" action="{{ route('panel.finans.gider.destroy', $g['id']) }}" onsubmit="return confirm('Sil?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 font-bold">Sil</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-slate-400 text-sm">Kayıt yok</div>
            @endforelse
        </div>
    </div>
</div>

<div class="mt-8 bg-white rounded-2xl border p-5 shadow-sm max-w-lg">
    <h3 class="font-display font-bold text-ink mb-3">Kategori ekle</h3>
    <form method="POST" action="{{ route('panel.finans.kategori.store') }}" class="flex flex-wrap gap-2 text-sm">
        @csrf
        <input name="ad" required placeholder="Ad" class="rounded-xl border px-3 py-2 flex-1 min-w-[120px]">
        <select name="tur" class="rounded-xl border px-3 py-2">
            <option value="gelir">Gelir</option>
            <option value="gider">Gider</option>
        </select>
        <button class="px-4 py-2 rounded-xl bg-ink text-white font-bold text-xs">Ekle</button>
    </form>
    @if(count($katList))
        <ul class="mt-4 space-y-1 text-sm">
            @foreach($katList as $k)
                @php $k = is_array($k) ? $k : (array) $k; @endphp
                <li class="flex justify-between items-center py-1 border-b border-slate-50">
                    <span>{{ $k['ad'] ?? '' }} <span class="text-xs text-slate-400">({{ $k['tur'] ?? '' }})</span></span>
                    <form method="POST" action="{{ route('panel.finans.kategori.destroy', $k['id']) }}" onsubmit="return confirm('Sil?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-500 font-bold">Sil</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
