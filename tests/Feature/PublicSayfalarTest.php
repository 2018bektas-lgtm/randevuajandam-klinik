<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Public sayfaların duman (smoke) testleri.
 *
 * Regresyon P3-4: Bu projede 176 blade dosyasına karşılık toplam 2 test vardı;
 * pratikte hiç test edilmiyordu. Oysa proje, randevuajandam-hekim'in 88 dosyalık
 * birebir kopyasını taşıyor — hekim tarafında yakalanan hatalar burada
 * sessizce kalıyordu. Nitekim delogis temasının "Hakkımda" sayfası tanımsız
 * `doctor_photo()` yüzünden HTTP 500 veriyordu ve hiçbir test bunu görmedi.
 *
 * Bu testler API'ye bağlı değildir: SiteContentService API yapılandırılmamışsa
 * demo içeriğe düşer, dolayısıyla sayfalar yine render edilmelidir.
 */
class PublicSayfalarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function publicRotaSaglayici(): array
    {
        return [
            'anasayfa' => ['frontend.anasayfa'],
            'hakkimda' => ['frontend.hakkimda'],
            'hekimler' => ['frontend.hekimler'],
            'hizmetler' => ['frontend.hizmetler'],
            'galeri' => ['frontend.galeri'],
            'blog' => ['frontend.blog'],
            'sss' => ['frontend.sss'],
            'iletisim' => ['frontend.iletisim'],
            'randevu' => ['frontend.randevu'],
        ];
    }

    #[DataProvider('publicRotaSaglayici')]
    public function test_public_sayfa_acilir(string $rota): void
    {
        $response = $this->get(route($rota));

        $this->assertContains(
            $response->status(),
            [200, 302, 404],
            "{$rota}: beklenmeyen HTTP {$response->status()} (500 = kirik sayfa)."
        );
        $this->assertNotSame(500, $response->status(), "{$rota}: sunucu hatasi.");
    }

    public function test_panel_girisi_acilir(): void
    {
        $response = $this->get(route('panel.giris'));

        $response->assertStatus(200);
    }

    public function test_sitemap_uretilir(): void
    {
        $response = $this->get(route('frontend.sitemap'));

        $this->assertNotSame(500, $response->status());
    }

    public function test_olmayan_sayfa_500_vermez(): void
    {
        $response = $this->get('/boyle-bir-sayfa-yok-12345');

        $this->assertNotSame(500, $response->status());
    }

    /**
     * Panel oturum açmadan erişilememeli.
     */
    public function test_panel_oturumsuz_erisilemez(): void
    {
        $response = $this->get('/yonetim');

        $this->assertContains($response->status(), [302, 401, 403, 404]);
        $this->assertNotSame(200, $response->status(), 'Panel oturumsuz acildi.');
    }
}
