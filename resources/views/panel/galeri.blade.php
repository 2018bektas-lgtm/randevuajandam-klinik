@extends('panel.layouts.app')
@section('baslik', 'Galeri')
@section('sayfa_baslik', 'Fotoğraf Galerisi')

@section('icerik')
<div class="bg-white rounded-2xl border border-[#E5E7EB] p-5 mb-6 shadow-sm max-w-xl">
    <h3 class="font-display font-bold text-ink mb-3">Fotoğraf yükle</h3>
    <form method="POST" action="{{ route('panel.galeri.store') }}" enctype="multipart/form-data" class="space-y-3 text-sm">
        @csrf
        <input type="file" name="resimler[]" accept="image/*" multiple required class="w-full text-xs">
        <p class="text-xs text-slate-400">Birden fazla seçebilirsiniz. Görseller ana platform deposuna kaydedilir.</p>
        <button class="px-5 py-2.5 rounded-xl bg-brand-500 text-white font-bold text-sm">Yükle</button>
    </form>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    @forelse($items as $g)
        @php $g = is_array($g) ? $g : (array) $g; @endphp
        <div class="bg-white rounded-2xl border border-[#E5E7EB] overflow-hidden shadow-sm">
            @if(!empty($g['url']))
                <img src="{{ $g['url'] }}" alt="" class="w-full h-40 object-cover bg-slate-100">
            @else
                <div class="w-full h-40 bg-slate-100 flex items-center justify-center text-slate-400 text-xs">Önizleme yok</div>
            @endif
            <div class="p-3 space-y-2">
                <form method="POST" action="{{ route('panel.galeri.update', $g['id']) }}" class="space-y-2 text-sm">
                    @csrf @method('PUT')
                    <input name="baslik" value="{{ $g['baslik'] ?? '' }}" placeholder="Başlık" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs">
                    <input type="number" name="sira" value="{{ $g['sira'] ?? 0 }}" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs" placeholder="Sıra">
                    <button class="w-full py-1.5 rounded-lg bg-ink text-white text-xs font-bold">Kaydet</button>
                </form>
                <form method="POST" action="{{ route('panel.galeri.destroy', $g['id']) }}" onsubmit="return confirm('Silinsin mi?')">
                    @csrf @method('DELETE')
                    <button class="w-full py-1.5 rounded-lg border border-red-200 text-red-600 text-xs font-bold">Sil</button>
                </form>
            </div>
        </div>
    @empty
        <div class="col-span-full p-10 text-center text-slate-400 bg-white rounded-2xl border">Galeri boş.</div>
    @endforelse
</div>
@endsection
