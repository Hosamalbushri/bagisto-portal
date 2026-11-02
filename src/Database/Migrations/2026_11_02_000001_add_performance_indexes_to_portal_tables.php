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
        Schema::table('portal_page_sections', function (Blueprint $table) {
            $table->index(['portal_page_id', 'status', 'sort_order'], 'portal_sections_page_status_sort_idx');
        });

        Schema::table('portal_menu_items', function (Blueprint $table) {
            $table->index(['portal_menu_id', 'status', 'sort_order'], 'portal_menu_items_scope_idx');
        });

        Schema::table('portal_menu_slot_assignments', function (Blueprint $table) {
            $table->index(['channel_id', 'theme_code', 'slot_id'], 'portal_slot_channel_theme_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_page_sections', function (Blueprint $table) {
            $table->dropIndex('portal_sections_page_status_sort_idx');
        });

        Schema::table('portal_menu_items', function (Blueprint $table) {
            $table->dropIndex('portal_menu_items_scope_idx');
        });

        Schema::table('portal_menu_slot_assignments', function (Blueprint $table) {
            $table->dropIndex('portal_slot_channel_theme_idx');
        });
    }
};
