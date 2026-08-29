<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Erişilebilirlik: skip-link, görünür odak halkası ve alt metinleri.
 *
 * Regresyon P2-15: Projede hiç "içeriğe atla" bağlantısı yoktu (WCAG 2.4.1)
 * ve `:focus-visible` yalnızca 3 dosyada tanımlıydı (WCAG 2.4.7) — klavyeyle
 * gezen kullanıcı her sayfada tüm menüyü geçmek zorunda kalıyor ve odağın
 * nerede olduğunu göremiyordu.
 *
 * Regresyon P2-14: Anlam taşıyan görsellerde (hekim fotoğrafı, logo, blog
 * kapağı, hizmet görseli) `alt=""` kullanılıyordu. Not: DEKORATİF görsellerde
 * (`shapes/`, `icon/`) `alt=""` doğru kullanımdır ve bilerek korunmuştur.
 */
class ErisilebilirlikTest extends TestCase
{
    /**
     * @return array<int, string>
     */
    private function temaLayoutlari(): array
    {
        return glob(resource_path('views/frontend/themes/*/layouts/app.blade.php')) ?: [];
    }

    public function test_tum_tema_layoutlari_skip_link_iceriyor(): void
    {
        $layoutlar = $this->temaLayoutlari();
        $this->assertNotEmpty($layoutlar, 'Tema layout dosyasi bulunamadi.');

        foreach ($layoutlar as $yol) {
            $icerik = (string) file_get_contents($yol);
            $this->assertStringContainsString(
                "@include('frontend.partials.erisilebilirlik')",
                $icerik,
                basename(dirname(dirname($yol))).': skip-link partial eklenmemis.'
            );
        }
    }

    public function test_tum_tema_layoutlari_ana_icerik_hedefine_sahip(): void
    {
        foreach ($this->temaLayoutlari() as $yol) {
            $icerik = (string) file_get_contents($yol);
            $this->assertStringContainsString(
                'id="ana-icerik"',
                $icerik,
                basename(dirname(dirname($yol))).': skip-link hedefi (#ana-icerik) yok.'
            );
        }
    }

    public function test_erisilebilirlik_partiali_odak_stili_tanimlar(): void
    {
        $yol = resource_path('views/frontend/partials/erisilebilirlik.blade.php');
        $this->assertFileExists($yol);

        $icerik = (string) file_get_contents($yol);
        $this->assertStringContainsString('.ra-skip-link', $icerik);
        $this->assertStringContainsString(':focus-visible', $icerik);
        // Skip-link yalnizca odaklaninca gorunmeli
        $this->assertStringContainsString('left: -9999px', $icerik);
    }

    /**
     * Layout'lardaki <body> etiketi bozulmamis olmali.
     *
     * Regresyon: skip-link'i ekleyen betik, `{{ request()->routeIs(...) }}`
     * ifadesindeki `->` operatorunun `>` karakterinde etiketi erken kesip
     * include'u <body> niteliklerinin ORTASINA yazmisti.
     */
    public function test_body_etiketleri_bozulmamis(): void
    {
        foreach ($this->temaLayoutlari() as $yol) {
            $icerik = (string) file_get_contents($yol);

            $this->assertSame(
                1,
                preg_match('/<body\b.*?>/s', $icerik, $m),
                basename(dirname(dirname($yol))).': <body> etiketi bulunamadi.'
            );
            $this->assertStringNotContainsString(
                '@include',
                $m[0],
                basename(dirname(dirname($yol))).': <body> etiketinin ICINE include yazilmis.'
            );
        }
    }


    /**
     * P3-6: Hareket azaltma tercihi tum animasyonlari etkisiz kilmali ve
     * animasyonla gorunur hale gelen ogeleri gizli birakmamali.
     */
    public function test_hareket_azaltma_tercihi_destekleniyor(): void
    {
        $icerik = (string) file_get_contents(
            resource_path('views/frontend/partials/erisilebilirlik.blade.php')
        );

        $this->assertStringContainsString('prefers-reduced-motion', $icerik);
        $this->assertStringContainsString('animation-duration: 0.001ms !important', $icerik);
        // Gizli kalma riski olan ogeler acikca gorunur yapilmali
        foreach (['.wow', '.reveal', '.image-anime'] as $secici) {
            $this->assertStringContainsString($secici, $icerik, "{$secici} hareket azaltmada gorunur yapilmamis.");
        }
    }

    /**
     * P3-7: Ozel imlec dokunmatik / kaba isaretci cihazlarda devre disi.
     */
    public function test_ozel_imlec_dokunmatikte_kapali(): void
    {
        $icerik = (string) file_get_contents(
            resource_path('views/frontend/partials/erisilebilirlik.blade.php')
        );

        $this->assertStringContainsString('(hover: none), (pointer: coarse)', $icerik);
        $this->assertStringContainsString('.custom-cursor__cursor', $icerik);
    }

    /**
     * Anlam tasiyan gorsellerde bos alt kalmamali.
     * Dekoratif olanlar (shapes/icon/star/quote) kapsam disi.
     */
    public function test_anlamli_gorsellerde_bos_alt_yok(): void
    {
        $dekoratif = '/(shapes\/|\/icon\/|icon-|star|quote|curve|bg-shape|dots|pattern|customer-img)/i';
        $bulunan = [];

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views/frontend/themes'))
        );
        foreach ($it as $dosya) {
            if (! $dosya->isFile() || ! str_ends_with((string) $dosya, '.blade.php')) {
                continue;
            }
            foreach (explode("\n", (string) file_get_contents((string) $dosya)) as $no => $satir) {
                if (! str_contains($satir, 'alt=""') || ! str_contains($satir, '<img')) {
                    continue;
                }
                // Dekoratif oldugu MARKUP'TA belirtilmis (tercih edilen yol)
                if (str_contains($satir, 'aria-hidden="true"')) {
                    continue;
                }
                // Ya da dosya adindan dekoratif oldugu anlasiliyor
                if (preg_match($dekoratif, $satir)) {
                    continue;   // dekoratif: alt="" dogru kullanim
                }
                $bulunan[] = basename((string) $dosya).':'.($no + 1);
            }
        }

        $this->assertSame(
            [],
            $bulunan,
            'Anlam tasiyan gorselde bos alt: '.implode(', ', $bulunan)
        );
    }
}
