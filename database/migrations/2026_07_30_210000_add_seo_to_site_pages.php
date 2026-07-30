<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_pages')) {
            return;
        }
        Schema::table('site_pages', function (Blueprint $table) {
            if (! Schema::hasColumn('site_pages', 'meta_baslik')) {
                $table->string('meta_baslik', 255)->nullable()->after('icerik');
            }
            if (! Schema::hasColumn('site_pages', 'meta_aciklama')) {
                $table->string('meta_aciklama', 500)->nullable()->after('meta_baslik');
            }
            if (! Schema::hasColumn('site_pages', 'meta_anahtar_kelimeler')) {
                $table->string('meta_anahtar_kelimeler', 500)->nullable()->after('meta_aciklama');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_pages')) {
            return;
        }
        Schema::table('site_pages', function (Blueprint $table) {
            foreach (['meta_baslik', 'meta_aciklama', 'meta_anahtar_kelimeler'] as $col) {
                if (Schema::hasColumn('site_pages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
