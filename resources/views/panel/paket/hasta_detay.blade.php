@extends('panel.layouts.app')
@section('title', 'Hasta Detay')
@section('content')
@php
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
        <h2 class="font-bold text-sm uppercase mb-3">Randevu geçmişi</h2>
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
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-900">Randevu geçmişi paketinizde yok.</div>
    @endif
</div>
@endsection
