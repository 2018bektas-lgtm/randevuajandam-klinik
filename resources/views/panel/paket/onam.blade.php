@extends('panel.layouts.app')
@section('title', 'Onam Formları')
@section('content')
<div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white border rounded-xl p-5 space-y-3">
        <h2 class="font-bold text-sm uppercase">Yeni form</h2>
        <form method="POST" action="{{ route('panel.onam.store') }}" class="space-y-2">
            @csrf
            <input name="baslik" required class="w-full border rounded-lg p-2 text-sm" placeholder="Başlık">
            <textarea name="icerik" required rows="6" class="w-full border rounded-lg p-2 text-sm" placeholder="Metin"></textarea>
            <button class="px-4 py-2 bg-orange-600 text-white text-xs font-bold rounded-lg">Kaydet</button>
        </form>
    </div>
    <div class="bg-white border rounded-xl p-5">
        <h2 class="font-bold text-sm uppercase mb-3">Formlar</h2>
        @forelse($formlar as $f)
            @php $f = is_array($f) ? $f : (array) $f; @endphp
            <div class="border-b py-3 flex justify-between gap-2 text-sm">
                <div>
                    <div class="font-bold">{{ $f['baslik'] ?? '' }}</div>
                    <div class="text-xs text-slate-500 line-clamp-2">{{ strip_tags($f['icerik'] ?? '') }}</div>
                </div>
                <form method="POST" action="{{ route('panel.onam.destroy', $f['id']) }}" onsubmit="return confirm('Sil?')">@csrf @method('DELETE')
                    <button class="text-red-600 text-xs font-bold">Sil</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-slate-500">Henüz form yok.</p>
        @endforelse
    </div>
</div>
@endsection
