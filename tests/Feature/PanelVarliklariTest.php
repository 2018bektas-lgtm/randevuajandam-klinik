<?php

namespace Tests\Feature;

use Illuminate\Foundation\Vite;
use Tests\TestCase;

/**
 * Panel varliklari Vite ile derlenmeli, Tailwind Play CDN kullanilmamali.
 *
 * Regresyon: panel `https://cdn.tailwindcss.com` yukluyordu. Play CDN uretim
 * icin tasarlanmamistir: CSS her sayfa acilisinda tarayicida derlenir ve CDN
 * engellenirse panel tamamen stilsiz kalir — mobil menu siniflari
 * (`md:hidden`, `-translate-x-full`) da uretilmedigi icin sidebar ekrani
 * kaplar ve panel kullanilamaz hale gelir.
 */
class PanelVarliklariTest extends TestCase
{
    /**
     * Tailwind utility sinifi kullanan, kendi basina tam HTML dokumani olan
     * panel blade'leri. Kendi el yazimi CSS'ini kullanan sayfalar (or. hekim
     * `panel/auth/giris.blade.php`) Tailwind yuklemek zorunda degildir.
     *
     * @return array<int, string>
     */
    private function tailwindKullananPanelBladeleri(): array
    {
        $adaylar = [
            resource_path('views/panel/layouts/app.blade.php'),
            resource_path('views/panel/auth/giris.blade.php'),
            resource_path('views/panel/auth/two_factor_challenge.blade.php'),
        ];

        $sonuc = [];
        foreach ($adaylar as $yol) {
            if (! is_file($yol)) {
                continue;
            }
            $icerik = (string) file_get_contents($yol);

            $tamDokuman = str_contains($icerik, '<!DOCTYPE');
            $tailwindKullaniyor = (bool) preg_match(
                '/class="[^"]*(?:md:|bg-brand-|text-ink|font-display|rounded-3xl)/',
                $icerik
            );

            if ($tamDokuman && $tailwindKullaniyor) {
                $sonuc[] = $yol;
            }
        }

        return $sonuc;
    }

    public function test_hicbir_blade_tailwind_play_cdn_kullanmiyor(): void
    {
        $bulunan = [];

        $dizin = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );
        foreach ($dizin as $dosya) {
            if (! $dosya->isFile() || ! str_ends_with((string) $dosya, '.blade.php')) {
                continue;
            }
            if (str_contains((string) file_get_contents((string) $dosya), 'cdn.tailwindcss.com')) {
                $bulunan[] = (string) $dosya;
            }
        }

        $this->assertSame([], $bulunan, 'Tailwind Play CDN hala kullaniliyor: '.implode(', ', $bulunan));
    }

    public function test_tailwind_kullanan_panel_sayfalari_vite_ile_yukluyor(): void
    {
        $bladeler = $this->tailwindKullananPanelBladeleri();
        $this->assertNotEmpty($bladeler, 'Tailwind kullanan panel blade dosyasi bulunamadi.');

        foreach ($bladeler as $blade) {
            $this->assertStringContainsString(
                "@vite('resources/css/panel.css')",
                (string) file_get_contents($blade),
                basename($blade).' Vite ile stil yuklemiyor.'
            );
        }
    }

    public function test_derlenmis_panel_css_manifestte_var(): void
    {
        $manifest = public_path('build/manifest.json');
        $this->assertFileExists($manifest, 'public/build/manifest.json yok — `npm run build` calistirilmali.');

        $veri = json_decode((string) file_get_contents($manifest), true);
        $this->assertArrayHasKey('resources/css/panel.css', $veri ?? [], 'panel.css manifestte yok.');

        $dosya = public_path('build/'.$veri['resources/css/panel.css']['file']);
        $this->assertFileExists($dosya, 'Derlenmis panel CSS dosyasi bulunamadi.');
    }

    public function test_vite_direktifi_manifesti_cozuyor(): void
    {
        $html = app(Vite::class)('resources/css/panel.css')->toHtml();

        $this->assertStringContainsString('/build/assets/panel-', $html);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
    }

    public function test_derlenmis_css_panelin_ihtiyaci_olan_siniflari_iceriyor(): void
    {
        $manifest = json_decode((string) file_get_contents(public_path('build/manifest.json')), true);
        $css = (string) file_get_contents(public_path('build/'.$manifest['resources/css/panel.css']['file']));

        $gerekli = [
            // Marka renkleri (eski satir ici tailwind.config karsiligi)
            '.bg-brand-500',
            '.text-brand-600',
            // brand-200 kullanimda oldugu halde eski config'de TANIMSIZDI
            '.border-brand-200',
            '.text-ink',
            '.font-display',
            // Mobil sidebar: CDN engellenince bunlar uretilmiyordu
            '.md\\:hidden',
            '.-translate-x-full',
            '.md\\:translate-x-0',
        ];

        foreach ($gerekli as $sinif) {
            $this->assertStringContainsString(
                $sinif,
                $css,
                "Derlenmis panel CSS icinde eksik: {$sinif}"
            );
        }
    }
}
