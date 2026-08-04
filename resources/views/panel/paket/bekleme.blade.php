@extends('panel.layouts.app')
@section('title', 'Bekleme Listesi')
@section('content')
<div class="p-6 space-y-4">
    <h1 class="text-lg font-bold">Bekleme Listesi</h1>
    @if(empty($items) || (is_countable($items) && count($items) === 0))
        <p class="text-sm text-slate-500">Kayıt yok.</p>
    @else
        <div class="bg-white border rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="p-3">Ad</th>
                        <th class="p-3">Telefon</th>
                        <th class="p-3">Durum</th>
                        <th class="p-3">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $b)
                        @php $row = is_array($b) ? $b : (array) $b; @endphp
                        <tr class="border-t">
                            <td class="p-3">{{ ($row['ad'] ?? '') }} {{ ($row['soyad'] ?? '') }}</td>
                            <td class="p-3">{{ $row['telefon'] ?? '' }}</td>
                            <td class="p-3">{{ $row['durum'] ?? '—' }}</td>
                            <td class="p-3 flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('panel.bekleme.bildir', $row['id']) }}">@csrf
                                    <button class="text-xs font-bold text-sky-700">Bildir</button>
                                </form>
                                <form method="POST" action="{{ route('panel.bekleme.sil', $row['id']) }}" onsubmit="return confirm('Silinsin mi?')">@csrf @method('DELETE')
                                    <button class="text-xs font-bold text-red-600">Sil</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(method_exists($items, 'links'))
            <div>{{ $items->links() }}</div>
        @endif
    @endif
</div>
@endsection
