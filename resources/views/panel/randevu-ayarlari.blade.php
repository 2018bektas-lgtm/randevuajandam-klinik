@extends('panel.layouts.app')
@section('baslik', 'Randevu Ayarları')
@section('sayfa_baslik', 'Randevu Ayarları')

@section('icerik')
@php
    $gunler = [1=>'Pazartesi',2=>'Salı',3=>'Çarşamba',4=>'Perşembe',5=>'Cuma',6=>'Cumartesi',7=>'Pazar'];
    $saatMap = collect($saatler)->keyBy('gun');
@endphp

<form method="POST" action="{{ route('panel.randevu-ayarlari.update') }}" class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 space-y-4 max-w-3xl">
    @csrf @method('PUT')
    <h3 class="font-display font-bold text-ink">Genel ayarlar</h3>
    <div class="grid sm:grid-cols-2 gap-4 text-sm">
        <div>
            <label class="text-xs font-bold uppercase text-slate-500">Periyot (dk)</label>
            <select name="randevu_periyodu" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                @foreach([15,20,30,45,60] as $p)
                    <option value="{{ $p }}" @selected(($ayar['randevu_periyodu'] ?? 30) == $p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-bold uppercase text-slate-500">Onay tipi</label>
            <select name="randevu_onay_tipi" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
                <option value="manuel" @selected(($ayar['randevu_onay_tipi'] ?? '') === 'manuel')>Manuel</option>
                <option value="otomatik" @selected(($ayar['randevu_onay_tipi'] ?? '') === 'otomatik')>Otomatik</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-bold uppercase text-slate-500">En erken (saat)</label>
            <input type="number" name="en_erken_randevu_saati" value="{{ $ayar['en_erken_randevu_saati'] ?? 2 }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
        </div>
        <div>
            <label class="text-xs font-bold uppercase text-slate-500">En geç (gün)</label>
            <input type="number" name="en_gec_randevu_gunu" value="{{ $ayar['en_gec_randevu_gunu'] ?? 30 }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
        </div>
        <div>
            <label class="text-xs font-bold uppercase text-slate-500">İptal limit (saat)</label>
            <input type="number" name="iptal_saat_limiti" value="{{ $ayar['iptal_saat_limiti'] ?? 24 }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
        </div>
        <div>
            <label class="text-xs font-bold uppercase text-slate-500">Günlük max (0=limitsiz)</label>
            <input type="number" name="gunluk_maksimum_randevu" value="{{ $ayar['gunluk_maksimum_randevu'] ?? 0 }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
        </div>
    </div>
    <div class="flex flex-wrap gap-4 text-sm">
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="aktif_mi" value="1" @checked($ayar['aktif_mi'] ?? true)> Randevu kabulü</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="online_randevu_aktif" value="1" @checked($ayar['online_randevu_aktif'] ?? true)> Online randevu</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="yuzyuze_randevu_aktif" value="1" @checked($ayar['yuzyuze_randevu_aktif'] ?? true)> Yüz yüze randevu</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="randevu_iptal_aktif_mi" value="1" @checked($ayar['randevu_iptal_aktif_mi'] ?? true)> Online iptal</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="email_bildirimleri" value="1" @checked($ayar['email_bildirimleri'] ?? true)> E-posta</label>
        <label class="inline-flex items-center gap-2"><input type="checkbox" name="sms_bildirimleri" value="1" @checked($ayar['sms_bildirimleri'] ?? true)> SMS</label>
    </div>
    <button class="px-5 py-2.5 rounded-xl bg-brand-500 text-white font-bold text-sm">Ayarları Kaydet</button>
</form>

<form method="POST" action="{{ route('panel.calisma-saatleri.update') }}" class="bg-white rounded-2xl border border-slate-200 p-6 max-w-3xl space-y-3">
    @csrf @method('PUT')
    <h3 class="font-display font-bold text-ink">Çalışma saatleri</h3>
    @foreach($gunler as $g => $ad)
        @php $s = $saatMap->get($g); @endphp
        <div class="grid grid-cols-2 sm:grid-cols-6 gap-2 items-center text-sm border-b border-slate-50 pb-2">
            <label class="font-medium text-ink col-span-2 sm:col-span-1">
                <input type="checkbox" name="aktif_{{ $g }}" value="1" @checked($s['aktif_mi'] ?? false)> {{ $ad }}
            </label>
            <input type="time" name="bas_{{ $g }}" value="{{ isset($s['mesai_baslangic']) ? substr($s['mesai_baslangic'],0,5) : '09:00' }}" class="rounded-lg border border-slate-200 px-2 py-1">
            <input type="time" name="bit_{{ $g }}" value="{{ isset($s['mesai_bitis']) ? substr($s['mesai_bitis'],0,5) : '17:00' }}" class="rounded-lg border border-slate-200 px-2 py-1">
            <label class="text-xs"><input type="checkbox" name="ogle_aktif_{{ $g }}" value="1" @checked($s['ogle_arasi_aktif_mi'] ?? false)> Öğle</label>
            <input type="time" name="ogle_bas_{{ $g }}" value="{{ isset($s['ogle_baslangic']) ? substr($s['ogle_baslangic'],0,5) : '12:00' }}" class="rounded-lg border border-slate-200 px-2 py-1">
            <input type="time" name="ogle_bit_{{ $g }}" value="{{ isset($s['ogle_bitis']) ? substr($s['ogle_bitis'],0,5) : '13:00' }}" class="rounded-lg border border-slate-200 px-2 py-1">
        </div>
    @endforeach
    <button class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-sm">Saatleri Kaydet</button>
</form>
@endsection
