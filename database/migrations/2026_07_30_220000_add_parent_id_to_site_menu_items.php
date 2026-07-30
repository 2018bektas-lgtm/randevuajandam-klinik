<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_menu_items', function (Blueprint $table) {
            if (! Schema::hasColumn('site_menu_items', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('id');
                $table->index('parent_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_menu_items', function (Blueprint $table) {
            if (Schema::hasColumn('site_menu_items', 'parent_id')) {
                $table->dropIndex(['parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
};
