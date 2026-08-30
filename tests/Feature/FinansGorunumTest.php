<?php

namespace Tests\Feature;

use App\Support\ApiData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Finans sayfaları API verisiyle çizilebilmeli.
 *
 * Bildirilen sorun: /yonetim/finans/gelirler HTTP 500 veriyordu.
 *
 * Sunucu kaydındaki gerçek hata:
 *     Undefined property: stdClass::$finansKategori
 *     (panel/finans/gelirler.blade.php)
 *
 * Sebep: bu sayfaların verisi ana API'den geliyor ve ApiData::obj() onu
 * `json_decode(json_encode(...))` ile düz stdClass'a çeviriyor. Laravel
 * bir modeli JSON'a çevirirken İLİŞKİ adlarını snake_case yapar
 * ($snakeAttributes = true), yani API'nin gönderdiği anahtar
 * `finans_kategori`. Görünüm ise Eloquent ilişki adıyla `finansKategori`
 * okuyordu.
 *
 * PHP 8'de stdClass üzerinde tanımsız özellik okumak Warning üretir ve
 * Laravel bunu ErrorException'a çevirir — bu yüzden `@if(...)` koruma
 * sağlamıyordu: hata kontrolün kendisinde oluşuyordu.
 *
 * TumRotalarTest bunu yakalayamamıştı; o test oturumsuz gezdiği için bu
 * sayfalar giriş ekranına yönleniyor ve görünüm hiç çizilmiyordu. Bu
 * yüzden burada görünüm DOĞRUDAN çiziliyor.
 */
class FinansGorunumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Panel düzeni $errors kullanıyor; bunu normalde web yığınındaki
        // ShareErrorsFromSession ara katmanı paylaşır. Görünümü doğrudan
        // çizdiğimiz için burada elle sağlanıyor.
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);
    }

    /**
     * API'nin gönderdiği gerçek şekil: ilişkiler snake_case anahtarla.
     *
     * @return array<string, mixed>
     */
    private function apiKategori(): array
    {
        return ['id' => 3, 'ad' => 'Seans Geliri', 'renk' => '#10B981', 'tur' => 'gelir'];
    }

    private function odemeler(bool $kategoriVar = true): \Illuminate\Pagination\LengthAwarePaginator
    {
        // odemeler tablosunun gercek sutunlari (baslik sutunu YOK)
        $kayit = [
            'id' => 1,
            'doktor_id' => 1,
            'randevu_id' => null,
            'hasta_id' => 7,
            'hizmet_id' => 2,
            'tutar' => 1500,
            'odenen_tutar' => 1500,
            'odeme_yontemi' => 'nakit',
            'durum' => 'odendi',
            'aciklama' => 'Seans ücreti',
            'odeme_tarihi' => '2026-08-20 10:00:00',
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
            'finans_kategori_id' => $kategoriVar ? 3 : null,
            // ILISKI: API bunu snake_case gonderir
            'finans_kategori' => $kategoriVar ? $this->apiKategori() : null,
            'hasta' => ['id' => 7, 'ad' => 'Ayse', 'soyad' => 'Yilmaz'],
            'hizmet' => ['id' => 2, 'ad' => 'Bireysel Terapi'],
            'randevu' => null,
            'kalemler' => [],
        ];

        return ApiData::paginate([$kayit], ['current_page' => 1, 'total' => 1, 'last_page' => 1]);
    }

    private function giderler(bool $kategoriVar = true): \Illuminate\Pagination\LengthAwarePaginator
    {
        // giderler tablosunun gercek sutunlari
        $kayit = [
            'id' => 1,
            'doktor_id' => 1,
            'kategori' => 'diger',
            'baslik' => 'Kira',
            'tutar' => 8000,
            'tarih' => '2026-08-01 00:00:00',
            'aciklama' => ' Agustos ayi ofis kirasi',
            'belge_yolu' => null,
            'created_at' => '2026-08-01 00:00:00',
            'updated_at' => '2026-08-01 00:00:00',
            'finans_kategori_id' => $kategoriVar ? 4 : null,
            'finans_kategori' => $kategoriVar
                ? ['id' => 4, 'ad' => 'Ofis Gideri', 'renk' => '#EF4444', 'tur' => 'gider']
                : null,
        ];

        return ApiData::paginate([$kayit], ['current_page' => 1, 'total' => 1, 'last_page' => 1]);
    }

    // ------------------------------------------------------------ gelirler

    public function test_gelirler_gorunumu_cizilir(): void
    {
        $html = view('panel.finans.gelirler', [
            'odemeler' => $this->odemeler(),
            'gelirKategorileri' => ApiData::collection([$this->apiKategori()]),
            'hastalar' => ApiData::collection([
                ['id' => 7, 'ad' => 'Ayse', 'soyad' => 'Yilmaz', 'ad_soyad' => 'Ayse Yilmaz'],
            ]),
            'hizmetler' => ApiData::collection([['id' => 2, 'ad' => 'Bireysel Terapi', 'fiyat' => 1500, 'sure' => 50]]),
        ])->render();

        $this->assertStringContainsString('1.500,00', $html);
    }

    /**
     * Kategori rozeti gerçekten basılmalı; sadece "patlamadı" yetmez.
     */
    public function test_gelirlerde_kategori_rozeti_basilir(): void
    {
        $html = view('panel.finans.gelirler', [
            'odemeler' => $this->odemeler(),
            'gelirKategorileri' => ApiData::collection([$this->apiKategori()]),
            'hastalar' => ApiData::collection([]),
            'hizmetler' => ApiData::collection([]),
        ])->render();

        $this->assertStringContainsString('Seans Geliri', $html, 'Kategori adi basilmadi.');
        $this->assertStringContainsString('#10B981', $html, 'Kategori rengi basilmadi.');
    }

    /**
     * Kategorisiz kayıt da 500 vermemeli.
     */
    public function test_gelirler_kategorisiz_kayitla_cizilir(): void
    {
        $html = view('panel.finans.gelirler', [
            'odemeler' => $this->odemeler(kategoriVar: false),
            'gelirKategorileri' => ApiData::collection([]),
            'hastalar' => ApiData::collection([]),
            'hizmetler' => ApiData::collection([]),
        ])->render();

        $this->assertStringContainsString('1.500,00', $html);
        $this->assertStringNotContainsString('Seans Geliri', $html);
    }

    /**
     * İKİNCİ hata: `$odeme->kalemler->count()`.
     *
     * ApiData `json_decode(json_encode(...))` kullanıyor ve assoc=false
     * yalnızca JSON NESNELERİNİ stdClass yapar; JSON dizileri PHP dizisi
     * olarak kalır. Yani `kalemler` bir dizidir ve üzerinde `->count()`
     * çağırmak ölümcül hata verir:
     *
     *     Call to a member function count() on array
     *
     * finansKategori hatası düzeltilir düzeltilmez aynı sayfada bu
     * patlayacaktı; kayıtta henüz görünmüyordu çünkü ilk hata daha önce
     * oluşuyordu.
     */
    public function test_odeme_kalemleri_olan_kayit_cizilir(): void
    {
        $kayit = [
            'id' => 1,
            'doktor_id' => 1,
            'randevu_id' => null,
            'hasta_id' => 7,
            'hizmet_id' => 2,
            'tutar' => 1500,
            'odenen_tutar' => 900,
            'odeme_yontemi' => 'nakit',
            'durum' => 'kismi',
            'aciklama' => 'Seans ücreti',
            'odeme_tarihi' => '2026-08-20 10:00:00',
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
            'finans_kategori_id' => 3,
            'finans_kategori' => $this->apiKategori(),
            'hasta' => ['id' => 7, 'ad' => 'Ayse', 'soyad' => 'Yilmaz'],
            'hizmet' => ['id' => 2, 'ad' => 'Bireysel Terapi'],
            'randevu' => null,
            // JSON dizisi -> PHP dizisi (Collection DEGIL)
            'kalemler' => [
                ['id' => 11, 'tutar' => 500, 'odeme_yontemi' => 'nakit', 'tarih' => '2026-08-10 09:00:00', 'aciklama' => 'Ilk taksit'],
                ['id' => 12, 'tutar' => 400, 'odeme_yontemi' => 'havale', 'tarih' => '2026-08-18 09:00:00', 'aciklama' => 'Ikinci taksit'],
            ],
        ];

        $html = view('panel.finans.gelirler', [
            'odemeler' => ApiData::paginate([$kayit], ['current_page' => 1, 'total' => 1, 'last_page' => 1]),
            'gelirKategorileri' => ApiData::collection([$this->apiKategori()]),
            'hastalar' => ApiData::collection([]),
            'hizmetler' => ApiData::collection([]),
        ])->render();

        $this->assertStringContainsString('2 kayıt', $html, 'Odeme kalemi sayisi basilmadi.');
    }

    // ------------------------------------------------------------ giderler

    public function test_giderler_gorunumu_cizilir(): void
    {
        $html = view('panel.finans.giderler', [
            'giderler' => $this->giderler(),
            'giderKategorileri' => ApiData::collection([
                ['id' => 4, 'ad' => 'Ofis Gideri', 'renk' => '#EF4444', 'tur' => 'gider'],
            ]),
        ])->render();

        $this->assertStringContainsString('Kira', $html);
        $this->assertStringContainsString('Ofis Gideri', $html, 'Kategori adi basilmadi.');
    }

    public function test_giderler_kategorisiz_kayitla_cizilir(): void
    {
        $html = view('panel.finans.giderler', [
            'giderler' => $this->giderler(kategoriVar: false),
            'giderKategorileri' => ApiData::collection([]),
        ])->render();

        $this->assertStringContainsString('Kategorisiz', $html);
    }

    // ------------------------------------------------------- genel koruma

    /**
     * Bu sayfaların verisi ApiData ile düz stdClass'a dönüyor; orada
     * Eloquent ilişki adı (camelCase) YOKTUR. Görünümlerde camelCase
     * ilişki erişimi kalmamalı.
     *
     * @return array<string, array{0: string}>
     */
    public static function finansGorunumSaglayici(): array
    {
        return [
            'gelirler' => ['panel/finans/gelirler.blade.php'],
            'giderler' => ['panel/finans/giderler.blade.php'],
        ];
    }

    #[DataProvider('finansGorunumSaglayici')]
    public function test_gorunumde_camelcase_iliski_erisimi_yok(string $gorunum): void
    {
        $blade = (string) file_get_contents(resource_path('views/'.$gorunum));

        $this->assertStringNotContainsString(
            'finansKategori',
            $blade,
            "{$gorunum}: API verisi duz stdClass; camelCase iliski adi tanimsiz ".
            'ozellik hatasi (HTTP 500) uretir. snake_case `finans_kategori` kullanin.'
        );
    }
}
