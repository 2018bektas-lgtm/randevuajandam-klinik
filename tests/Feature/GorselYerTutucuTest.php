<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Görsel yer tutucuları ve JS'siz görünürlük güvenlik ağı.
 *
 * Regresyon 1 (P1-11): Fotoğrafı olmayan hekim için Unsplash'ten çekilen bir
 * portre gösteriliyordu — yani YABANCI BİR İNSANIN YÜZÜ hekim fotoğrafı olarak
 * basılıyordu. Ayrıca her ziyaretçinin IP'si üçüncü tarafa gidiyordu.
 *
 * Regresyon 2 (P1-12): Tema CSS'inde `.reveal { visibility: hidden }` var ve
 * görünürlüğü yalnızca GSAP açıyor; JS çalışmazsa görseller kalıcı görünmez
 * kalıyordu.
 */
class GorselYerTutucuTest extends TestCase
{
    public function test_kodda_unsplash_referansi_kalmadi(): void
    {
        $bulunan = [];

        foreach ([app_path(), config_path(), resource_path('views')] as $kok) {
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($kok));
            foreach ($it as $dosya) {
                if (! $dosya->isFile() || ! str_ends_with((string) $dosya, '.php')) {
                    continue;
                }
                if (str_contains((string) file_get_contents((string) $dosya), 'images.unsplash.com')) {
                    $bulunan[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', (string) $dosya);
                }
            }
        }

        $this->assertSame([], $bulunan, 'Unsplash referansi kalmis: '.implode(', ', $bulunan));
    }

    public function test_avatar_yerel_ve_isim_bas_harflerini_kullanir(): void
    {
        $avatar = avatar_placeholder('Uzm. Psk. Ayşe Yılmaz');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $avatar);
        $this->assertStringNotContainsString('http', $avatar);

        $svg = base64_decode(substr($avatar, strlen('data:image/svg+xml;base64,')));
        $this->assertStringContainsString('<svg', $svg);
        // "Uzm." + "Psk." -> UP (ilk iki kelimenin bas harfi)
        $this->assertStringContainsString('>UP<', $svg);
    }

    public function test_avatar_bos_isimde_de_calisir(): void
    {
        foreach ([null, '', '   '] as $girdi) {
            $avatar = avatar_placeholder($girdi);
            $this->assertStringStartsWith('data:image/svg+xml;base64,', $avatar);
            $svg = base64_decode(substr($avatar, strlen('data:image/svg+xml;base64,')));
            $this->assertStringContainsString('<svg', $svg);
        }
    }

    public function test_doctor_photo_fotograf_yoksa_yerel_avatara_duser(): void
    {
        $url = doctor_photo(['ad_soyad' => 'Ayşe Yılmaz', 'unvan' => 'Uzm. Psk.', 'profil_resmi' => null]);

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $url);
        $this->assertStringNotContainsString('unsplash', $url);
    }

    public function test_dekoratif_yer_tutucu_yerel_dosya(): void
    {
        $url = image_placeholder();

        $this->assertStringContainsString('/images/placeholder.svg', $url);
        $this->assertFileExists(public_path('images/placeholder.svg'));
    }

    public function test_config_dosyalari_asset_cagirmaz(): void
    {
        // config/*.php bootstrap sirasinda yuklenir; asset()/route() cagirmak
        // uygulamayi acilista dusurur ve config:cache degeri sabitler.
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(config_path()));
        foreach ($it as $dosya) {
            if (! $dosya->isFile() || ! str_ends_with((string) $dosya, '.php')) {
                continue;
            }
            $icerik = (string) file_get_contents((string) $dosya);
            $this->assertDoesNotMatchRegularExpression(
                '/(?<![\w>$])(asset|route|url)\s*\(/',
                $icerik,
                basename((string) $dosya).' config dosyasinda asset()/route()/url() cagriliyor.'
            );
        }
    }

    /**
     * doctor_photo() delogis temasinda kullaniliyor ama bu projede TANIMLI
     * DEGILDI; pages/hakkimda "Call to undefined function doctor_photo()" ile
     * HTTP 500 veriyordu.
     */
    public function test_doctor_photo_bu_projede_tanimli(): void
    {
        $this->assertTrue(function_exists('doctor_photo'));
    }

    public function test_delogis_hakkimda_sayfasi_render_oluyor(): void
    {
        $hekim = [
            'id' => 1, 'ad_soyad' => 'Mehmet Demir', 'unvan' => 'Dr.', 'slug' => 'mehmet-demir',
            'profil_resmi' => null, 'uzmanlik' => 'Kardiyoloji', 'uzmanlik_alani' => 'Kardiyoloji',
            'bio' => 'Kisa', 'kisa_bio' => 'Kisa', 'branslar' => [], 'hizmetler' => [],
        ];
        $doktor = array_merge($hekim, [
            'tema_id' => 'delogis', 'klinik_adi' => 'Ornek Klinik', 'hekimler' => [$hekim],
            'bloglar' => [], 'galeri' => [], 'slider' => [], 'istatistikler' => [],
            'sss' => [], 'yorumlar' => [],
        ]);

        $html = view('frontend.themes.delogis.pages.hakkimda', [
            'doktor' => $doktor, 'klinik' => $doktor, 'hekim' => $hekim,
        ])->render();

        $this->assertStringNotContainsString('images.unsplash.com', $html);
    }
}
