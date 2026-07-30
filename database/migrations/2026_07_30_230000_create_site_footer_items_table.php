<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_footer_items')) {
            return;
        }

        Schema::create('site_footer_items', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('label');
            $table->string('route')->default('frontend.anasayfa');
            $table->string('url')->nullable();
            $table->boolean('aktif')->default(true);
            $table->unsignedInteger('sira')->default(0);
            $table->timestamps();
            $table->index('sira');
        });

        $now = now();
        $defaults = [
            ['key' => 'hakkimda', 'label' => 'Hakkımızda', 'route' => 'frontend.hakkimda', 'sira' => 1],
            ['key' => 'hekimler', 'label' => 'Hekimlerimiz', 'route' => 'frontend.hekimler', 'sira' => 2],
            ['key' => 'hizmetler', 'label' => 'Hizmetler', 'route' => 'frontend.hizmetler', 'sira' => 3],
            ['key' => 'galeri', 'label' => 'Galeri', 'route' => 'frontend.galeri', 'sira' => 4],
            ['key' => 'blog', 'label' => 'Blog', 'route' => 'frontend.blog', 'sira' => 5],
            ['key' => 'sss', 'label' => 'S.S.S.', 'route' => 'frontend.sss', 'sira' => 6],
            ['key' => 'iletisim', 'label' => 'İletişim', 'route' => 'frontend.iletisim', 'sira' => 7],
        ];
        foreach ($defaults as $row) {
            DB::table('site_footer_items')->insert(array_merge($row, [
                'url' => null,
                'aktif' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_footer_items');
    }
};
