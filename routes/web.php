<?php

use App\Http\Controllers\Frontend\BookingProxyController;
use App\Http\Controllers\Frontend\SiteController;
use App\Http\Controllers\Panel\AuthController;
use App\Http\Controllers\Panel\BlogController;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\EgitimController;
use App\Http\Controllers\Panel\FaqController;
use App\Http\Controllers\Panel\FinansController;
use App\Http\Controllers\Panel\GaleriController;
use App\Http\Controllers\Panel\HizmetController;
use App\Http\Controllers\Panel\ProfilController;
use App\Http\Controllers\Panel\RandevuController;
use App\Http\Controllers\Panel\SiteAyarlariController;
use App\Http\Controllers\Panel\YorumController;
use Illuminate\Support\Facades\Route;

// ——— Public klinik website ———
Route::controller(SiteController::class)->group(function () {
    Route::get('/', 'anasayfa')->name('frontend.anasayfa');
    Route::get('/hakkimizda', 'hakkimda')->name('frontend.hakkimda');
    Route::get('/hakkimda', 'hakkimda'); // alias
    Route::get('/hekimler', 'hekimler')->name('frontend.hekimler');
    Route::get('/hekimler/{slug}', 'hekimDetay')->name('frontend.hekim.detay');
    Route::get('/hizmetler', 'hizmetler')->name('frontend.hizmetler');
    Route::get('/hizmetler/{slug}', 'hizmetDetay')->name('frontend.hizmet.detay');
    Route::get('/galeri', 'galeri')->name('frontend.galeri');
    Route::get('/blog', 'blog')->name('frontend.blog');
    Route::get('/blog/{slug}', 'blogDetay')->name('frontend.blog.detay');
    Route::get('/sss', 'sss')->name('frontend.sss');
    Route::get('/iletisim', 'iletisim')->name('frontend.iletisim');
    Route::get('/randevu', 'iletisim')->name('frontend.randevu');
    Route::get('/sitemap.xml', 'sitemap')->name('frontend.sitemap');
});

// Misafir randevu — clinic API proxy
Route::prefix('site-api/booking')
    ->name('frontend.booking.')
    ->middleware('throttle:60,1')
    ->group(function () {
        Route::get('/status', [BookingProxyController::class, 'status'])->name('status');
        Route::get('/doctors', [BookingProxyController::class, 'doctors'])->name('doctors');
        Route::get('/services', [BookingProxyController::class, 'services'])->name('services');
        Route::get('/slots', [BookingProxyController::class, 'slots'])->name('slots');
        Route::post('/otp/send', [BookingProxyController::class, 'sendOtp'])
            ->middleware('throttle:8,1')
            ->name('otp.send');
        Route::post('/otp/verify', [BookingProxyController::class, 'verifyOtp'])
            ->middleware('throttle:15,1')
            ->name('otp.verify');
        Route::post('/appointments', [BookingProxyController::class, 'storeAppointment'])
            ->middleware('throttle:10,1')
            ->name('appointments');
    });

// Platform → doktor sitesi webhook (HMAC)
Route::post('/webhook/receiver', [\App\Http\Controllers\Frontend\WebhookReceiverController::class, 'receive'])
    ->middleware('throttle:120,1')
    ->name('frontend.webhook.receiver');

// ——— Hekim paneli (API senkron, ana site ile aynı UI) ———
Route::prefix('yonetim')->name('panel.')->group(function () {
    Route::get('/giris', [AuthController::class, 'girisFormu'])->name('giris');
    Route::post('/giris', [AuthController::class, 'giris'])->middleware('throttle:12,1')->name('giris.post');
    Route::post('/cikis', [AuthController::class, 'cikis'])->name('cikis');

    Route::middleware('panel.auth')->group(function () {
        // Yerel panel — API zorunlu değil
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/panel', [DashboardController::class, 'index']);

        // API entegrasyonu: ana sunucuda üretilen key buraya girilir (üretim yok)
        Route::get('/api-entegrasyon', [\App\Http\Controllers\Panel\WebSitesiController::class, 'index'])->name('api-entegrasyon');
        Route::post('/api-entegrasyon', [\App\Http\Controllers\Panel\WebSitesiController::class, 'kaydet'])->name('api-entegrasyon.kaydet');
        Route::post('/api-entegrasyon/test', [\App\Http\Controllers\Panel\WebSitesiController::class, 'test'])->name('api-entegrasyon.test');
        Route::post('/api-entegrasyon/temizle', [\App\Http\Controllers\Panel\WebSitesiController::class, 'temizle'])->name('api-entegrasyon.temizle');
        // Eski URL uyumluluğu
        Route::get('/web-sitesi', fn () => redirect()->route('panel.api-entegrasyon'))->name('web-sitesi');
        Route::post('/web-sitesi', [\App\Http\Controllers\Panel\WebSitesiController::class, 'kurulum'])->name('web-sitesi.kurulum');
        Route::post('/web-sitesi/api-anahtari', [\App\Http\Controllers\Panel\WebSitesiController::class, 'apiAnahtari'])->name('web-sitesi.api-anahtari');

        // Site ayarları — LOCAL SQLite (API bağımsız)
        Route::prefix('site-ayarlari')->name('site-ayarlari.')->group(function () {
            Route::get('/', [SiteAyarlariController::class, 'index'])->name('index');
            Route::get('/genel', [SiteAyarlariController::class, 'genel'])->name('genel');
            Route::post('/genel', [SiteAyarlariController::class, 'kaydetGenel'])->name('genel.kaydet');
            Route::get('/temalar', [SiteAyarlariController::class, 'temalar'])->name('temalar');
            Route::post('/temalar', [SiteAyarlariController::class, 'kaydetTema'])->name('temalar.kaydet');
            Route::get('/menu', [SiteAyarlariController::class, 'menu'])->name('menu');
            Route::post('/menu', [SiteAyarlariController::class, 'menuKaydet'])->name('menu.kaydet');
            Route::get('/slider', [SiteAyarlariController::class, 'slider'])->name('slider');
            Route::post('/slider', [SiteAyarlariController::class, 'sliderStore'])->name('slider.store');
            Route::put('/slider/{id}', [SiteAyarlariController::class, 'sliderUpdate'])->name('slider.update');
            Route::post('/slider/{id}', [SiteAyarlariController::class, 'sliderUpdate']);
            Route::delete('/slider/{id}', [SiteAyarlariController::class, 'sliderDestroy'])->name('slider.destroy');
            Route::post('/slider/{id}/sil', [SiteAyarlariController::class, 'sliderDestroy']);
            Route::get('/anasayfa', [SiteAyarlariController::class, 'anasayfa'])->name('anasayfa');
            Route::post('/anasayfa', [SiteAyarlariController::class, 'anasayfaKaydet'])->name('anasayfa.kaydet');
            Route::get('/seo', [SiteAyarlariController::class, 'seo'])->name('seo');
            Route::post('/seo', [SiteAyarlariController::class, 'kaydetSeo'])->name('seo.kaydet');
            Route::get('/iletisim', [SiteAyarlariController::class, 'iletisim'])->name('iletisim');
            Route::post('/iletisim', [SiteAyarlariController::class, 'kaydetIletisim'])->name('iletisim.kaydet');
            Route::post('/reorder', [SiteAyarlariController::class, 'reorder'])->name('reorder');
            Route::post('/toggle', [SiteAyarlariController::class, 'toggle'])->name('toggle');
            Route::post('/{group}', [SiteAyarlariController::class, 'kaydet'])
                ->where('group', 'genel|menu|slider|anasayfa|seo|iletisim')
                ->name('kaydet');
        });

        // Platform API gerektiren rotalar
        Route::middleware('panel.api')->group(function () {
            Route::get('/profil', [ProfilController::class, 'edit'])->name('profil');
            Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
            Route::post('/profil', [ProfilController::class, 'update'])->name('profil.post');
            Route::get('/hakkimda', [ProfilController::class, 'hakkimda'])->name('hakkimda');
            Route::post('/hakkimda', [ProfilController::class, 'hakkimdaGuncelle'])->name('hakkimda.post');
            Route::put('/hakkimda', [ProfilController::class, 'hakkimdaGuncelle'])->name('hakkimda.update');
            Route::get('/sifre', [ProfilController::class, 'sifreFormu'])->name('sifre');
            Route::put('/sifre', [ProfilController::class, 'sifreGuncelle'])->name('sifre.update');
            Route::post('/sifre', [ProfilController::class, 'sifreGuncelle'])->name('sifre.post');

            Route::get('/randevular', [RandevuController::class, 'index'])->name('randevular');
            Route::get('/randevular/events', [RandevuController::class, 'events'])->name('randevular.events');
            Route::post('/randevular/periyot', [RandevuController::class, 'updatePeriod'])->name('randevular.periyot');
            Route::post('/randevular', [RandevuController::class, 'store'])->name('randevular.store');
            Route::delete('/randevular/{id}', [RandevuController::class, 'destroy'])->name('randevular.destroy');
            Route::post('/randevular/{id}/sil', [RandevuController::class, 'destroy']);
            Route::get('/randevular/talepler', [RandevuController::class, 'talepler'])->name('randevular.talepler');
            Route::put('/randevular/{id}/durum', [RandevuController::class, 'durum'])->name('randevular.durum');
            Route::post('/randevular/{id}/durum', [RandevuController::class, 'durum']);
            Route::put('/randevular/{id}/guncelle', [RandevuController::class, 'guncelle'])->name('randevular.guncelle');
            Route::post('/randevular/{id}/guncelle', [RandevuController::class, 'guncelle']);
            Route::get('/hastalar/ara', [RandevuController::class, 'hastaAra'])->name('randevular.hastalar-ara');
            Route::post('/hastalar', [RandevuController::class, 'hastaEkle'])->name('randevular.hasta-ekle');
            Route::get('/hastalar', [RandevuController::class, 'hastalar'])->name('hastalar');
            Route::post('/randevular/{id}/reschedule', [RandevuController::class, 'reschedule'])->name('randevular.reschedule');
            Route::get('/randevu/hizli-kapat-slotlar', [RandevuController::class, 'hizliKapatSlots'])->name('randevu.hizli-kapat-slotlar');
            Route::post('/randevu/hizli-kapat', [RandevuController::class, 'hizliKapatKaydet'])->name('randevu.hizli-kapat');
            Route::get('/randevu-ayarlari', [RandevuController::class, 'ayarlar'])->name('randevu-ayarlari');
            Route::put('/randevu-ayarlari', [RandevuController::class, 'ayarlarKaydet'])->name('randevu-ayarlari.update');
            Route::post('/randevu-ayarlari', [RandevuController::class, 'ayarlarKaydet']);
            Route::put('/calisma-saatleri', [RandevuController::class, 'calismaSaatleriKaydet'])->name('calisma-saatleri.update');
            Route::post('/calisma-saatleri', [RandevuController::class, 'calismaSaatleriKaydet']);
            Route::post('/randevu/izin-ekle', [RandevuController::class, 'izinEkle'])->name('randevu.izin-ekle');
            Route::post('/randevu/izin-sil/{id}', [RandevuController::class, 'izinSil'])->name('randevu.izin-sil');
            Route::delete('/randevu/izin-sil/{id}', [RandevuController::class, 'izinSil']);

            Route::get('/hizmetler', [HizmetController::class, 'index'])->name('hizmetler');
            Route::get('/hizmetler/ekle', [HizmetController::class, 'create'])->name('hizmetler.create');
            Route::post('/hizmetler', [HizmetController::class, 'store'])->name('hizmetler.store');
            Route::get('/hizmetler/{id}/duzenle', [HizmetController::class, 'edit'])->name('hizmetler.edit');
            Route::put('/hizmetler/{id}', [HizmetController::class, 'update'])->name('hizmetler.update');
            Route::post('/hizmetler/{id}', [HizmetController::class, 'update']);
            Route::delete('/hizmetler/{id}', [HizmetController::class, 'destroy'])->name('hizmetler.destroy');

            Route::get('/bloglar', [BlogController::class, 'index'])->name('bloglar');
            Route::get('/bloglar/ekle', [BlogController::class, 'create'])->name('bloglar.create');
            Route::post('/bloglar', [BlogController::class, 'store'])->name('bloglar.store');
            Route::get('/bloglar/{id}/duzenle', [BlogController::class, 'edit'])->name('bloglar.edit');
            Route::put('/bloglar/{id}', [BlogController::class, 'update'])->name('bloglar.update');
            Route::post('/bloglar/{id}', [BlogController::class, 'update']);
            Route::delete('/bloglar/{id}', [BlogController::class, 'destroy'])->name('bloglar.destroy');

            // Eğitimler (API senkron — ana site hekim paneli ile aynı)
            Route::get('/egitimler', [EgitimController::class, 'index'])->name('egitimler.index');
            Route::get('/egitimler/olustur', [EgitimController::class, 'create'])->name('egitimler.create');
            Route::post('/egitimler', [EgitimController::class, 'store'])->name('egitimler.store');
            Route::get('/egitimler/basvurular', [EgitimController::class, 'basvurularTumu'])->name('egitimler.basvurular.tumu');
            Route::get('/egitimler/{id}/duzenle', [EgitimController::class, 'edit'])->name('egitimler.edit')->whereNumber('id');
            Route::put('/egitimler/{id}', [EgitimController::class, 'update'])->name('egitimler.update')->whereNumber('id');
            Route::post('/egitimler/{id}', [EgitimController::class, 'update'])->whereNumber('id');
            Route::delete('/egitimler/{id}', [EgitimController::class, 'destroy'])->name('egitimler.destroy')->whereNumber('id');
            Route::get('/egitimler/{id}/basvurular', [EgitimController::class, 'basvurular'])->name('egitimler.basvurular')->whereNumber('id');
            Route::post('/egitimler/{id}/basvurular/{basvuruId}/durum', [EgitimController::class, 'basvuruDurum'])->name('egitimler.basvuru.durum')->whereNumber('id')->whereNumber('basvuruId');
            Route::post('/egitimler/{id}/basvurular/{basvuruId}/odeme', [EgitimController::class, 'basvuruOdeme'])->name('egitimler.basvuru.odeme')->whereNumber('id')->whereNumber('basvuruId');

            Route::get('/sss', [FaqController::class, 'index'])->name('faqs');
            Route::post('/sss', [FaqController::class, 'store'])->name('faqs.store');
            Route::put('/sss/{id}', [FaqController::class, 'update'])->name('faqs.update');
            Route::post('/sss/{id}', [FaqController::class, 'update']);
            Route::delete('/sss/{id}', [FaqController::class, 'destroy'])->name('faqs.destroy');
            Route::post('/sss/{id}/toggle', [FaqController::class, 'toggle'])->name('faqs.toggle');

            Route::get('/galeri', [GaleriController::class, 'index'])->name('galeri');
            Route::post('/galeri', [GaleriController::class, 'store'])->name('galeri.store');
            Route::post('/galeri/sirala', [GaleriController::class, 'sirala'])->name('galeri.sirala');
            Route::put('/galeri/{id}', [GaleriController::class, 'update'])->name('galeri.update');
            Route::post('/galeri/{id}', [GaleriController::class, 'update']);
            Route::post('/galeri/{id}/guncelle', [GaleriController::class, 'update'])->name('galeri.guncelle');
            Route::delete('/galeri/{id}', [GaleriController::class, 'destroy'])->name('galeri.destroy');

            Route::get('/yorumlar', [YorumController::class, 'index'])->name('yorumlar');
            Route::post('/yorumlar/{id}/yanit', [YorumController::class, 'yanit'])->name('yorumlar.yanit');
            Route::post('/yorumlar/{id}/yanitla', [YorumController::class, 'yanit'])->name('yorumlar.yanitla');
            Route::put('/yorumlar/{id}/durum', [YorumController::class, 'durum'])->name('yorumlar.durum');
            Route::post('/yorumlar/{id}/durum', [YorumController::class, 'durum']);

            Route::get('/finans', [FinansController::class, 'index'])->name('finans');
            Route::get('/finans/gelirler', [FinansController::class, 'gelirler'])->name('finans.gelirler');
            Route::get('/finans/giderler', [FinansController::class, 'giderler'])->name('finans.giderler');
            Route::get('/finans/kategoriler', [FinansController::class, 'kategoriler'])->name('finans.kategoriler');
            Route::get('/finans/hasta-bakiyeleri', [FinansController::class, 'hastaBakiyeleri'])->name('finans.hasta-bakiyeleri');
            Route::get('/finans/rapor/pdf', [FinansController::class, 'raporPdf'])->name('finans.rapor-pdf');
            Route::post('/finans/gelirler', [FinansController::class, 'storeGelir'])->name('finans.gelir.store');
            Route::post('/finans/gelirler/store', [FinansController::class, 'storeGelir'])->name('finans.gelirler.store');
            Route::post('/finans/gelirler/{id}/guncelle', [FinansController::class, 'updateGelir'])->name('finans.gelirler.update');
            Route::post('/finans/gelirler/{id}/kalem', [FinansController::class, 'storeKalem'])->name('finans.gelirler.kalem.store');
            Route::delete('/finans/gelirler/{odemeId}/kalem/{kalemId}', [FinansController::class, 'destroyKalem'])->name('finans.gelirler.kalem.destroy');
            Route::post('/finans/gelirler/{odemeId}/kalem/{kalemId}/sil', [FinansController::class, 'destroyKalem']);
            Route::delete('/finans/gelirler/{id}', [FinansController::class, 'destroyGelir'])->name('finans.gelir.destroy');
            Route::delete('/finans/gelirler/{id}/sil', [FinansController::class, 'destroyGelir'])->name('finans.gelirler.destroy');
            Route::post('/finans/gelirler/{id}/sil', [FinansController::class, 'destroyGelir']);
            Route::post('/finans/giderler', [FinansController::class, 'storeGider'])->name('finans.gider.store');
            Route::post('/finans/giderler/store', [FinansController::class, 'storeGider'])->name('finans.giderler.store');
            Route::post('/finans/giderler/{id}/guncelle', [FinansController::class, 'updateGider'])->name('finans.giderler.update');
            Route::delete('/finans/giderler/{id}', [FinansController::class, 'destroyGider'])->name('finans.gider.destroy');
            Route::delete('/finans/giderler/{id}/sil', [FinansController::class, 'destroyGider'])->name('finans.giderler.destroy');
            Route::post('/finans/giderler/{id}/sil', [FinansController::class, 'destroyGider']);
            Route::post('/finans/kategoriler', [FinansController::class, 'storeKategori'])->name('finans.kategori.store');
            Route::post('/finans/kategoriler/store', [FinansController::class, 'storeKategori'])->name('finans.kategoriler.store');
            Route::post('/finans/kategoriler/{id}/guncelle', [FinansController::class, 'updateKategori'])->name('finans.kategoriler.update');
            Route::post('/finans/kategoriler/{id}/toggle', [FinansController::class, 'toggleKategori'])->name('finans.kategoriler.toggle');
            Route::delete('/finans/kategoriler/{id}', [FinansController::class, 'destroyKategori'])->name('finans.kategori.destroy');
            Route::delete('/finans/kategoriler/{id}/sil', [FinansController::class, 'destroyKategori'])->name('finans.kategoriler.destroy');
            Route::post('/finans/kategoriler/{id}/sil', [FinansController::class, 'destroyKategori']);
        });
    });
});
