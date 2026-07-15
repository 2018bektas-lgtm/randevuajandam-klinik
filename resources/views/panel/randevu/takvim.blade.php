@extends('panel.layouts.app')
@section('baslik', 'Randevu Takvimi')
@section('sayfa_baslik', 'Haftalık Randevu Takvimi')

@section('icerik')
@if(!empty($error))
    <div class="mb-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-sm">{{ $error }}</div>
@endif

<style>
    .fc { font-family: Inter, system-ui, sans-serif; }
    .fc-toolbar-title { font-family: Outfit, Inter, sans-serif !important; font-weight: 700 !important; color: #111827 !important; font-size: 1.25rem !important; }
    .fc-button-primary {
        background-color: #fff !important; border-color: #E5E7EB !important; color: #4B5563 !important;
        border-radius: 12px !important; font-weight: 600 !important; font-family: Outfit, Inter, sans-serif !important;
        font-size: .75rem !important; padding: 8px 16px !important; text-transform: capitalize !important;
        box-shadow: 0 1px 2px rgba(0,0,0,.05) !important;
    }
    .fc-button-primary:hover { background: #F9FAFB !important; color: #111827 !important; }
    .fc-button-active, .fc-button-active:hover { background: #C96A2B !important; border-color: #C96A2B !important; color: #fff !important; }
    .fc-today-button { background: #1F2937 !important; border-color: #1F2937 !important; color: #fff !important; }
    .fc-theme-standard td, .fc-theme-standard th { border-color: #F3F4F6 !important; }
    .fc-col-header-cell { background: #FAFAFA !important; padding: 12px 0 !important; border-bottom: 2px solid #E5E7EB !important; }
    .fc-col-header-cell-cushion { font-family: Outfit, Inter, sans-serif !important; font-weight: 600 !important; color: #1F2937 !important; font-size: 13px !important; text-decoration: none !important; }
    .fc-non-business {
        background-color: #FAFAFA !important;
        background-image: repeating-linear-gradient(45deg, transparent, transparent 8px, rgba(243,244,246,.6) 8px, rgba(243,244,246,.6) 16px) !important;
        opacity: .95 !important; cursor: not-allowed !important;
    }
    .fc-day-today { background-color: rgba(201,106,43,.02) !important; }
    .fc-timegrid-slot { height: 52px !important; border-bottom: 1px solid #F9FAFB !important; cursor: pointer; }
    .fc-timegrid-slot-label-cushion { font-size: 11px !important; font-weight: 600 !important; color: #4B5563 !important; }
    .fc-event { border-radius: 10px !important; cursor: pointer; border: none !important; box-shadow: 0 2px 8px rgba(31,41,55,.04); }
    .fc-event:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(31,41,55,.08) !important; }
    .fc-highlight { background: rgba(201,106,43,.12) !important; }
    #toast { position: fixed; right: 1.25rem; bottom: 1.25rem; z-index: 80; max-width: 22rem; }
</style>

<div class="space-y-6">
    <div class="bg-white p-5 rounded-2xl border border-[#E5E7EB] shadow-sm flex flex-col xl:flex-row xl:items-center justify-between gap-4 text-xs text-[#4B5563]">
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
            <span class="font-bold text-[#111827] font-display">Takvim Rehberi:</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-[#C96A2B]/20 border-l-4 border-[#C96A2B]"></span> Beklemede</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-100 border-l-4 border-emerald-500"></span> Onaylı</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-blue-100 border-l-4 border-blue-500"></span> Tamamlandı</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-100 border-l-4 border-red-500"></span> İptal</span>
            <span class="text-slate-400">Boş mesai saatinde tıklayarak yeni randevu ekleyin.</span>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <label class="font-bold text-[#111827] font-display flex items-center gap-2">
                ⏱️ Periyot:
                <select id="calendarSlotDurationSelect" class="px-3 py-1.5 rounded-xl border border-[#E5E7EB] bg-white font-semibold text-[#4B5563] focus:border-[#C96A2B] outline-none cursor-pointer text-xs">
                    @foreach([15,20,30,45,60] as $p)
                        <option value="{{ $p }}" @selected(($periyot ?? 30) == $p)>{{ $p }} dk</option>
                    @endforeach
                </select>
            </label>
            <button type="button" onclick="hizliKapatModalAc()" class="px-4 py-2 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-xs font-bold font-display text-red-600 transition">
                ⚡ Hızlı Saat Kapat
            </button>
            <a href="{{ route('panel.hastalar') }}" class="px-4 py-2 rounded-xl border border-[#E5E7EB] hover:bg-[#FFF7ED] text-xs font-bold font-display text-[#4B5563] hover:text-[#C96A2B] transition">
                Hastalar
            </a>
            <a href="{{ route('panel.randevular.talepler') }}" class="px-4 py-2 rounded-xl border border-[#E5E7EB] hover:bg-[#FFF7ED] hover:border-[#E7B58A]/40 text-xs font-bold font-display text-[#4B5563] hover:text-[#C96A2B] transition">
                Liste / Talepler →
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-sm p-4 sm:p-6">
        <div id="calendar" class="min-h-[720px]"></div>
    </div>
</div>

{{-- Detail modal --}}
<div id="eventModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-2xl max-w-md w-full overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="font-display font-bold text-[#111827] text-sm">Randevu Detayı</h3>
            <button type="button" onclick="closeEventModal()" class="text-slate-400 text-xl">&times;</button>
        </div>
        <div class="px-6 py-5 space-y-3 text-sm">
            <div>
                <div class="text-[10px] font-bold uppercase text-slate-400">Hasta</div>
                <div id="evHasta" class="font-semibold text-ink font-display"></div>
                <div id="evIletisim" class="text-xs text-slate-500"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><div class="text-[10px] font-bold uppercase text-slate-400">Tarih / Saat</div><div id="evZaman" class="font-semibold"></div></div>
                <div><div class="text-[10px] font-bold uppercase text-slate-400">Durum</div><div id="evDurum" class="font-bold text-xs uppercase text-[#C96A2B]"></div></div>
            </div>
            <div>
                <label class="text-[10px] font-bold uppercase text-slate-400">Hizmet</label>
                <select id="evHizmetSelect" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    <option value="">Hizmet seçin</option>
                    @foreach(($hizmetler ?? []) as $h)
                        <option value="{{ $h->id }}">{{ $h->ad }}</option>
                    @endforeach
                </select>
                <div id="evHizmet" class="hidden"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <div class="text-[10px] font-bold uppercase text-slate-400">Görüşme</div>
                    <div id="evGorusmeTipi" class="text-xs font-bold text-slate-600">Yüz yüze</div>
                </div>
            </div>
            <div id="evOnlineJoin" class="hidden rounded-xl border border-sky-100 bg-sky-50 p-3 space-y-2">
                <p class="text-[11px] text-sky-900 font-semibold">📹 Online görüşme — platform üzerinden (Zoom yok)</p>
                <a id="evJoinLink" href="#" target="_blank" rel="noopener"
                   class="inline-flex px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold">Görüşmeye Katıl →</a>
            </div>
            <div><div class="text-[10px] font-bold uppercase text-slate-400">Hasta notu</div><div id="evNot" class="text-xs italic text-slate-600"></div></div>
            <div>
                <label class="text-[10px] font-bold uppercase text-slate-400">Hekim notu / açıklama</label>
                <textarea id="evHekimNotu" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-xs"></textarea>
            </div>
        </div>
        <div id="evActions" class="px-6 py-4 bg-slate-50 border-t flex flex-wrap gap-2 justify-end"></div>
    </div>
</div>

{{-- New appointment modal --}}
<div id="formModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl border border-[#E5E7EB] shadow-2xl max-w-lg w-full overflow-hidden max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b flex justify-between items-center sticky top-0 bg-white z-10">
            <h3 class="font-display font-bold text-[#111827] text-sm">Yeni Randevu Oluştur</h3>
            <button type="button" onclick="closeFormModal()" class="text-slate-400 text-xl">&times;</button>
        </div>
        <form id="appointmentForm" class="p-6 space-y-4 text-sm" onsubmit="submitAppointment(event)">
            @csrf
            <div class="bg-[#FFF7ED] border border-[#E7B58A]/30 text-[#C96A2B] p-4 rounded-2xl text-xs font-semibold flex justify-between">
                <span>📅 Seçilen tarih & saat</span>
                <span id="formSelectedDateTime" class="text-[#111827] font-bold">—</span>
            </div>
            <input type="hidden" name="tarih" id="formTarih">
            <input type="hidden" name="saat" id="formSaat">

            <div>
                <label class="text-[10px] font-bold uppercase text-slate-500">Hizmet *</label>
                <select name="hizmet_id" id="formHizmet" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                    <option value="">Hizmet seçin</option>
                    @foreach(($hizmetler ?? []) as $h)
                        <option value="{{ $h->id }}" data-sure="{{ $h->sure }}">{{ $h->ad }} ({{ $h->sure }} dk)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="text-[10px] font-bold uppercase text-slate-500">Danışan *</label>
                    <button type="button" onclick="toggleGuestMode()" id="guestToggleBtn" class="text-[11px] font-bold text-[#C96A2B]">Manuel / misafir girişi</button>
                </div>
                <div id="patientSearchBox">
                    <input type="hidden" name="danisan_id" id="formDanisanId">
                    <input type="text" id="formDanisanSearch" autocomplete="off" placeholder="Ad, telefon veya e-posta yazın (min 2 karakter)..."
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                    <div id="formDanisanResults" class="mt-1 hidden border border-slate-200 rounded-xl bg-white shadow-lg max-h-40 overflow-y-auto text-sm"></div>
                    <p id="formDanisanSelected" class="mt-1 text-xs text-emerald-700 hidden"></p>
                    <button type="button" onclick="openNewClient()" class="mt-2 text-[11px] font-bold text-[#C96A2B]">+ Yeni danışan kaydet</button>
                </div>
                <div id="guestFields" class="hidden grid grid-cols-2 gap-2">
                    <input name="ad" id="formAd" placeholder="Ad" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <input name="soyad" id="formSoyad" placeholder="Soyad" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <input name="telefon" id="formTelefon" placeholder="Telefon" class="col-span-2 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    <input name="e_posta" id="formEposta" type="email" placeholder="E-posta (opsiyonel)" class="col-span-2 rounded-xl border border-slate-200 px-3 py-2 text-sm">
                </div>
            </div>

            <div>
                <label class="text-[10px] font-bold uppercase text-slate-500">Görüşme tipi</label>
                <div class="mt-1 flex gap-2 text-xs">
                    <label class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 cursor-pointer">
                        <input type="radio" name="gorusme_tipi" value="yuz_yuze" checked> Yüz yüze
                    </label>
                    <label class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 cursor-pointer">
                        <input type="radio" name="gorusme_tipi" value="online"> Online (platform)
                    </label>
                </div>
                <p class="mt-1 text-[10px] text-slate-500">Online da aynı slotu kapatır. Pakette “online görüşme” yoksa kayıt reddedilir.</p>
            </div>

            <div>
                <label class="text-[10px] font-bold uppercase text-slate-500">Not</label>
                <textarea name="aciklama" id="formNot" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeFormModal()" class="px-4 py-2.5 rounded-xl border text-xs font-bold text-slate-500">İptal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#C96A2B] hover:bg-[#B55A20] text-white text-xs font-bold font-display">Randevuyu Kaydet</button>
            </div>
        </form>
    </div>
</div>

{{-- New patient modal --}}
<div id="clientModal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-slate-900/50">
    <div class="bg-white rounded-2xl border shadow-2xl max-w-sm w-full p-6 space-y-3">
        <h3 class="font-display font-bold text-sm">Yeni Danışan</h3>
        <input id="ncName" placeholder="Ad Soyad *" class="w-full rounded-xl border px-3 py-2 text-sm">
        <input id="ncEmail" type="email" placeholder="E-posta *" class="w-full rounded-xl border px-3 py-2 text-sm">
        <input id="ncTel" placeholder="Telefon *" class="w-full rounded-xl border px-3 py-2 text-sm">
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" onclick="closeNewClient()" class="px-3 py-2 text-xs font-bold border rounded-xl">İptal</button>
            <button type="button" onclick="submitNewClient()" class="px-4 py-2 text-xs font-bold bg-[#C96A2B] text-white rounded-xl">Kaydet</button>
        </div>
    </div>
</div>

{{-- Hızlı Saat Kapat Modal --}}
<div id="hizliKapatModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
    <div id="hizliKapatModalContainer" class="bg-white rounded-2xl border border-[#E5E7EB] shadow-2xl max-w-lg w-full overflow-hidden">
        <div class="p-6 border-b border-[#E5E7EB] flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wider text-[#1F2937] font-display flex items-center gap-2">
                <span>⚡</span> Hızlı Saat Dilimi Kapat / Aç
            </h3>
            <button type="button" onclick="hizliKapatModalKapat()" class="text-[#6B7280] hover:text-[#1F2937] text-xl leading-none">&times;</button>
        </div>
        <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Tarih Seçin</label>
                    <span id="secili_tarih_formatli" class="text-xs font-bold text-[#C96A2B] font-display">Bugün</span>
                </div>
                <div class="flex items-center gap-2">
                    <div id="tarih_seridi" class="flex-1 flex gap-2 overflow-x-auto pb-2"></div>
                    <div class="relative flex-shrink-0">
                        <input type="date" id="hizli_kapat_tarih" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}"
                               onchange="hizliKapatTarihDegisti(this.value)"
                               class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                        <button type="button" class="p-3 rounded-2xl border border-[#E5E7EB] bg-white hover:bg-slate-50 text-[#1F2937]">📅</button>
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                <label class="block text-[10px] font-bold text-[#6B7280] uppercase tracking-wider font-display">Kapatılacak / Açılacak Saat Dilimleri</label>
                <div id="hizli_kapat_slotlar_container" class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2">
                    <div class="col-span-full py-6 text-center text-[#6B7280] text-xs">Saat dilimleri yükleniyor...</div>
                </div>
            </div>
        </div>
        <div class="p-4 bg-slate-50 border-t border-[#E5E7EB] flex items-center justify-between gap-3">
            <span class="text-[10px] text-[#6B7280] leading-relaxed max-w-[70%]">* Aktif ettiğiniz dilimler randevuya kapatılır; pasif edince tekrar açılır.</span>
            <button type="button" onclick="hizliKapatModalKapat()" class="px-5 py-2.5 rounded-xl bg-[#1F2937] hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider font-display">Kapat</button>
        </div>
    </div>
</div>

<div id="toast" class="hidden"></div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/locales/tr.global.min.js"></script>
<script>
(function () {
    const routes = {
        events: @json(route('panel.randevular.events')),
        periyot: @json(route('panel.randevular.periyot')),
        store: @json(route('panel.randevular.store')),
        durum: @json(route('panel.randevular.durum', ['id' => '__ID__'])),
        destroy: @json(url('/yonetim/randevular')),
        reschedule: @json(url('/yonetim/randevular')),
        guncelle: @json(url('/yonetim/randevular')),
        hastaAra: @json(route('panel.randevular.hastalar-ara')),
        hastaEkle: @json(route('panel.randevular.hasta-ekle')),
        hizliSlots: @json(route('panel.randevu.hizli-kapat-slotlar')),
        hizliSave: @json(route('panel.randevu.hizli-kapat')),
    };
    let currentEventProps = null;
    const csrf = @json(csrf_token());
    const minTime = @json($minHour ?? '08:00:00');
    const maxTime = @json($maxHour ?? '20:00:00');
    let slotDuration = @json($slotDurationString ?? '00:30:00');
    const businessHours = @json($businessHours ?? []);
    let calendar, guestMode = false, searchTimer = null;

    function toast(msg, ok) {
        const el = document.getElementById('toast');
        el.className = 'fixed right-5 bottom-5 z-[80] px-4 py-3 rounded-xl text-sm font-semibold shadow-lg ' + (ok ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white');
        el.textContent = msg;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3200);
    }

    function periodToSlot(min) {
        min = parseInt(min, 10);
        if (min >= 60) return '01:00:00';
        return '00:' + String(min).padStart(2, '0') + ':00';
    }

    // —— Period change ——
    document.getElementById('calendarSlotDurationSelect')?.addEventListener('change', async function () {
        const minutes = parseInt(this.value, 10);
        const dur = periodToSlot(minutes);
        if (calendar) {
            calendar.setOption('slotDuration', dur);
            calendar.setOption('snapDuration', dur);
            calendar.setOption('slotLabelInterval', dur);
        }
        try {
            const res = await fetch(routes.periyot, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ periyot: minutes })
            });
            const j = await res.json();
            if (!res.ok) throw new Error(j.message || 'Periyot kaydedilemedi');
            toast(j.message || 'Periyot güncellendi', true);
            slotDuration = dur;
        } catch (e) {
            toast(e.message, false);
        }
    });

    // —— Event detail ——
    window.closeEventModal = function () {
        const m = document.getElementById('eventModal');
        m.classList.add('hidden'); m.classList.remove('flex');
    };

    function openEventModal(props) {
        currentEventProps = props;
        document.getElementById('evHasta').textContent = props.hasta_ad || '—';
        document.getElementById('evIletisim').textContent = [props.telefon, props.e_posta].filter(Boolean).join(' · ') || '—';
        document.getElementById('evZaman').textContent = (props.tarih || '') + ' ' + (props.saat || '');
        document.getElementById('evHizmet').textContent = props.hizmet_ad || '—';
        document.getElementById('evDurum').textContent = props.durum || '—';
        document.getElementById('evNot').textContent = props.not || 'Belirtilmedi';
        document.getElementById('evHekimNotu').value = props.hekim_notu || props.not || '';
        const gorusmeTipi = props.gorusme_tipi || 'yuz_yuze';
        const gorusmeEl = document.getElementById('evGorusmeTipi');
        if (gorusmeEl) {
            gorusmeEl.textContent = gorusmeTipi === 'online' ? '📹 Online (platform)' : 'Yüz yüze';
            gorusmeEl.className = gorusmeTipi === 'online'
                ? 'text-xs font-bold text-sky-700'
                : 'text-xs font-bold text-slate-600';
        }
        const joinBox = document.getElementById('evOnlineJoin');
        const joinLink = document.getElementById('evJoinLink');
        if (joinBox && joinLink) {
            if (gorusmeTipi === 'online' && props.platform_join_url && props.durum === 'onaylandi') {
                joinBox.classList.remove('hidden');
                joinLink.href = props.platform_join_url;
            } else {
                joinBox.classList.add('hidden');
            }
        }
        const sel = document.getElementById('evHizmetSelect');
        if (sel) {
            // Match by label if hizmet_id yok
            let matched = false;
            for (const opt of sel.options) {
                if (props.hizmet_id && String(opt.value) === String(props.hizmet_id)) {
                    sel.value = opt.value; matched = true; break;
                }
                if (!matched && props.hizmet_ad && opt.textContent.trim().startsWith(props.hizmet_ad)) {
                    sel.value = opt.value; matched = true;
                }
            }
            if (!matched) sel.value = '';
        }
        const actions = document.getElementById('evActions');
        actions.innerHTML = '';
        const id = props.randevu_id;
        const durum = props.durum;

        function btn(label, next, cls) {
            const b = document.createElement('button');
            b.type = 'button'; b.className = cls; b.textContent = label;
            b.onclick = () => updateDurum(id, next);
            actions.appendChild(b);
        }
        if (durum === 'beklemede') {
            btn('Onayla', 'onaylandi', 'px-3 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold');
            btn('İptal', 'iptal', 'px-3 py-2 rounded-xl border border-red-200 text-red-600 text-xs font-bold');
        } else if (durum === 'onaylandi') {
            btn('Tamamla', 'tamamlandi', 'px-3 py-2 rounded-xl bg-[#C96A2B] text-white text-xs font-bold');
            btn('İptal', 'iptal', 'px-3 py-2 rounded-xl border border-red-200 text-red-600 text-xs font-bold');
        }
        const saveBtn = document.createElement('button');
        saveBtn.type = 'button';
        saveBtn.className = 'px-3 py-2 rounded-xl border text-xs font-bold text-slate-600';
        saveBtn.textContent = 'Hizmet / Not Kaydet';
        saveBtn.onclick = () => updateRandevuDetay(id);
        actions.appendChild(saveBtn);

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'px-3 py-2 rounded-xl bg-red-50 text-red-600 text-xs font-bold border border-red-100';
        del.textContent = 'Sil';
        del.onclick = () => deleteRandevu(id);
        actions.appendChild(del);

        const m = document.getElementById('eventModal');
        m.classList.remove('hidden'); m.classList.add('flex');
    }

    async function updateRandevuDetay(id) {
        const hizmet_id = parseInt(document.getElementById('evHizmetSelect')?.value || '0', 10);
        if (!hizmet_id) { toast('Hizmet seçin', false); return; }
        try {
            const res = await fetch(routes.guncelle + '/' + id + '/guncelle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({
                    hizmet_id,
                    aciklama: document.getElementById('evHekimNotu').value
                })
            });
            const j = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(j.message || 'Güncellenemedi');
            // also save hekim note via durum endpoint
            await updateDurum(id, currentEventProps?.durum || 'beklemede');
            toast(j.message || 'Güncellendi', true);
            closeEventModal();
            calendar.refetchEvents();
        } catch (e) { toast(e.message, false); }
    }

    async function updateDurum(id, durum) {
        const body = new FormData();
        body.append('_token', csrf);
        body.append('_method', 'PUT');
        body.append('durum', durum);
        body.append('hekim_notu', document.getElementById('evHekimNotu').value);
        try {
            const res = await fetch(routes.durum.replace('__ID__', id), {
                method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body
            });
            const j = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(j.message || 'Güncellenemedi');
            toast(j.message || 'Güncellendi', true);
            closeEventModal();
            calendar.refetchEvents();
        } catch (e) { toast(e.message, false); }
    }

    async function deleteRandevu(id) {
        if (!confirm('Randevuyu silmek istediğinize emin misiniz?')) return;
        try {
            const res = await fetch(routes.destroy + '/' + id, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
            });
            const j = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(j.message || 'Silinemedi');
            toast(j.message || 'Silindi', true);
            closeEventModal();
            calendar.refetchEvents();
        } catch (e) { toast(e.message, false); }
    }

    // —— New appointment form ——
    window.closeFormModal = function () {
        document.getElementById('formModal').classList.add('hidden');
        document.getElementById('formModal').classList.remove('flex');
    };

    function openFormModal(dateStr, timeStr) {
        document.getElementById('formTarih').value = dateStr;
        document.getElementById('formSaat').value = timeStr;
        const parts = dateStr.split('-');
        document.getElementById('formSelectedDateTime').textContent = `${parts[2]}.${parts[1]}.${parts[0]} — ${timeStr}`;
        document.getElementById('formHizmet').value = '';
        document.getElementById('formDanisanId').value = '';
        document.getElementById('formDanisanSearch').value = '';
        document.getElementById('formDanisanSelected').classList.add('hidden');
        document.getElementById('formNot').value = '';
        const gorusmeYuz = document.querySelector('#appointmentForm input[name="gorusme_tipi"][value="yuz_yuze"]');
        if (gorusmeYuz) gorusmeYuz.checked = true;
        guestMode = false;
        document.getElementById('guestFields').classList.add('hidden');
        document.getElementById('patientSearchBox').classList.remove('hidden');
        document.getElementById('guestToggleBtn').textContent = 'Manuel / misafir girişi';
        document.getElementById('formModal').classList.remove('hidden');
        document.getElementById('formModal').classList.add('flex');
    }

    window.toggleGuestMode = function () {
        guestMode = !guestMode;
        document.getElementById('guestFields').classList.toggle('hidden', !guestMode);
        document.getElementById('patientSearchBox').classList.toggle('hidden', guestMode);
        document.getElementById('guestToggleBtn').textContent = guestMode ? 'Kayıtlı danışan seç' : 'Manuel / misafir girişi';
        if (guestMode) document.getElementById('formDanisanId').value = '';
    };

    // Patient search
    document.getElementById('formDanisanSearch')?.addEventListener('input', function () {
        const q = this.value.trim();
        clearTimeout(searchTimer);
        if (q.length < 2) {
            document.getElementById('formDanisanResults').classList.add('hidden');
            return;
        }
        searchTimer = setTimeout(async () => {
            try {
                const res = await fetch(routes.hastaAra + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
                const j = await res.json();
                const box = document.getElementById('formDanisanResults');
                const items = j.results || [];
                if (!items.length) {
                    box.innerHTML = '<div class="px-3 py-2 text-xs text-slate-400">Sonuç yok — yeni danışan ekleyin</div>';
                } else {
                    box.innerHTML = items.map(it =>
                        `<button type="button" class="w-full text-left px-3 py-2 hover:bg-[#FFF7ED] text-xs border-b border-slate-50" data-id="${it.id}" data-text="${it.text.replace(/"/g, '&quot;')}">${it.text}</button>`
                    ).join('');
                    box.querySelectorAll('button').forEach(b => b.onclick = () => {
                        document.getElementById('formDanisanId').value = b.dataset.id;
                        document.getElementById('formDanisanSelected').textContent = 'Seçili: ' + b.dataset.text;
                        document.getElementById('formDanisanSelected').classList.remove('hidden');
                        document.getElementById('formDanisanSearch').value = '';
                        box.classList.add('hidden');
                    });
                }
                box.classList.remove('hidden');
            } catch (e) { /* ignore */ }
        }, 250);
    });

    window.openNewClient = function () {
        document.getElementById('clientModal').classList.remove('hidden');
        document.getElementById('clientModal').classList.add('flex');
    };
    window.closeNewClient = function () {
        document.getElementById('clientModal').classList.add('hidden');
        document.getElementById('clientModal').classList.remove('flex');
    };
    window.submitNewClient = async function () {
        const name = document.getElementById('ncName').value.trim();
        const email = document.getElementById('ncEmail').value.trim();
        const telefon = document.getElementById('ncTel').value.trim();
        if (!name || !email || !telefon) { toast('Tüm alanlar zorunlu', false); return; }
        try {
            const res = await fetch(routes.hastaEkle, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ name, email, telefon })
            });
            const j = await res.json();
            if (!res.ok) throw new Error(j.message || 'Eklenemedi');
            document.getElementById('formDanisanId').value = j.danisan.id;
            document.getElementById('formDanisanSelected').textContent = 'Seçili: ' + j.danisan.name;
            document.getElementById('formDanisanSelected').classList.remove('hidden');
            toast(j.message || 'Danışan eklendi', true);
            closeNewClient();
        } catch (e) { toast(e.message, false); }
    };

    window.submitAppointment = async function (e) {
        e.preventDefault();
        const gorusmeRadio = document.querySelector('#appointmentForm input[name="gorusme_tipi"]:checked');
        const payload = {
            tarih: document.getElementById('formTarih').value,
            saat: document.getElementById('formSaat').value,
            hizmet_id: parseInt(document.getElementById('formHizmet').value, 10),
            aciklama: document.getElementById('formNot').value,
            gorusme_tipi: gorusmeRadio ? gorusmeRadio.value : 'yuz_yuze',
        };
        if (guestMode) {
            payload.ad = document.getElementById('formAd').value;
            payload.soyad = document.getElementById('formSoyad').value;
            payload.telefon = document.getElementById('formTelefon').value;
            payload.e_posta = document.getElementById('formEposta').value;
            if (!payload.ad || !payload.soyad || !payload.telefon) {
                toast('Misafir için ad, soyad, telefon zorunlu', false);
                return;
            }
        } else {
            payload.danisan_id = parseInt(document.getElementById('formDanisanId').value, 10) || null;
            if (!payload.danisan_id) {
                toast('Danışan seçin veya misafir girişi kullanın', false);
                return;
            }
        }
        if (!payload.hizmet_id) { toast('Hizmet seçin', false); return; }

        try {
            const res = await fetch(routes.store, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(payload)
            });
            const j = await res.json();
            if (!res.ok) throw new Error(j.message || 'Randevu oluşturulamadı');
            toast(j.message || 'Randevu oluşturuldu', true);
            closeFormModal();
            calendar.refetchEvents();
        } catch (err) { toast(err.message, false); }
    };

    // —— Calendar ——
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('calendar');
        if (!el || typeof FullCalendar === 'undefined') return;

        calendar = new FullCalendar.Calendar(el, {
            initialView: 'timeGridWeek',
            locale: 'tr',
            firstDay: 1,
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            buttonText: { today: 'Bugün', month: 'Aylık', week: 'Haftalık', day: 'Günlük', list: 'Ajanda' },
            businessHours: businessHours,
            selectConstraint: 'businessHours',
            slotDuration: slotDuration,
            snapDuration: slotDuration,
            slotLabelInterval: slotDuration,
            slotLabelFormat: { hour: '2-digit', minute: '2-digit', omitZeroMinute: false, meridiem: false },
            slotMinTime: minTime,
            slotMaxTime: maxTime,
            allDaySlot: false,
            selectable: true,
            selectMirror: true,
            editable: true,
            eventDurationEditable: false,
            eventStartEditable: true,
            nowIndicator: true,
            selectOverlap: function (ev) { return !ev || ev.display !== 'background'; },
            eventSources: [
                { url: routes.events },
                {
                    events: function (info, success) {
                        const now = new Date();
                        if (now > info.start) {
                            success([{
                                id: 'past',
                                start: info.start.toISOString(),
                                end: (now > info.end ? info.end : now).toISOString(),
                                display: 'background',
                                extendedProps: { type: 'gecmis' }
                            }]);
                        } else success([]);
                    }
                }
            ],
            selectAllow: function (info) {
                return info.start >= new Date();
            },
            select: function (info) {
                const d = info.start;
                const dateStr = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
                const timeStr = String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
                openFormModal(dateStr, timeStr);
                calendar.unselect();
            },
            eventDidMount: function (info) {
                if (info.event.display === 'background') {
                    info.event.setProp('editable', false);
                    const t = info.event.extendedProps.type;
                    if (t === 'ogle') {
                        info.el.style.backgroundColor = '#FFF7ED';
                        info.el.style.borderLeft = '4px solid #E7B58A';
                        info.el.innerHTML = '<div style="font-size:10px;font-weight:700;color:#C96A2B;text-align:center;padding-top:6px;">ÖĞLE ARASI</div>';
                    } else if (t === 'gecmis') {
                        info.el.style.backgroundColor = '#F5F5F4';
                        info.el.style.opacity = '0.85';
                        info.el.style.cursor = 'not-allowed';
                    } else if (t === 'izin') {
                        info.el.style.backgroundColor = '#F5F5F4';
                        info.el.style.borderLeft = '4px solid #9CA3AF';
                    }
                    return;
                }
                if (info.event.extendedProps.type === 'randevu') {
                    const durum = info.event.extendedProps.durum;
                    let border = '#C96A2B', bg = 'rgba(201,106,43,0.09)', color = '#92400E';
                    if (durum === 'onaylandi') { border = '#10B981'; bg = 'rgba(16,185,129,0.09)'; color = '#065F46'; }
                    if (durum === 'tamamlandi') { border = '#3B82F6'; bg = 'rgba(59,130,246,0.09)'; color = '#1E40AF'; }
                    if (durum === 'iptal') {
                        border = '#EF4444'; bg = 'rgba(239,68,68,0.09)'; color = '#991B1B';
                        info.event.setProp('editable', false);
                    }
                    info.el.style.backgroundColor = bg;
                    info.el.style.borderLeft = '4px solid ' + border;
                    info.el.style.color = color;
                    info.el.style.borderRadius = '10px';
                } else {
                    info.event.setProp('editable', false);
                }
            },
            eventClick: function (info) {
                if (info.event.display === 'background') return;
                if (info.event.extendedProps.type !== 'randevu') return;
                info.jsEvent.preventDefault();
                openEventModal(info.event.extendedProps);
            },
            eventDrop: function (info) {
                rescheduleAppointment(info);
            }
        });
        calendar.render();
    });

    async function rescheduleAppointment(info) {
        const event = info.event;
        if (event.display === 'background' || event.extendedProps.type !== 'randevu') {
            info.revert();
            return;
        }
        if (event.start < new Date()) {
            toast('Geçmiş bir saate taşınamaz', false);
            info.revert();
            return;
        }
        const id = event.extendedProps.randevu_id || String(event.id).replace('randevu_', '');
        const d = event.start;
        const tarih = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        const saat = String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
        try {
            const res = await fetch(routes.reschedule + '/' + id + '/reschedule', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ tarih, saat })
            });
            const j = await res.json().catch(() => ({}));
            if (!res.ok || j.success === false) {
                throw new Error(j.message || 'Randevu taşınamadı');
            }
            toast(j.message || 'Randevu taşındı', true);
            calendar.refetchEvents();
        } catch (e) {
            toast(e.message, false);
            info.revert();
        }
    }

    // —— Hızlı kapat ——
    let hizliKapatDegisiklikYapildi = false;

    window.tarihSeridiniOlustur = function (seciliTarihStr) {
        const serit = document.getElementById('tarih_seridi');
        if (!serit) return;
        const bugun = new Date();
        let html = '';
        for (let i = 0; i < 14; i++) {
            const tarih = new Date();
            tarih.setDate(bugun.getDate() + i);
            const yyyy = tarih.getFullYear();
            const mm = String(tarih.getMonth() + 1).padStart(2, '0');
            const dd = String(tarih.getDate()).padStart(2, '0');
            const tarihStr = `${yyyy}-${mm}-${dd}`;
            const gunAdi = tarih.toLocaleDateString('tr-TR', { weekday: 'short' });
            const gunNo = tarih.getDate();
            const ayAdi = tarih.toLocaleDateString('tr-TR', { month: 'short' });
            const isSelected = (tarihStr === seciliTarihStr);
            const activeClasses = isSelected
                ? 'bg-[#C96A2B] text-white border-[#C96A2B] shadow-md'
                : 'bg-[#FAFAFA] text-[#4B5563] border-[#E5E7EB] hover:border-[#C96A2B]';
            html += `<button type="button" onclick="hizliKapatSerittenTarihSec('${tarihStr}')"
                class="flex-shrink-0 flex flex-col items-center justify-center w-14 py-2.5 rounded-2xl border text-center transition-all ${activeClasses}">
                <span class="text-[9px] font-bold uppercase">${gunAdi}</span>
                <span class="text-base font-extrabold font-display">${gunNo}</span>
                <span class="text-[8px] font-semibold uppercase">${ayAdi}</span>
            </button>`;
        }
        serit.innerHTML = html;
        const formatliBaslik = document.getElementById('secili_tarih_formatli');
        if (formatliBaslik) {
            const bugunStr = bugun.toISOString().slice(0, 10);
            formatliBaslik.innerText = seciliTarihStr === bugunStr ? 'Bugün' : seciliTarihStr.split('-').reverse().join('.');
        }
    };

    window.hizliKapatSerittenTarihSec = function (tarihStr) {
        const input = document.getElementById('hizli_kapat_tarih');
        if (input) input.value = tarihStr;
        tarihSeridiniOlustur(tarihStr);
        hizliKapatSlotlariYukle(tarihStr);
    };
    window.hizliKapatTarihDegisti = function (tarihStr) {
        tarihSeridiniOlustur(tarihStr);
        hizliKapatSlotlariYukle(tarihStr);
    };
    window.hizliKapatModalAc = function () {
        hizliKapatDegisiklikYapildi = false;
        const modal = document.getElementById('hizliKapatModal');
        if (!modal) return;
        modal.classList.remove('hidden');
        const bugun = document.getElementById('hizli_kapat_tarih').value;
        tarihSeridiniOlustur(bugun);
        hizliKapatSlotlariYukle(bugun);
    };
    window.hizliKapatModalKapat = function () {
        const modal = document.getElementById('hizliKapatModal');
        if (modal) modal.classList.add('hidden');
        if (hizliKapatDegisiklikYapildi && calendar) calendar.refetchEvents();
    };
    window.hizliKapatSlotlariYukle = async function (tarih) {
        const container = document.getElementById('hizli_kapat_slotlar_container');
        container.innerHTML = '<div class="col-span-full py-6 text-center text-[#6B7280] text-xs animate-pulse">Saat dilimleri yükleniyor...</div>';
        try {
            const res = await fetch(routes.hizliSlots + '?tarih=' + encodeURIComponent(tarih), { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!data.aktif_mi) {
                container.innerHTML = `<div class="col-span-full py-6 text-center text-red-500 text-xs font-semibold bg-red-50 rounded-xl border border-red-100">${data.mesaj || 'Çalışma saati yok'}</div>`;
                return;
            }
            if (!data.slots || !data.slots.length) {
                container.innerHTML = '<div class="col-span-full py-6 text-center text-[#6B7280] text-xs">Bu tarihe ait slot bulunamadı.</div>';
                return;
            }
            let html = '';
            data.slots.forEach((slot, index) => {
                let statusColor = 'bg-[#FAFAFA] border-[#E5E7EB]';
                let badgeHtml = '';
                let disabled = '';
                if (slot.dolu_mu) {
                    statusColor = 'bg-emerald-50 border-emerald-100 opacity-60 pointer-events-none';
                    badgeHtml = '<span class="absolute right-2 top-2 text-[8px] bg-emerald-500 text-white font-bold px-1.5 py-0.5 rounded">DOLU</span>';
                    disabled = 'disabled';
                } else if (slot.ogle_mi) {
                    statusColor = 'bg-amber-50 border-amber-100 opacity-60 pointer-events-none';
                    badgeHtml = '<span class="absolute right-2 top-2 text-[8px] bg-amber-500 text-white font-bold px-1.5 py-0.5 rounded">ÖĞLE</span>';
                    disabled = 'disabled';
                } else if (slot.kapali_mi) {
                    statusColor = 'bg-red-50/50 border-red-200';
                    badgeHtml = '<span class="absolute right-2 top-2 text-[8px] bg-red-500 text-white font-bold px-1.5 py-0.5 rounded">KAPALI</span>';
                }
                html += `<label id="slot-card-${index}" class="relative p-4 rounded-2xl border ${statusColor} flex flex-col justify-between gap-3 select-none cursor-pointer hover:border-[#C96A2B] transition-all shadow-sm">
                    ${badgeHtml}
                    <span class="text-xs font-bold text-[#111827] font-display pt-1">${slot.saat_baslangic} - ${slot.saat_bitis}</span>
                    <div class="flex justify-between items-center mt-1">
                        <span class="text-[10px] text-[#6B7280] font-semibold">Kapalı</span>
                        <div class="relative inline-flex items-center">
                            <input type="checkbox" name="hizli_kapat_saatler[]" value="${slot.saat_baslangic}"
                                   ${slot.kapali_mi ? 'checked' : ''} ${disabled}
                                   onchange="hizliKapatToggle(this, 'slot-card-${index}')"
                                   class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#C96A2B]"></div>
                        </div>
                    </div>
                </label>`;
            });
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = '<div class="col-span-full py-6 text-center text-red-500 text-xs">Slotlar yüklenemedi.</div>';
        }
    };
    window.hizliKapatToggle = async function (checkbox, cardId) {
        const card = document.getElementById(cardId);
        if (!card) return;
        card.classList.add('opacity-50', 'pointer-events-none');
        const tarih = document.getElementById('hizli_kapat_tarih').value;
        const checkboxes = document.querySelectorAll('input[name="hizli_kapat_saatler[]"]:checked');
        const saatler = Array.from(checkboxes).map(cb => cb.value);
        try {
            const res = await fetch(routes.hizliSave, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ tarih, saatler })
            });
            const data = await res.json();
            card.classList.remove('opacity-50', 'pointer-events-none');
            if (data.basarili || data.success) {
                hizliKapatDegisiklikYapildi = true;
                card.classList.remove('bg-red-50/50', 'border-red-200', 'bg-[#FAFAFA]', 'border-[#E5E7EB]');
                const oldBadge = card.querySelector('span.absolute');
                if (oldBadge) oldBadge.remove();
                if (checkbox.checked) {
                    card.classList.add('bg-red-50/50', 'border-red-200');
                    card.insertAdjacentHTML('afterbegin', '<span class="absolute right-2 top-2 text-[8px] bg-red-500 text-white font-bold px-1.5 py-0.5 rounded">KAPALI</span>');
                } else {
                    card.classList.add('bg-[#FAFAFA]', 'border-[#E5E7EB]');
                }
            } else {
                checkbox.checked = !checkbox.checked;
                toast(data.mesaj || data.message || 'Kayıt hatası', false);
            }
        } catch (e) {
            card.classList.remove('opacity-50', 'pointer-events-none');
            checkbox.checked = !checkbox.checked;
            toast('Bağlantı hatası', false);
        }
    };
})();
</script>
@endsection
