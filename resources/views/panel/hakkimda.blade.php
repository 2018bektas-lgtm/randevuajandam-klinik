@extends('panel.layouts.app')

@section('baslik', 'Hakkımda Sayfam - Hekim Paneli')
@section('sayfa_baslik', 'Hakkımda Sayfam')

@section('icerik')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('panel.hakkimda.post') }}" method="POST" class="space-y-8">
        @csrf

        <div class="p-8 rounded-3xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden">
            <h3 class="text-lg font-bold font-display text-[#111827] mb-6 pb-3 border-b border-slate-100">Özgeçmiş &amp; Uzmanlık Bilgileri</h3>

            <div class="grid grid-cols-1 gap-5 mb-5">
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Branş / Uzmanlık Alanı (Birden Fazla Seçebilirsiniz)</label>
                    @php
                        $selectedBranslar = collect(old('branslar', $doktor->branslar->pluck('id')->toArray() ?? []))->map(fn ($x) => (int) $x)->all();
                    @endphp
                    <div class="border border-[#E5E7EB] rounded-xl p-3 max-h-56 overflow-y-auto bg-slate-50/40 space-y-1">
                        @forelse(($branslar ?? []) as $brans)
                            <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-white cursor-pointer text-xs">
                                <input type="checkbox" name="branslar[]" value="{{ $brans->id }}"
                                       @checked(in_array((int) $brans->id, $selectedBranslar, true))
                                       class="rounded border-slate-300 text-[#C96A2B] focus:ring-[#C96A2B]">
                                <span>{{ $brans->ad }}</span>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400">Branş listesi yüklenemedi. Uzmanlık alanını metin olarak girebilirsiniz.</p>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="uzmanlik_alani" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Uzmanlık Alanı (metin, branş seçilmezse)</label>
                    <input type="text" name="uzmanlik_alani" id="uzmanlik_alani" value="{{ old('uzmanlik_alani', $doktor->uzmanlik_alani) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] text-xs">
                </div>

                <div class="space-y-1.5">
                    <label for="klinik_adi" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Klinik / Muayenehane Adı</label>
                    <input type="text" name="klinik_adi" id="klinik_adi" value="{{ old('klinik_adi', $doktor->klinik_adi) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-[#111827] focus:outline-none focus:border-[#C96A2B] text-xs">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Mezuniyet / Eğitim Bilgisi</label>
                    <div class="flex gap-2">
                        <input type="text" id="mezuniyet_input" placeholder="Örn: Hacettepe Üniversitesi Tıp Fakültesi (2005)"
                               class="flex-grow px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-xs">
                        <button type="button" onclick="addMezuniyetTag()" class="px-4 py-2.5 bg-[#C96A2B] text-white font-bold text-xs uppercase rounded-xl">Ekle</button>
                    </div>
                    <div id="mezuniyet_tags_container" class="flex flex-wrap gap-2 p-3 border border-[#E5E7EB] rounded-xl bg-slate-50/50 min-h-[50px]"></div>
                    <div id="mezuniyet_hidden_fields"></div>
                </div>

                <div class="space-y-1.5">
                    <label for="biyografi" class="block text-[10px] font-bold text-[#1F2937] uppercase tracking-wider font-display">Özgeçmiş / Detaylı Bilgi</label>
                    <textarea name="biyografi" id="biyografi" rows="12"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-[#E5E7EB] text-xs">{{ old('biyografi', $doktor->biyografi) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3.5">
            <a href="{{ route('panel.dashboard') }}" class="px-6 py-3 rounded-xl border border-[#E5E7EB] bg-white text-[#6B7280] font-bold text-xs uppercase font-display">Geri Dön</a>
            <button type="submit" class="px-8 py-3 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white font-bold text-xs uppercase font-display">Bilgilerimi Kaydet</button>
        </div>
    </form>
</div>

<script>
(function () {
    let mezuniyetTags = @json(old('mezuniyet', $doktor->mezuniyet ?? []));
    if (!Array.isArray(mezuniyetTags)) mezuniyetTags = [];
    function renderMezuniyet() {
        const box = document.getElementById('mezuniyet_tags_container');
        const hidden = document.getElementById('mezuniyet_hidden_fields');
        box.innerHTML = mezuniyetTags.map((t, i) =>
            `<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#FFF7ED] border border-[#E7B58A]/40 text-xs font-semibold text-[#C96A2B]">
                ${String(t).replace(/</g,'&lt;')}
                <button type="button" onclick="removeMezuniyetTag(${i})" class="font-bold">&times;</button>
            </span>`
        ).join('') || '<span class="text-[11px] text-slate-400">Henüz eğitim bilgisi eklenmedi.</span>';
        hidden.innerHTML = mezuniyetTags.map(t =>
            `<input type="hidden" name="mezuniyet[]" value="${String(t).replace(/"/g,'&quot;')}">`
        ).join('');
    }
    window.addMezuniyetTag = function () {
        const input = document.getElementById('mezuniyet_input');
        const val = (input?.value || '').trim();
        if (!val) return;
        if (!mezuniyetTags.includes(val)) { mezuniyetTags.push(val); renderMezuniyet(); }
        input.value = '';
    };
    window.removeMezuniyetTag = function (i) { mezuniyetTags.splice(i, 1); renderMezuniyet(); };
    document.getElementById('mezuniyet_input')?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); addMezuniyetTag(); }
    });
    renderMezuniyet();
})();
</script>
@endsection
