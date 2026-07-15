@include('panel.site-ayarlari._nav')

@push('styles')
<style>
    /* ——— Site Ayarları design system ——— */
    .sa-wrap { width: 100%; max-width: none; }

    .sa-card {
        background: #fff;
        border: 1px solid #E8EAED;
        border-radius: 1.15rem;
        box-shadow: 0 1px 2px rgba(16,24,40,.04), 0 8px 28px rgba(16,24,40,.04);
        overflow: hidden;
    }
    .sa-card-head {
        display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between;
        gap: .85rem; padding: 1.15rem 1.35rem;
        background: linear-gradient(180deg, #FCFCFD 0%, #fff 100%);
        border-bottom: 1px solid #F0F1F3;
    }
    .sa-card-head h3 {
        margin: 0; font-size: .95rem; font-weight: 700; color: #111827;
        font-family: Outfit, Inter, sans-serif; letter-spacing: -.01em;
    }
    .sa-card-body { padding: 1.35rem 1.35rem 1.5rem; }
    .sa-card-foot {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: .75rem; padding: 1rem 1.35rem;
        background: #FAFBFC; border-top: 1px solid #F0F1F3;
    }

    .sa-hint { font-size: .72rem; color: #6B7280; line-height: 1.5; margin: .3rem 0 0; }
    .sa-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
        padding: .28rem .6rem; border-radius: 999px;
        background: #FFF7ED; color: #C96A2B; border: 1px solid rgba(231,181,138,.55);
        white-space: nowrap;
    }
    .sa-badge-soft {
        background: #F3F4F6; color: #6B7280; border-color: #E5E7EB;
    }

    .sa-label {
        display: block; margin-bottom: .4rem;
        font-size: .68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: #6B7280; font-family: Outfit, Inter, sans-serif;
    }
    .sa-field { margin-bottom: 1.05rem; }
    .sa-field:last-child { margin-bottom: 0; }
    .sa-input, .sa-textarea, .sa-select {
        width: 100%;
        padding: .7rem .95rem;
        border: 1px solid #E5E7EB;
        border-radius: .85rem;
        background: #fff;
        font-size: .8125rem;
        color: #111827;
        transition: border-color .15s, box-shadow .15s, background .15s;
        outline: none;
    }
    .sa-input:hover, .sa-textarea:hover { border-color: #D1D5DB; }
    .sa-input:focus, .sa-textarea:focus, .sa-select:focus {
        border-color: #C96A2B;
        box-shadow: 0 0 0 3px rgba(201,106,43,.12);
        background: #FFFCFA;
    }
    .sa-textarea { resize: vertical; min-height: 4.5rem; line-height: 1.5; }
    .sa-help { font-size: .68rem; color: #9CA3AF; margin-top: .35rem; }

    .sa-grid-2 { display: grid; grid-template-columns: 1fr; gap: 1rem 1.25rem; }
    .sa-grid-3 { display: grid; grid-template-columns: 1fr; gap: 1rem 1.25rem; }
    @media (min-width: 768px) {
        .sa-grid-2 { grid-template-columns: 1fr 1fr; }
        .sa-grid-3 { grid-template-columns: 1fr 1fr 1fr; }
    }
    .sa-layout {
        display: grid; grid-template-columns: 1fr; gap: 1.15rem;
    }
    @media (min-width: 1100px) {
        .sa-layout { grid-template-columns: 1.4fr .9fr; align-items: start; }
        .sa-layout-wide { grid-template-columns: 1fr; }
    }

    .sa-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: .45rem;
        padding: .7rem 1.25rem; border-radius: .85rem;
        font-size: .75rem; font-weight: 700; letter-spacing: .03em;
        font-family: Outfit, Inter, sans-serif;
        transition: background .15s, transform .1s, box-shadow .15s;
        border: none; cursor: pointer; text-decoration: none;
    }
    .sa-btn:active { transform: translateY(1px); }
    .sa-btn-primary {
        background: linear-gradient(180deg, #D17A3A 0%, #C96A2B 100%);
        color: #fff;
        box-shadow: 0 1px 2px rgba(201,106,43,.25), 0 6px 16px rgba(201,106,43,.18);
    }
    .sa-btn-primary:hover { background: #B55A20; color: #fff; }
    .sa-btn-ghost {
        background: #fff; color: #374151; border: 1px solid #E5E7EB;
    }
    .sa-btn-ghost:hover { background: #F9FAFB; border-color: #D1D5DB; }
    .sa-btn-danger {
        background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA;
    }
    .sa-btn-danger:hover { background: #FEE2E2; }
    .sa-btn-sm { padding: .45rem .8rem; font-size: .7rem; border-radius: .7rem; }

    /* Toggle cards */
    .sa-toggle-card {
        display: flex; align-items: center; gap: .9rem;
        padding: .95rem 1.05rem;
        border: 1px solid #E8EAED; border-radius: .95rem;
        background: #FAFBFC; transition: border-color .15s, background .15s, box-shadow .15s;
        cursor: pointer;
    }
    .sa-toggle-card:hover { border-color: #E7B58A; background: #fff; }
    .sa-toggle-card.is-on {
        border-color: rgba(201,106,43,.35);
        background: linear-gradient(135deg, #FFFBF7 0%, #fff 100%);
        box-shadow: 0 0 0 3px rgba(201,106,43,.06);
    }
    .sa-toggle-card .sa-toggle-icon {
        width: 2.35rem; height: 2.35rem; border-radius: .75rem;
        display: flex; align-items: center; justify-content: center;
        background: #F3F4F6; color: #6B7280; flex-shrink: 0;
    }
    .sa-toggle-card.is-on .sa-toggle-icon {
        background: #FFF7ED; color: #C96A2B;
    }
    .sa-toggle-card strong {
        display: block; font-size: .8rem; color: #111827; font-weight: 700;
    }
    .sa-toggle-card span.desc {
        display: block; font-size: .68rem; color: #9CA3AF; margin-top: .15rem;
    }

    .sa-switch { position: relative; width: 42px; height: 24px; flex-shrink: 0; }
    .sa-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
    .sa-switch span {
        position: absolute; inset: 0; background: #E5E7EB; border-radius: 999px; cursor: pointer; transition: .2s;
    }
    .sa-switch span:before {
        content: ""; position: absolute; width: 18px; height: 18px; left: 3px; top: 3px;
        background: #fff; border-radius: 50%; transition: .2s; box-shadow: 0 1px 3px rgba(0,0,0,.15);
    }
    .sa-switch input:checked + span { background: #C96A2B; }
    .sa-switch input:checked + span:before { transform: translateX(18px); }

    /* Sortable rows */
    .sa-drag {
        cursor: grab; color: #C4C9D1; border: 0; background: transparent;
        border-radius: .55rem; padding: .35rem; transition: color .15s, background .15s;
        flex-shrink: 0;
    }
    .sa-drag:hover { color: #C96A2B; background: #FFF7ED; }
    .sa-drag:active { cursor: grabbing; }
    .sa-row {
        display: flex; align-items: flex-start; gap: .75rem;
        padding: 1rem 1.05rem;
        border: 1px solid #E8EAED; border-radius: 1rem;
        background: #fff;
        transition: box-shadow .15s, border-color .15s, background .15s, opacity .15s, transform .15s;
        user-select: none;
    }
    .sa-row:hover { border-color: #E7B58A88; box-shadow: 0 4px 14px rgba(16,24,40,.04); }
    .sa-row.sortable-ghost {
        opacity: .45; background: #FFF7ED; border-color: #E7B58A; border-style: dashed;
    }
    .sa-row.sortable-chosen {
        box-shadow: 0 14px 36px rgba(201,106,43,.14);
        border-color: #C96A2B; background: #fff; z-index: 5;
    }
    .sa-row.is-off { opacity: .48; }
    .sa-row.is-off .sa-order { background: #F3F4F6; color: #9CA3AF; border-color: #E5E7EB; }
    .sa-order {
        width: 1.85rem; height: 1.85rem; border-radius: .55rem; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .68rem; font-weight: 800; font-family: ui-monospace, monospace;
        background: #FFF7ED; color: #C96A2B; border: 1px solid rgba(231,181,138,.55);
        margin-top: .15rem;
    }
    .sa-list { display: flex; flex-direction: column; gap: .65rem; }

    .sa-empty {
        padding: 2.5rem 1.5rem; text-align: center;
        font-size: .8rem; color: #9CA3AF;
        border: 1.5px dashed #E5E7EB; border-radius: 1rem; background: #FAFBFC;
    }
    .sa-empty strong { display: block; color: #6B7280; margin-bottom: .35rem; font-size: .85rem; }

    /* Color picker */
    .sa-color-wrap {
        display: flex; align-items: center; gap: .65rem;
        padding: .45rem .65rem; border: 1px solid #E5E7EB; border-radius: .85rem; background: #fff;
    }
    .sa-color-wrap input[type="color"] {
        width: 2.5rem; height: 2.5rem; border: 0; padding: 0; background: transparent;
        border-radius: .55rem; cursor: pointer;
    }
    .sa-color-wrap input[type="text"] {
        flex: 1; border: 0; outline: none; font-size: .8rem; font-family: ui-monospace, monospace; color: #374151;
    }

    /* SEO tags */
    .sa-tags {
        display: flex; flex-wrap: wrap; align-items: center; gap: .45rem;
        min-height: 3rem; padding: .55rem .7rem;
        border: 1px solid #E5E7EB; border-radius: .85rem; background: #fff;
        cursor: text; transition: border-color .15s, box-shadow .15s;
    }
    .sa-tags:focus-within {
        border-color: #C96A2B;
        box-shadow: 0 0 0 3px rgba(201,106,43,.12);
        background: #FFFCFA;
    }
    .sa-tag {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .28rem .55rem .28rem .7rem;
        border-radius: 999px;
        background: linear-gradient(180deg, #FFF7ED, #FFEDD5);
        color: #9A4A18; border: 1px solid rgba(201,106,43,.25);
        font-size: .72rem; font-weight: 600;
        animation: saTagIn .18s ease;
    }
    @keyframes saTagIn {
        from { transform: scale(.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .sa-tag button {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.1rem; height: 1.1rem; border-radius: 999px;
        border: 0; background: rgba(201,106,43,.12); color: #C96A2B;
        font-size: .7rem; line-height: 1; cursor: pointer; padding: 0;
    }
    .sa-tag button:hover { background: #C96A2B; color: #fff; }
    .sa-tags-input {
        flex: 1; min-width: 8rem; border: 0; outline: none; background: transparent;
        font-size: .8rem; padding: .35rem .25rem; color: #111827;
    }

    /* SEO preview */
    .sa-serp {
        padding: 1.15rem 1.2rem; border-radius: 1rem;
        background: #F8FAFC; border: 1px solid #E8EAED;
    }
    .sa-serp .serp-url { font-size: .72rem; color: #202124; margin-bottom: .2rem; word-break: break-all; }
    .sa-serp .serp-title { font-size: 1.05rem; color: #1a0dab; font-weight: 500; line-height: 1.3; margin-bottom: .2rem; }
    .sa-serp .serp-desc { font-size: .8rem; color: #4d5156; line-height: 1.45; }
    .sa-counter {
        font-size: .65rem; font-weight: 600; color: #9CA3AF; float: right;
    }
    .sa-counter.warn { color: #D97706; }
    .sa-counter.bad { color: #DC2626; }

    /* Sticky action bar */
    .sa-actions {
        position: sticky; bottom: 0; z-index: 20;
        margin-top: 1.25rem;
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: .75rem;
        padding: .9rem 1.15rem;
        background: rgba(255,255,255,.92);
        backdrop-filter: blur(10px);
        border: 1px solid #E8EAED;
        border-radius: 1rem;
        box-shadow: 0 -4px 24px rgba(16,24,40,.06);
    }

    /* Info callout */
    .sa-callout {
        display: flex; gap: .75rem; align-items: flex-start;
        padding: .9rem 1rem; border-radius: .95rem;
        background: #F0F9FF; border: 1px solid #BAE6FD; color: #0C4A6E;
        font-size: .75rem; line-height: 1.5;
    }
    .sa-callout.warn { background: #FFFBEB; border-color: #FDE68A; color: #92400E; }
    .sa-callout svg { flex-shrink: 0; margin-top: .1rem; }

    /* Modal */
    .sa-modal {
        position: fixed; inset: 0; z-index: 60;
        display: none; align-items: center; justify-content: center;
        padding: 1rem; background: rgba(17,24,39,.48); backdrop-filter: blur(3px);
    }
    .sa-modal.is-open { display: flex; }
    .sa-modal-box {
        background: #fff; border-radius: 1.15rem; border: 1px solid #E8EAED;
        box-shadow: 0 24px 64px rgba(0,0,0,.18);
        width: 100%; max-width: 32rem; max-height: 90vh; overflow-y: auto;
        padding: 1.35rem 1.4rem 1.4rem;
    }
    .sa-modal-box h3 {
        margin: 0 0 1rem; font-size: .95rem; font-weight: 700;
        font-family: Outfit, Inter, sans-serif; color: #111827;
    }

    /* Toast */
    .sa-toast {
        position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
        min-width: 220px; max-width: 360px;
        padding: .85rem 1.1rem; border-radius: .9rem;
        background: #111827; color: #fff; font-size: .75rem; font-weight: 600;
        box-shadow: 0 12px 40px rgba(0,0,0,.22);
        transform: translateY(120%); opacity: 0;
        transition: transform .25s ease, opacity .25s ease;
        display: flex; align-items: center; gap: .6rem;
    }
    .sa-toast.show { transform: translateY(0); opacity: 1; }
    .sa-toast.ok { background: #065F46; }
    .sa-toast.err { background: #991B1B; }
    .sa-toast-dot {
        width: 8px; height: 8px; border-radius: 50%; background: #6EE7B7; flex-shrink: 0;
    }
    .sa-toast.err .sa-toast-dot { background: #FCA5A5; }

    /* Thumb for slider */
    .sa-thumb {
        width: 4.25rem; height: 3.1rem; border-radius: .65rem;
        object-fit: cover; border: 1px solid #E5E7EB; flex-shrink: 0; margin-top: .1rem;
        background: #F3F4F6;
    }
    .sa-thumb-ph {
        width: 4.25rem; height: 3.1rem; border-radius: .65rem;
        background: linear-gradient(135deg, #F3F4F6, #E5E7EB);
        border: 1px solid #E5E7EB; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: .6rem; font-weight: 700; color: #9CA3AF; margin-top: .1rem;
    }
</style>
@endpush

@push('scripts')
<script>
window.saToast = function (msg, type) {
    type = type || 'ok';
    let el = document.getElementById('saToast');
    if (!el) {
        el = document.createElement('div');
        el.id = 'saToast';
        el.className = 'sa-toast';
        el.innerHTML = '<span class="sa-toast-dot"></span><span class="sa-toast-msg"></span>';
        document.body.appendChild(el);
    }
    el.className = 'sa-toast ' + type + ' show';
    el.querySelector('.sa-toast-msg').textContent = msg;
    clearTimeout(el._t);
    el._t = setTimeout(() => el.classList.remove('show'), 2200);
};

window.saReorderNumbers = function (container) {
    if (!container) return;
    container.querySelectorAll('[data-id]').forEach((row, i) => {
        const badge = row.querySelector('.sa-order');
        if (badge) badge.textContent = String(i + 1).padStart(2, '0');
    });
};

window.saInitSortable = function (el, type, reorderUrl, csrf) {
    if (!el || !window.Sortable) return null;
    return Sortable.create(el, {
        handle: '.sa-drag',
        animation: 200,
        easing: 'cubic-bezier(0.2, 0, 0, 1)',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: async function () {
            window.saReorderNumbers(el);
            const ids = [...el.querySelectorAll('[data-id]')].map(n => parseInt(n.dataset.id, 10));
            try {
                const res = await fetch(reorderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ type, ids })
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                window.saToast('Sıralama kaydedildi', 'ok');
            } catch (e) {
                window.saToast('Sıralama kaydedilemedi', 'err');
            }
        }
    });
};

window.saInitToggles = function (toggleUrl, csrf) {
    document.querySelectorAll('.toggle-aktif').forEach(cb => {
        if (cb.dataset.bound) return;
        cb.dataset.bound = '1';
        cb.addEventListener('change', async function () {
            this.closest('.sa-row')?.classList.toggle('is-off', !this.checked);
            try {
                const res = await fetch(toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        type: this.dataset.type,
                        id: parseInt(this.dataset.id, 10),
                        aktif: this.checked
                    })
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                window.saToast(this.checked ? 'Aktif edildi' : 'Pasif edildi', 'ok');
            } catch (e) {
                this.checked = !this.checked;
                this.closest('.sa-row')?.classList.toggle('is-off', !this.checked);
                window.saToast('Durum güncellenemedi', 'err');
            }
        });
    });
};

window.saBindToggleCards = function () {
    document.querySelectorAll('.sa-toggle-card').forEach(card => {
        const input = card.querySelector('input[type="checkbox"]');
        if (!input) return;
        const sync = () => card.classList.toggle('is-on', input.checked);
        sync();
        input.addEventListener('change', sync);
        card.addEventListener('click', (e) => {
            if (e.target.closest('input, label, a, button')) return;
            input.checked = !input.checked;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
};

/** Tag input: Enter / virgül / blur ile ekle */
window.saInitTagInput = function (root) {
    const wrap = typeof root === 'string' ? document.querySelector(root) : root;
    if (!wrap || wrap.dataset.ready) return;
    wrap.dataset.ready = '1';
    const hidden = wrap.querySelector('input[type="hidden"]');
    const input = wrap.querySelector('.sa-tags-input');
    if (!hidden || !input) return;

    let tags = (hidden.value || '')
        .split(',')
        .map(t => t.trim())
        .filter(Boolean);

    const render = () => {
        wrap.querySelectorAll('.sa-tag').forEach(n => n.remove());
        tags.forEach((tag, i) => {
            const el = document.createElement('span');
            el.className = 'sa-tag';
            el.innerHTML = '<span></span><button type="button" aria-label="Kaldır">×</button>';
            el.querySelector('span').textContent = tag;
            el.querySelector('button').addEventListener('click', (e) => {
                e.stopPropagation();
                tags.splice(i, 1);
                commit();
            });
            wrap.insertBefore(el, input);
        });
        hidden.value = tags.join(', ');
    };

    const commit = () => render();

    const add = (raw) => {
        raw.split(/[,;]+/).forEach(part => {
            const t = part.trim().replace(/\s+/g, ' ');
            if (!t) return;
            if (tags.some(x => x.toLocaleLowerCase('tr') === t.toLocaleLowerCase('tr'))) return;
            if (tags.length >= 30) return;
            tags.push(t);
        });
        commit();
    };

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            add(input.value);
            input.value = '';
        } else if (e.key === 'Backspace' && !input.value && tags.length) {
            tags.pop();
            commit();
        }
    });
    input.addEventListener('blur', () => {
        if (input.value.trim()) {
            add(input.value);
            input.value = '';
        }
    });
    wrap.addEventListener('click', () => input.focus());
    render();
};
</script>
@endpush
