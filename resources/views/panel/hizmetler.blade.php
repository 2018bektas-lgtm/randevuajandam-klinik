@extends('panel.layouts.app')
@section('baslik', 'Hizmetler')
@section('sayfa_baslik', 'Hizmetler')

@section('icerik')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-200 p-5 h-fit">
        <h3 class="font-display font-bold text-ink mb-3">Yeni hizmet</h3>
        <form method="POST" action="{{ route('panel.hizmetler.store') }}" class="space-y-3 text-sm">
            @csrf
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Ad</label>
                <input name="ad" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Süre (dk)</label>
                <input type="number" name="sure" value="30" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Fiyat</label>
                <input type="number" step="0.01" name="fiyat" value="0" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Açıklama</label>
                <textarea name="aciklama" rows="3" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2"></textarea>
            </div>
            <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="aktif_mi" value="1" checked> Aktif</label>
            <button class="w-full py-2.5 rounded-xl bg-brand-500 text-white font-bold">Ekle</button>
        </form>
    </div>

    <div class="lg:col-span-2 space-y-3">
        @forelse($items as $h)
            <div class="bg-white rounded-2xl border border-slate-200 p-4">
                <form method="POST" action="{{ route('panel.hizmetler.update', $h['id']) }}" class="grid sm:grid-cols-5 gap-2 items-end text-sm">
                    @csrf
                    @method('PUT')
                    <div class="sm:col-span-2">
                        <label class="text-[10px] font-bold uppercase text-slate-400">Ad</label>
                        <input name="ad" value="{{ $h['ad'] }}" class="w-full rounded-lg border border-slate-200 px-2 py-1.5">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-400">Süre</label>
                        <input type="number" name="sure" value="{{ $h['sure'] }}" class="w-full rounded-lg border border-slate-200 px-2 py-1.5">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase text-slate-400">Fiyat</label>
                        <input type="number" step="0.01" name="fiyat" value="{{ $h['fiyat'] }}" class="w-full rounded-lg border border-slate-200 px-2 py-1.5">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-bold uppercase text-slate-400 flex items-center gap-1">
                            <input type="checkbox" name="aktif_mi" value="1" @checked($h['aktif_mi'] ?? false)> Aktif
                        </label>
                        <button class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-bold">Kaydet</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('panel.hizmetler.destroy', $h['id']) }}" class="mt-2" onsubmit="return confirm('Silinsin mi?')">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 text-xs font-bold">Sil</button>
                </form>
            </div>
        @empty
            <div class="p-8 text-center text-slate-400 bg-white rounded-2xl border">Henüz hizmet yok.</div>
        @endforelse
    </div>
</div>
@endsection
