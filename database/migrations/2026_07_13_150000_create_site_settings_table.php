<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Doktor sitesinin kendi vitrin ayarları (SQLite).
 * Ana platform API verisinden bağımsız: slider, menü, ana sayfa blokları vb.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->unique(); // genel, menu, slider, anasayfa, seo, iletisim
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
