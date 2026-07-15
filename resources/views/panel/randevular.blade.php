@extends('panel.layouts.app')
@section('baslik', 'Randevular')
@section('sayfa_baslik', 'Randevular')

@section('icerik')
<div class="mb-4 flex flex-wrap gap-2">
    @foreach(['' => 'Tümü', 'beklemede' => 'Bekleyen', 'onaylandi' => 'Onaylı', 'tamamlandi' => 'Tamamlanan', 'iptal' => 'İptal'] as $k => $v)
        <a href="{{ route('panel.randevular', $k ? ['durum' => $k] : []) }}"
           class="px-3 py-1.5 rounded-full text-xs font-bold {{ ($durum ?? '') === $k || ($k === '' && empty($durum)) ? 'bg-brand-500 text-white' : 'bg-white border border-slate-200 text-slate-600' }}">
            {{ $v }}
        </a>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-4 py-3">Tarih</th>
                <th class="px-4 py-3">Hasta</th>
                <th class="px-4 py-3">Hizmet</th>
                <th class="px-4 py-3">Durum</th>
                <th class="px-4 py-3">İşlem</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse($items as $r)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="font-semibold text-ink">{{ $r['tarih'] }}</div>
                        <div class="text-xs text-slate-400">{{ $r['saat'] }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-ink">{{ $r['ad'] }} {{ $r['soyad'] }}</div>
                        <div class="text-xs text-slate-400">{{ $r['telefon'] }}</div>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $r['hizmet']['ad'] ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-bold uppercase">{{ $r['durum'] }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if(in_array($r['durum'], ['beklemede'], true))
                            <form method="POST" action="{{ route('panel.randevular.durum', $r['id']) }}" class="inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="durum" value="onaylandi">
                                <button class="text-xs font-bold text-emerald-600">Onayla</button>
                            </form>
                            <form method="POST" action="{{ route('panel.randevular.durum', $r['id']) }}" class="inline ml-2">
                                @csrf @method('PUT')
                                <input type="hidden" name="durum" value="iptal">
                                <button class="text-xs font-bold text-red-600">Reddet</button>
                            </form>
                        @elseif($r['durum'] === 'onaylandi')
                            <form method="POST" action="{{ route('panel.randevular.durum', $r['id']) }}" class="inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="durum" value="tamamlandi">
                                <button class="text-xs font-bold text-brand-600">Tamamla</button>
                            </form>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Kayıt yok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
