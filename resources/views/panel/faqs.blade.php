@extends('panel.layouts.app')
@section('baslik', 'SSS')
@section('sayfa_baslik', 'Sıkça Sorulan Sorular')

@section('icerik')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-2xl border border-[#E5E7EB] p-5 h-fit shadow-sm">
        <h3 class="font-display font-bold text-ink mb-3">Yeni soru</h3>
        <form method="POST" action="{{ route('panel.faqs.store') }}" class="space-y-3 text-sm">
            @csrf
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Soru</label>
                <input name="soru" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Cevap</label>
                <textarea name="cevap" rows="4" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2"></textarea>
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Sıra</label>
                <input type="number" name="sira" value="0" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
            </div>
            <button class="w-full py-2.5 rounded-xl bg-brand-500 text-white font-bold">Ekle</button>
        </form>
    </div>

    <div class="lg:col-span-2 space-y-3">
        @forelse($items as $f)
            @php $f = is_array($f) ? $f : (array) $f; @endphp
            <div class="bg-white rounded-2xl border border-[#E5E7EB] p-4 shadow-sm">
                <form method="POST" action="{{ route('panel.faqs.update', $f['id']) }}" class="space-y-2 text-sm">
                    @csrf @method('PUT')
                    <input name="soru" value="{{ $f['soru'] ?? '' }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 font-semibold">
                    <textarea name="cevap" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2">{{ $f['cevap'] ?? '' }}</textarea>
                    <div class="flex flex-wrap gap-3 items-center">
                        <input type="number" name="sira" value="{{ $f['sira'] ?? 0 }}" class="w-24 rounded-lg border border-slate-200 px-2 py-1.5 text-xs">
                        <label class="text-xs flex items-center gap-1">
                            <input type="checkbox" name="aktif" value="1" @checked($f['aktif'] ?? true)> Aktif
                        </label>
                        <button class="px-3 py-1.5 rounded-lg bg-ink text-white text-xs font-bold">Kaydet</button>
                    </div>
                </form>
                <div class="flex gap-2 mt-2">
                    <form method="POST" action="{{ route('panel.faqs.toggle', $f['id']) }}">
                        @csrf
                        <button class="px-3 py-1.5 rounded-lg border text-xs font-bold">Toggle</button>
                    </form>
                    <form method="POST" action="{{ route('panel.faqs.destroy', $f['id']) }}" onsubmit="return confirm('Silinsin mi?')">
                        @csrf @method('DELETE')
                        <button class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 text-xs font-bold">Sil</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-slate-400 bg-white rounded-2xl border">SSS kaydı yok.</div>
        @endforelse
    </div>
</div>
@endsection
