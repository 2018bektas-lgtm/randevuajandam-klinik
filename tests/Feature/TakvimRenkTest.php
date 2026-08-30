<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Panel takvimi: randevu durumları renkleriyle ayırt edilebilmeli.
 *
 * Bildirilen sorun: "açık yeşil ya, randevular takvim sayfasında diğer
 * renkler görünmüyor."
 *
 * Kök neden CSS öncelik sırasıydı. Stil sayfasında
 *
 *     .fc-event { ... border: none !important; ... }
 *
 * vardı. eventDidMount ise durum rengini SATIR İÇİ atıyor:
 *
 *     info.el.style.borderLeft = '4px solid ' + border;
 *
 * Stil sayfasındaki `!important`, satır içi bildirimden önceliklidir; bu
 * yüzden renkli sol şerit hiç çizilmiyordu. Geriye yalnızca %9
 * saydamlıktaki çok soluk zemin kalıyor ve dört durum birbirinden ayırt
 * edilemiyordu. Aynı kural öğle arası / geçmiş / izin bloklarının
 * şeritlerini de siliyordu.
 *
 * randevuajandam-site aynı takvimi çiziyor ve orada `.fc-event` kuralında
 * `border` bildirimi yok — iki proje bu yüzden farklı görünüyordu.
 */
class TakvimRenkTest extends TestCase
{
    private function takvimBlade(): string
    {
        return (string) file_get_contents(
            resource_path('views/panel/randevu/takvim.blade.php')
        );
    }

    /**
     * Blade'deki `.fc-event { ... }` kuralının gövdesini döndürür.
     */
    private function fcEventKurali(): string
    {
        $blade = $this->takvimBlade();

        $this->assertMatchesRegularExpression(
            '~\.fc-event\s*\{~',
            $blade,
            '.fc-event kurali bulunamadi.'
        );

        preg_match('~\.fc-event\s*\{([^}]*)\}~', $blade, $m);

        return $m[1] ?? '';
    }

    /**
     * ASIL KORUMA: `.fc-event` kuralı `border` bildirimi içermemeli.
     *
     * İçerirse (özellikle `!important` ile) durum rengi şeridi ezilir.
     */
    public function test_fc_event_kurali_border_bildirimi_icermez(): void
    {
        $kural = $this->fcEventKurali();

        $this->assertDoesNotMatchRegularExpression(
            '~(^|;)\s*border\s*:~',
            $kural,
            "`.fc-event` kuralinda `border` bildirimi var; eventDidMount icinde ".
            "satir ici atanan durum rengi seridini ezer ve tum randevular ayni ".
            "renkte gorunur. Bulunan kural: {$kural}"
        );
    }

    /**
     * Satır içi şerit atanmadan önce border sıfırlanmalı (site ile aynı sıra).
     */
    public function test_serit_atanmadan_once_border_sifirlanir(): void
    {
        $blade = $this->takvimBlade();

        $this->assertStringContainsString(
            "info.el.style.border = 'none';",
            $blade,
            'Satir ici `border = none` sifirlamasi yok.'
        );

        $sifirla = strpos($blade, "info.el.style.border = 'none';");
        $serit = strpos($blade, "info.el.style.borderLeft = '4px solid ' + border;");

        $this->assertNotFalse($serit, 'Durum rengi serit atamasi bulunamadi.');
        $this->assertLessThan(
            $serit,
            $sifirla,
            'border sifirlamasi seritten SONRA geliyor; serit siliniyor.'
        );
    }

    /**
     * Dört durumun rengi de tanımlı olmalı. Renkler randevuajandam-site
     * takvimiyle aynı; iki panel arasında görsel fark olmamalı.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function durumRenkSaglayici(): array
    {
        return [
            'beklemede' => ['beklemede', '#C96A2B'],
            'onaylandi' => ['onaylandi', '#10B981'],
            'tamamlandi' => ['tamamlandi', '#3B82F6'],
            'iptal' => ['iptal', '#EF4444'],
        ];
    }

    #[DataProvider('durumRenkSaglayici')]
    public function test_her_durumun_rengi_tanimli(string $durum, string $renk): void
    {
        $blade = $this->takvimBlade();

        $this->assertStringContainsString(
            $renk,
            $blade,
            "'{$durum}' durumunun rengi ({$renk}) takvimde tanimli degil."
        );
    }

    /**
     * Durumlar birbirinden farklı renk almalı; hepsi aynı olursa takvim
     * yine ayırt edilemez olur.
     */
    public function test_durum_renkleri_birbirinden_farkli(): void
    {
        $renkler = array_map(
            static fn (array $satir): string => $satir[1],
            array_values(self::durumRenkSaglayici())
        );

        $this->assertSame(
            count($renkler),
            count(array_unique($renkler)),
            'Iki durum ayni rengi kullaniyor.'
        );
    }

    /**
     * Arka plan blokları (öğle arası / izin) da şeritlerini korumalı.
     */
    public function test_arkaplan_bloklarinin_seridi_korunur(): void
    {
        $blade = $this->takvimBlade();

        foreach (['#E7B58A', '#9CA3AF'] as $renk) {
            $this->assertStringContainsString(
                "borderLeft = '4px solid {$renk}'",
                $blade,
                "Arka plan blogu seridi yok: {$renk}"
            );
        }
    }
}
