<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Özel vitrin sayfaları (KVKK, gizlilik, hakkımızda ek vb.)
 * Menüden seçilir veya footera işaretlenir.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_pages')) {
            return;
        }

        Schema::create('site_pages', function (Blueprint $table) {
            $table->id();
            $table->string('baslik');
            $table->string('slug')->unique();
            $table->longText('icerik')->nullable();
            $table->boolean('aktif')->default(true);
            $table->boolean('footer_goster')->default(false);
            $table->unsignedInteger('sira')->default(0);
            $table->timestamps();
            $table->index(['aktif', 'footer_goster', 'sira']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_pages');
    }
};
