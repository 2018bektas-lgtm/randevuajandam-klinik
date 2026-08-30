<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Finans sayfalarındaki modallar çalışır durumda olmalı.
 *
 * Bildirilen sorun: "/yonetim/finans/gelirler — modallar kapanmıyor,
 * input ve select tasarımları güzel değil."
 *
 * Bu sayfalar randevuajandam-site projesinden kopyalanmıştı. Orada
 * jQuery + select2 + flatpickr yükleniyor; PANELDE HİÇBİRİ YÜKLENMİYOR.
 * Tek kök neden iki şikâyeti birden üretiyordu:
 *
 *  1) closeModal() ilk iş olarak destroyModalSelect2() çağırıyordu; o da
 *     `$` (jQuery) kullanıyor. jQuery tanımsız olduğu için ReferenceError
 *     fırlıyor ve fonksiyon `classList.add('hidden')` satırına HİÇ
 *     ulaşamıyordu — modal kapanmıyordu.
 *
 *  2) `select2-*` sınıfları hiçbir biçim almıyordu; select'ler
 *     yanlarındaki Tailwind'li input'larla uyumsuz görünüyordu.
 *
 * Üçüncü bir hata da aynı kopyalamadan geliyordu: form adresleri
 * `/hekim/finans/...` idi, oysa bu panelin öneki `/yonetim`. Yani
 * "Düzenle" ve "Ödeme Ekle" 404'e gidiyordu.
 *
 * Çözüm: jQuery/select2 bağımlılığı kaldırıldı (yerel <select> +
 * public/css/panel-form.css), adresler route() ile üretiliyor.
 */
class FinansModalTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function sayfaSaglayici(): array
    {
        return [
            'gelirler' => ['panel/finans/gelirler.blade.php'],
            'giderler' => ['panel/finans/giderler.blade.php'],
            'kategoriler' => ['panel/finans/kategoriler.blade.php'],
        ];
    }

    private function icerik(string $gorunum): string
    {
        return (string) file_get_contents(resource_path('views/'.$gorunum));
    }

    /**
     * Yorumları ayıklanmış içerik.
     *
     * Bu dosyalarda kaldırılan hataların NEDENİ yorum olarak yazılı; ham
     * metinde arama yapmak kendi açıklamalarımızı "hata" sayar. Bu yüzden
     * yasak kalıplar yalnızca GERÇEK kodda aranıyor.
     */
    private function kod(string $gorunum): string
    {
        $blade = $this->icerik($gorunum);

        $blade = preg_replace('~\{\{--.*?--\}\}~s', '', $blade);   // Blade yorumu
        $blade = preg_replace('~/\*.*?\*/~s', '', $blade);         // /* ... */
        $blade = preg_replace('~^\s*//.*$~m', '', $blade);         // // satiri

        return (string) $blade;
    }

    /**
     * ASIL KORUMA: panelde jQuery yok; bu sayfalarda `$(...)` kullanılamaz.
     */
    #[DataProvider('sayfaSaglayici')]
    public function test_jquery_kullanilmaz(string $gorunum): void
    {
        $blade = $this->kod($gorunum);

        $this->assertDoesNotMatchRegularExpression(
            '~(?<![\w$])\$\(~',
            $blade,
            "{$gorunum}: jQuery cagrisi var ama panelde jQuery YUKLENMIYOR; ".
            'ReferenceError modal kapanmasini engeller.'
        );
    }

    /**
     * select2 hiç yüklenmiyor; onu çağıran kod kalmamalı.
     */
    #[DataProvider('sayfaSaglayici')]
    public function test_select2_cagrilmaz(string $gorunum): void
    {
        $blade = $this->kod($gorunum);

        foreach (['select2(', 'initModalSelect2', 'destroyModalSelect2'] as $parca) {
            $this->assertStringNotContainsString(
                $parca,
                $blade,
                "{$gorunum}: '{$parca}' kaldi; select2 panelde yuklenmiyor."
            );
        }
    }

    /**
     * Form adresleri elle yazılmamalı: `/hekim/...` bu panelde 404'tür.
     */
    #[DataProvider('sayfaSaglayici')]
    public function test_hekim_onekli_adres_kalmadi(string $gorunum): void
    {
        $blade = $this->kod($gorunum);

        $this->assertStringNotContainsString(
            '/hekim/finans',
            $blade,
            "{$gorunum}: site projesinin onegi kalmis; bu panelde onek /yonetim."
        );
    }

    /**
     * Projede tanımlı olmayan yardımcı çağrılmamalı.
     */
    #[DataProvider('sayfaSaglayici')]
    public function test_tanimsiz_yardimci_cagrilmaz(string $gorunum): void
    {
        $blade = $this->kod($gorunum);

        $this->assertDoesNotMatchRegularExpression(
            '~(?<!\*\s)mesajModalAc\(~',
            $blade,
            "{$gorunum}: mesajModalAc() proje genelinde TANIMSIZ."
        );
    }

    /**
     * Kapatma yolları: buton, Esc ve arka plan tıklaması.
     */
    #[DataProvider('sayfaSaglayici')]
    public function test_kapatma_yollari_var(string $gorunum): void
    {
        $blade = $this->icerik($gorunum);

        $this->assertStringContainsString(
            'window.closeModal',
            $blade,
            "{$gorunum}: closeModal disari acilmamis."
        );
        $this->assertStringContainsString(
            "e.key === 'Escape'",
            $blade,
            "{$gorunum}: Esc ile kapatma yok."
        );
        $this->assertStringContainsString(
            'ra-backdrop',
            $blade,
            "{$gorunum}: arka plan tiklamasiyla kapatma isaretlenmemis."
        );
    }

    /**
     * Modal açıkken arka plan kaymamalı.
     */
    #[DataProvider('sayfaSaglayici')]
    public function test_arka_plan_kaydirmasi_kilitlenir(string $gorunum): void
    {
        $blade = $this->icerik($gorunum);

        $this->assertStringContainsString(
            'ra-modal-acik',
            $blade,
            "{$gorunum}: modal acikken sayfa kaydirmasi kilitlenmiyor."
        );
    }

    /**
     * Select biçimlendirmesi panel düzeninden yükleniyor olmalı; aksi
     * halde select'ler yine biçimsiz kalır.
     */
    public function test_form_stili_panel_duzeninde_yuklu(): void
    {
        $duzen = (string) file_get_contents(
            resource_path('views/panel/layouts/app.blade.php')
        );

        $this->assertStringContainsString(
            'css/panel-form.css',
            $duzen,
            'panel-form.css panel duzeninde yuklenmiyor; selectler bicimsiz kalir.'
        );

        $this->assertFileExists(
            public_path('css/panel-form.css'),
            'panel-form.css dosyasi yok.'
        );
    }

    /**
     * Stil dosyası select sınıflarını gerçekten hedeflemeli.
     */
    public function test_form_stili_select_siniflarini_hedefler(): void
    {
        $css = (string) file_get_contents(public_path('css/panel-form.css'));

        foreach (['select2-filter', 'select2-modal', 'select2-hasta-filter'] as $sinif) {
            $this->assertStringContainsString(
                $sinif,
                $css,
                "panel-form.css '{$sinif}' sinifini bicimlendirmiyor."
            );
        }

        $this->assertStringContainsString(
            'ra-modal-acik',
            $css,
            'Modal acikken kaydirma kilidi CSS tarafinda tanimli degil.'
        );
    }
}
