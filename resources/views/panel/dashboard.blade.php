@extends('panel.layouts.app')

@section('baslik', 'Hekim Paneli')
@section('sayfa_baslik', 'Panel Özeti')

@section('icerik')
    <!-- Welcome Banner -->
    <div class="mb-8 p-8 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm relative overflow-hidden group">
        <div class="relative z-10">
            <h2 class="text-2xl font-bold font-display text-[#111827] tracking-tight">
                Tekrar Hoş Geldiniz, Sayın {{ ($doktor->unvan ?? '') ? $doktor->unvan.' ' : '' }}{{ $doktor->ad_soyad ?? 'Hekim' }}!
            </h2>
            <p class="text-sm text-[#6B7280] mt-1.5">
                Doktor sitesi paneli ana platform ile API üzerinden senkron çalışır. Randevu, hizmet, blog ve finans işlemleriniz her iki tarafta da geçerlidir.
            </p>
        </div>
        <div class="absolute right-0 bottom-0 top-0 w-1/3 bg-gradient-to-l from-[#FFF7ED]/35 to-transparent pointer-events-none"></div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] hover:-translate-y-0.5 transition-all duration-300">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Toplam Randevularım</span>
            <span class="text-3xl font-bold font-display text-[#111827] mt-2 block">{{ $toplamRandevu }}</span>
            <span class="text-xs text-[#C96A2B] mt-1.5 block font-medium">Sistemde kayıtlı toplam randevu</span>
        </div>
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] hover:-translate-y-0.5 transition-all duration-300">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Kayıtlı Hastalarım</span>
            <span class="text-3xl font-bold font-display text-[#111827] mt-2 block">{{ $kayitliHasta }}</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">Randevu almış tekil hasta sayısı</span>
        </div>
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] hover:-translate-y-0.5 transition-all duration-300">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Bekleyen Talepler</span>
            <span class="text-3xl font-bold font-display text-[#111827] mt-2 block">{{ $bekleyenTalep }}</span>
            <span class="text-xs text-[#C96A2B] mt-1.5 block font-medium">Onay bekleyen randevu talebi</span>
        </div>
        <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-[0_4px_24px_rgba(31,41,55,0.04)] hover:-translate-y-0.5 transition-all duration-300">
            <span class="text-xs font-bold text-[#6B7280] uppercase tracking-wider block font-display">Randevu Durumu</span>
            <span class="text-3xl font-bold font-display {{ $klinikDurumu ? 'text-emerald-600' : 'text-red-500' }} mt-2 block">{{ $klinikDurumu ? 'Aktif' : 'Pasif' }}</span>
            <span class="text-xs text-[#6B7280] mt-1.5 block font-medium">{{ $klinikDurumu ? 'Randevu alımına açık' : 'Randevu alımına kapalı' }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <h3 class="text-lg font-bold font-display text-[#111827] mb-4">Hızlı Erişim ve İşlemler</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="{{ route('panel.randevular') }}" class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#111827] group-hover:text-[#C96A2B] transition-colors">Randevu Talepleri</span>
                            <span class="block text-[11px] text-[#6B7280] mt-0.5">Onay bekleyenleri yönet</span>
                        </div>
                    </a>
                    <a href="{{ route('panel.hizmetler') }}" class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#111827] group-hover:text-[#C96A2B] transition-colors">Hizmetler</span>
                            <span class="block text-[11px] text-[#6B7280] mt-0.5">Tedavi tanımlarını düzenle</span>
                        </div>
                    </a>
                    <a href="{{ route('panel.bloglar') }}" class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#111827] group-hover:text-[#C96A2B] transition-colors">Blog Yazıları</span>
                            <span class="block text-[11px] text-[#6B7280] mt-0.5">İçerikleri yönet</span>
                        </div>
                    </a>
                    <a href="{{ route('panel.randevu-ayarlari') }}" class="p-4 rounded-xl bg-[#FAFAFA] border border-[#E5E7EB] hover:bg-white hover:border-[#C96A2B] transition-all duration-200 group flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#FFF7ED] text-[#C96A2B] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <span class="block text-sm font-semibold text-[#111827] group-hover:text-[#C96A2B] transition-colors">Randevu Ayarları</span>
                            <span class="block text-[11px] text-[#6B7280] mt-0.5">Periyot ve çalışma saatleri</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-white border border-[#E5E7EB] shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold font-display text-[#111827]">Bugünün Randevuları</h3>
                    <a href="{{ route('panel.randevular', ['durum' => 'all']) }}" class="text-xs font-bold text-[#C96A2B]">Tümü →</a>
                </div>
                <div class="divide-y divide-[#E5E7EB]">
                    @forelse($bugunRandevular as $r)
                        <div class="py-3 flex justify-between gap-2 text-sm">
                            <div>
                                <span class="font-bold text-[#111827] font-display">{{ $r->saat ?? '' }}</span>
                                <span class="text-[#4B5563]">— {{ $r->ad ?? '' }} {{ $r->soyad ?? '' }}</span>
                                <div class="text-[11px] text-[#6B7280]">{{ is_object($r->hizmet ?? null) ? ($r->hizmet->ad ?? '') : '' }}</div>
                            </div>
                            <span class="text-[10px] font-bold uppercase text-[#6B7280]">{{ $r->durum ?? '' }}</span>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-[#6B7280]">Bugün randevu yok.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
