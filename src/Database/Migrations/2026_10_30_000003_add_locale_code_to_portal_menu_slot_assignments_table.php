<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('portal_menu_slot_assignments', function (Blueprint $table) {
            $table->string('locale_code')->nullable()->after('theme_code');
            $table->index(['slot_id', 'channel_id', 'theme_code', 'locale_code'], 'slot_ch_theme_loc_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_menu_slot_assignments', function (Blueprint $table) {
            $table->dropIndex('slot_ch_theme_loc_idx');
            $table->dropColumn('locale_code');
        });
    }
};
