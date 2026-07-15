@extends('panel.layouts.app')
@section('baslik', 'Blog')
@section('sayfa_baslik', 'Blog Yazılarım')

@section('icerik')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white rounded-2xl border border-[#E5E7EB] p-5 h-fit shadow-sm">
        <h3 class="font-display font-bold text-ink mb-3">Yeni yazı</h3>
        <form method="POST" action="{{ route('panel.bloglar.store') }}" enctype="multipart/form-data" class="space-y-3 text-sm">
            @csrf
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Başlık</label>
                <input name="baslik" required value="{{ old('baslik') }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">İçerik</label>
                <textarea name="icerik" rows="6" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">{{ old('icerik') }}</textarea>
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Kapak görseli</label>
                <input type="file" name="resim" accept="image/*" class="mt-1 w-full text-xs">
            </div>
            <div>
                <label class="text-xs font-bold uppercase text-slate-500">Meta başlık</label>
                <input name="meta_baslik" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
            </div>
            <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="aktif_mi" value="1" checked> Yayında</label>
            <button class="w-full py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-bold transition">Ekle (API senkron)</button>
        </form>
    </div>

    <div class="lg:col-span-2 space-y-3">
        @forelse($items as $b)
            @php $b = is_array($b) ? $b : (array) $b; @endphp
            <div class="bg-white rounded-2xl border border-[#E5E7EB] p-4 shadow-sm">
                <form method="POST" action="{{ route('panel.bloglar.update', $b['id']) }}" enctype="multipart/form-data" class="space-y-2 text-sm">
                    @csrf @method('PUT')
                    <div class="flex flex-wrap gap-2 items-start justify-between">
                        <input name="baslik" value="{{ $b['baslik'] ?? '' }}" class="flex-1 min-w-[200px] rounded-xl border border-slate-200 px-3 py-2 font-semibold">
                        <label class="text-xs flex items-center gap-1 pt-2">
                            <input type="checkbox" name="aktif_mi" value="1" @checked($b['aktif_mi'] ?? false)> Aktif
                        </label>
                    </div>
                    <textarea name="icerik" rows="4" class="w-full rounded-xl border border-slate-200 px-3 py-2">{{ $b['icerik'] ?? '' }}</textarea>
                    <div class="flex flex-wrap gap-2 items-center">
                        <input type="file" name="resim" accept="image/*" class="text-xs">
                        <button class="px-4 py-2 rounded-xl bg-ink text-white text-xs font-bold">Kaydet</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('panel.bloglar.destroy', $b['id']) }}" class="mt-2" onsubmit="return confirm('Silinsin mi?')">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 text-xs font-bold">Sil</button>
                </form>
            </div>
        @empty
            <div class="p-10 text-center text-slate-400 bg-white rounded-2xl border">Henüz blog yazısı yok. Ana platform veya buradan ekleyin.</div>
        @endforelse
    </div>
</div>
@endsection
