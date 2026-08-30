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

        // Serit kalinligi/degisken adi degisebilir; onemli olan sira.
        $bulundu = preg_match(
            "~info\.el\.style\.borderLeft = '\d+px solid ' \+ \w+;~",
            $blade,
            $m,
            PREG_OFFSET_CAPTURE
        );

        $this->assertSame(1, $bulundu, 'Durum rengi serit atamasi bulunamadi.');
        $serit = $m[0][1];
        $this->assertLessThan(
            $serit,
            $sifirla,
            'border sifirlamasi seritten SONRA geliyor; serit siliniyor.'
        );
    }

    /**
     * Dört durumun zemin rengi.
     *
     * Bildirilen sorun: "daha belirgin olsun, böyle olmaz, okunmuyor bile
     * açık renklerle randevular."
     *
     * Ölçüm, sorunun metin kontrastı OLMADIĞINI gösterdi: eski soluk
     * zeminde koyu metnin oranı zaten 7.26:1'di. Asıl sorun blokların
     * BEYAZ takvim ızgarasından ayrışmamasıydı — randevu ile boş saat
     * ayırt edilemiyor, dört durum aynı açıklıkta görünüyordu.
     *
     * Çözüm dolgun zemin + beyaz metin. Tonlar, beyazla kontrastı WCAG AA
     * eşiğini (4.5:1) geçecek şekilde seçildi.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function durumRenkSaglayici(): array
    {
        return [
            'beklemede' => ['beklemede', '#A85520'],
            'onaylandi' => ['onaylandi', '#047857'],
            'tamamlandi' => ['tamamlandi', '#1D4ED8'],
            'iptal' => ['iptal', '#B91C1C'],
        ];
    }

    /** WCAG 2.1 bağıl parlaklık. */
    private function parlaklik(string $hex): float
    {
        $hex = ltrim($hex, '#');
        $kanal = [];

        foreach ([0, 2, 4] as $i) {
            $x = hexdec(substr($hex, $i, 2)) / 255;
            $kanal[] = $x <= 0.03928 ? $x / 12.92 : (($x + 0.055) / 1.055) ** 2.4;
        }

        return 0.2126 * $kanal[0] + 0.7152 * $kanal[1] + 0.0722 * $kanal[2];
    }

    private function kontrast(string $a, string $b): float
    {
        $la = $this->parlaklik($a);
        $lb = $this->parlaklik($b);

        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }

    /**
     * Beyaz metin her durum zemininde okunabilir olmalı (WCAG AA, 4.5:1).
     *
     * Renkler elle "koyu gibi duruyor" diye seçilmesin; eşik ölçülsün.
     */
    #[DataProvider('durumRenkSaglayici')]
    public function test_beyaz_metin_kontrasti_yeterli(string $durum, string $zemin): void
    {
        $oran = $this->kontrast('#FFFFFF', $zemin);

        $this->assertGreaterThanOrEqual(
            4.5,
            $oran,
            sprintf(
                "'%s' zemini (%s) uzerinde beyaz metin okunmuyor: %.2f:1 (AA esigi 4.5:1).",
                $durum, $zemin, $oran
            )
        );
    }

    /**
     * Bloklar beyaz takvim ızgarasından da ayrışmalı; asıl şikâyet buydu.
     */
    #[DataProvider('durumRenkSaglayici')]
    public function test_zemin_takvim_izgarasindan_ayrisir(string $durum, string $zemin): void
    {
        $oran = $this->kontrast('#FFFFFF', $zemin);

        $this->assertGreaterThan(
            3.0,
            $oran,
            sprintf(
                "'%s' blogu beyaz izgaradan ayrismiyor (%.2f:1); randevu ile bos saat karisir.",
                $durum, $oran
            )
        );
    }

    /**
     * Metin beyaz atanmalı; soluk zemin dönemindeki koyu metin renkleri
     * geri gelmemeli.
     */
    public function test_metin_beyaz_atanir(): void
    {
        $blade = $this->takvimBlade();

        $this->assertStringContainsString(
            "info.el.style.color = '#FFFFFF';",
            $blade,
            'Randevu blogunda metin beyaza ayarlanmiyor.'
        );
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
     * Sayfa başındaki "Takvim Rehberi" kutucukları, takvimde çizilen blok
     * renkleriyle birebir aynı olmalı; yoksa rehber yanlış bilgi verir.
     */
    #[DataProvider('durumRenkSaglayici')]
    public function test_rehber_kutulari_blok_rengiyle_ayni(string $durum, string $zemin): void
    {
        $blade = $this->takvimBlade();

        $this->assertStringContainsString(
            'background:'.$zemin,
            $blade,
            "Takvim Rehberi'nde '{$durum}' kutusu blok rengiyle ({$zemin}) eslesmiyor."
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
