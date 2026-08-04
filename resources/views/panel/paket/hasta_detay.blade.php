@extends('panel.layouts.app')
@section('title', 'Hasta Detay')
@section('content')
@php
    use App\Support\PaketOzellik;
    $h = is_array($hasta) ? $hasta : (array) $hasta;
@endphp
<div class="p-6 space-y-6">
    <a href="{{ route('panel.hastalar') }}" class="text-xs font-bold text-slate-500">← Hastalar</a>
    <div class="bg-white border rounded-xl p-5">
        <h1 class="font-bold text-lg">{{ ($h['ad'] ?? '') }} {{ ($h['soyad'] ?? '') }}</h1>
        <p class="text-sm text-slate-600">{{ $h['telefon'] ?? '' }} · {{ $h['e_posta'] ?? '' }}</p>
    </div>

    @if(!empty($h['tedavi_gecmisi_acik']))
    <div class="bg-white border rounded-xl p-5">
        <h2 class="font-bold text-sm uppercase mb-3">Tedavi / seans geçmişi</h2>
        @php $randevular = $h['randevular'] ?? []; @endphp
        @forelse($randevular as $r)
            @php $r = is_array($r) ? $r : (array) $r; @endphp
            <div class="border-b py-2 text-sm flex justify-between">
                <span>{{ $r['tarih'] ?? '' }} {{ $r['saat'] ?? '' }} — {{ $r['hizmet']['ad'] ?? ($r['hizmet_ad'] ?? '—') }}</span>
                <span class="text-xs uppercase">{{ $r['durum'] ?? '' }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-500">Kayıt yok.</p>
        @endforelse
    </div>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-900">Tedavi geçmişi paketinizde yok.</div>
    @endif

    @if(PaketOzellik::has('hasta_not_dosya'))
    <div class="bg-white border rounded-xl p-5 space-y-3">
        <h2 class="font-bold text-sm uppercase">Dosyalar</h2>
        <form method="POST" action="{{ route('panel.hastalar.dosya', $id) }}" enctype="multipart/form-data" class="space-y-2">
            @csrf
            <input type="file" name="dosya" required class="text-sm">
            <input name="baslik" class="w-full border rounded p-2 text-sm" placeholder="Başlık">
            <button class="px-3 py-1.5 bg-slate-900 text-white text-xs font-bold rounded">Yükle</button>
        </form>
        @foreach($dosyalar as $d)
            @php $d = is_array($d) ? $d : (array) $d; @endphp
            <div class="flex justify-between text-sm border-t py-2">
                <a href="{{ $d['url'] ?? '#' }}" target="_blank" class="text-orange-700 font-bold">{{ $d['baslik'] ?? $d['orijinal_ad'] ?? 'Dosya' }}</a>
                <form method="POST" action="{{ route('panel.hastalar.dosya.sil', $d['id']) }}">@csrf @method('DELETE')
                    <button class="text-red-600 text-xs">Sil</button>
                </form>
            </div>
        @endforeach
    </div>
    @endif

    @if(PaketOzellik::has('onam_formu') && !empty($onamFormlar))
    <div class="bg-white border rounded-xl p-5">
        <h2 class="font-bold text-sm uppercase mb-2">Onam kaydı</h2>
        <form method="POST" action="{{ route('panel.onam.imza') }}" class="space-y-2">
            @csrf
            <input type="hidden" name="hasta_id" value="{{ $id }}">
            <select name="onam_form_id" required class="w-full border rounded p-2 text-sm">
                <option value="">Form seçin</option>
                @foreach($onamFormlar as $f)
                    @php $f = is_array($f) ? $f : (array) $f; @endphp
                    <option value="{{ $f['id'] }}">{{ $f['baslik'] ?? '' }}</option>
                @endforeach
            </select>
            <button class="px-3 py-1.5 bg-orange-600 text-white text-xs font-bold rounded">Kaydet</button>
        </form>
    </div>
    @endif
</div>
@endsection
