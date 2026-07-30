<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SiteContentService;
use App\Services\SiteSettingsService;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function __construct(
        protected SiteContentService $content,
        protected SiteSettingsService $settings
    ) {}

    protected function doktor(): array
    {
        return $this->content->klinik();
    }

    public function yasalKvkk(): View
    {
        return $this->yasalSayfa(
            'KVKK Aydınlatma Metni',
            'yasal_kvkk',
            'Bu sitede randevu ve iletişim formları aracılığıyla paylaştığınız kimlik ve iletişim verileri, randevu süreçlerinin yürütülmesi amacıyla işlenir. Veri sorumlusu bu web sitesinin sahibi kliniktir. Detaylı metin için klinik paneli → Site Ayarları → Yasal bölümünden metninizi kaydediniz.'
        );
    }

    public function yasalGizlilik(): View
    {
        return $this->yasalSayfa(
            'Gizlilik Politikası',
            'yasal_gizlilik',
            'Bu web sitesinde toplanan bilgiler randevu ve iletişim amaçlarıyla kullanılır; üçüncü taraflara satılmaz. Çerez ve teknik loglar site güvenliği için tutulabilir. Güncel politika için klinik paneli → Site Ayarları → Yasal bölümünden metninizi kaydediniz.'
        );
    }

    public function yasalKullanim(): View
    {
        return $this->yasalSayfa(
            'Kullanım Koşulları',
            'yasal_kullanim',
            'Bu web sitesi bilgilendirme ve randevu talebi amaçlıdır. Tıbbi acil durumlar için 112 arayınız. Site içeriği bilgilendirme niteliğindedir. Güncel koşullar için klinik paneli → Site Ayarları → Yasal bölümünden metninizi kaydediniz.'
        );
    }

    protected function yasalSayfa(string $baslik, string $optionKey, string $varsayilan): View
    {
        $doktor = $this->doktor();
        $raw = trim((string) $this->settings->option($optionKey, ''));
        $icerik = $raw !== '' ? $raw : $varsayilan;

        return $this->themePage('yasal', [
            'doktor' => $doktor,
            'baslik' => $baslik,
            'icerik' => $icerik,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function themePage(string $page, array $data = []): View
    {
        $doktor = $data['doktor'] ?? $this->doktor();
        $data['doktor'] = $doktor;

        return theme_view('pages.'.$page, $data, current_theme_id($doktor));
    }

    public function anasayfa(): View
    {
        return $this->themePage('anasayfa');
    }

    public function hakkimda(): View
    {
        return $this->themePage('hakkimda');
    }

    public function hekimler(): View
    {
        return $this->themePage('hekimler');
    }

    public function hekimDetay(string $slug): View
    {
        $doktor = $this->doktor();
        $hekim = collect($doktor['hekimler'] ?? [])->first(function ($h) use ($slug) {
            return ($h['slug'] ?? '') === $slug || (string) ($h['id'] ?? '') === $slug;
        });
        abort_if(! $hekim, 404);

        return $this->themePage('hekim-detay', [
            'doktor' => $doktor,
            'hekim' => $hekim,
        ]);
    }

    public function hizmetler(): View
    {
        return $this->themePage('hizmetler');
    }

    public function hizmetDetay(string $slug): View
    {
        $doktor = $this->doktor();
        $hizmet = collect($doktor['hizmetler'] ?? [])->first(function ($h) use ($slug) {
            $hSlug = $h['slug'] ?? Str::slug($h['baslik'] ?? '');

            return $hSlug === $slug || (string) ($h['id'] ?? '') === $slug;
        });
        abort_if(! $hizmet, 404);

        return $this->themePage('hizmet-detay', [
            'doktor' => $doktor,
            'hizmet' => $hizmet,
        ]);
    }

    public function galeri(): View
    {
        return $this->themePage('galeri');
    }

    public function blog(): View
    {
        return $this->themePage('blog');
    }

    public function blogDetay(string $slug): View
    {
        $doktor = $this->doktor();
        $yazi = collect($doktor['bloglar'] ?? [])->firstWhere('slug', $slug);
        abort_if(! $yazi, 404);

        return $this->themePage('blog-detay', [
            'doktor' => $doktor,
            'yazi' => $yazi,
        ]);
    }

    public function sss(): View
    {
        return $this->themePage('sss');
    }

    public function iletisim(): View
    {
        return $this->themePage('iletisim');
    }

    public function sitemap(): Response
    {
        $doktor = $this->doktor();
        $urls = [
            ['loc' => route('frontend.anasayfa'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => route('frontend.hakkimda'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('frontend.hekimler'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => route('frontend.hizmetler'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => route('frontend.galeri'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => route('frontend.blog'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('frontend.sss'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => route('frontend.iletisim'), 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => route('frontend.randevu'), 'priority' => '0.9', 'changefreq' => 'monthly'],
        ];

        foreach ($doktor['hekimler'] ?? [] as $h) {
            $slug = $h['slug'] ?? '';
            if ($slug !== '') {
                $urls[] = ['loc' => route('frontend.hekim.detay', $slug), 'priority' => '0.8', 'changefreq' => 'monthly'];
            }
        }
        foreach ($doktor['hizmetler'] ?? [] as $h) {
            $slug = $h['slug'] ?? Str::slug($h['baslik'] ?? '');
            if ($slug !== '') {
                $urls[] = ['loc' => route('frontend.hizmet.detay', $slug), 'priority' => '0.7', 'changefreq' => 'monthly'];
            }
        }
        foreach ($doktor['bloglar'] ?? [] as $b) {
            if (! empty($b['slug'])) {
                $urls[] = ['loc' => route('frontend.blog.detay', $b['slug']), 'priority' => '0.7', 'changefreq' => 'weekly'];
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= '  <url><loc>'.e($u['loc']).'</loc><changefreq>'.$u['changefreq'].'</changefreq><priority>'.$u['priority'].'</priority></url>'."\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
