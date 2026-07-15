<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Doktor sitesi vitrin yapısı — local SQLite.
 * Ayrı tablolar: options, menu_items, slider_slides, homepage_sections
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_options', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('site_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('label');
            $table->string('route')->default('frontend.anasayfa');
            $table->string('url')->nullable(); // custom external optional
            $table->boolean('aktif')->default(true);
            $table->unsignedInteger('sira')->default(0);
            $table->timestamps();
            $table->index('sira');
        });

        Schema::create('site_slider_slides', function (Blueprint $table) {
            $table->id();
            $table->string('baslik')->nullable();
            $table->text('alt')->nullable();
            $table->string('etiket')->nullable();
            $table->string('badge')->nullable();
            $table->string('image')->nullable();
            $table->string('thumb')->nullable();
            $table->string('cta')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('cta2')->nullable();
            $table->string('cta2_url')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('aktif')->default(true);
            $table->unsignedInteger('sira')->default(0);
            $table->timestamps();
            $table->index('sira');
        });

        Schema::create('site_homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('baslik')->nullable();
            $table->text('alt_metin')->nullable();
            $table->boolean('aktif')->default(true);
            $table->unsignedInteger('sira')->default(0);
            $table->timestamps();
            $table->index('sira');
        });

        // Seed defaults
        $now = now();
        $menus = [
            ['key' => 'anasayfa', 'label' => 'Ana Sayfa', 'route' => 'frontend.anasayfa', 'sira' => 1],
            ['key' => 'hakkimda', 'label' => 'Hakkımızda', 'route' => 'frontend.hakkimda', 'sira' => 2],
            ['key' => 'hekimler', 'label' => 'Hekimlerimiz', 'route' => 'frontend.hekimler', 'sira' => 3],
            ['key' => 'hizmetler', 'label' => 'Hizmetler', 'route' => 'frontend.hizmetler', 'sira' => 4],
            ['key' => 'galeri', 'label' => 'Galeri', 'route' => 'frontend.galeri', 'sira' => 5],
            ['key' => 'blog', 'label' => 'Blog', 'route' => 'frontend.blog', 'sira' => 6],
            ['key' => 'sss', 'label' => 'S.S.S.', 'route' => 'frontend.sss', 'sira' => 7],
            ['key' => 'iletisim', 'label' => 'İletişim', 'route' => 'frontend.iletisim', 'sira' => 8],
        ];
        foreach ($menus as $m) {
            DB::table('site_menu_items')->insert(array_merge($m, [
                'aktif' => 1,
                'url' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $sections = [
            ['key' => 'slider', 'label' => 'Hero / Slider', 'sira' => 1],
            ['key' => 'istatistik', 'label' => 'İstatistik çubuğu', 'sira' => 2],
            ['key' => 'ozellikler', 'label' => 'Özellik kartları', 'sira' => 3],
            ['key' => 'hakkimda', 'label' => 'Hakkımızda özeti', 'sira' => 4],
            ['key' => 'hekimler', 'label' => 'Hekimlerimiz', 'sira' => 5],
            ['key' => 'hizmetler', 'label' => 'Hizmetler', 'sira' => 6],
            ['key' => 'surec', 'label' => 'Süreç adımları', 'sira' => 7],
            ['key' => 'galeri', 'label' => 'Galeri', 'sira' => 8],
            ['key' => 'yorumlar', 'label' => 'Yorumlar', 'sira' => 9],
            ['key' => 'blog', 'label' => 'Blog', 'sira' => 10],
            ['key' => 'cta', 'label' => 'Alt CTA bandı', 'sira' => 11],
        ];
        foreach ($sections as $s) {
            DB::table('site_homepage_sections')->insert(array_merge($s, [
                'baslik' => null,
                'alt_metin' => null,
                'aktif' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $options = [
            'site_baslik_ek' => '',
            'slogan_override' => '',
            'footer_metin' => '',
            'tema_renk' => '#0b5ed7',
            'vitrin_badge' => '',
            'whatsapp_goster' => '1',
            'hekim_girisi_goster' => '1',
            'slider_aktif' => '1',
            'slider_otomatik_api' => '1',
            'seo_meta_baslik' => '',
            'seo_meta_aciklama' => '',
            'seo_meta_anahtar' => '',
            'iletisim_baslik' => 'İletişim & online randevu',
            'iletisim_alt_metin' => 'Hesap oluşturmadan randevu talebi bırakabilirsiniz.',
            'iletisim_form_goster' => '1',
            'iletisim_harita_goster' => '1',
            'iletisim_saatler_goster' => '1',
        ];
        foreach ($options as $k => $v) {
            DB::table('site_options')->insert([
                'key' => $k,
                'value' => $v,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_homepage_sections');
        Schema::dropIfExists('site_slider_slides');
        Schema::dropIfExists('site_menu_items');
        Schema::dropIfExists('site_options');
    }
};
