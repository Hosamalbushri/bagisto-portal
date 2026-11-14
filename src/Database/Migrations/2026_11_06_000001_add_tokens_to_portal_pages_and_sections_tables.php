<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add tokens to portal_pages and portal_page_sections tables.
 *
 * NOTE: Rollback after token data has been saved will permanently destroy that token data.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('portal_pages', function (Blueprint $table) {
            $table->json('tokens')->nullable()->after('theme_code');
        });

        Schema::table('portal_page_sections', function (Blueprint $table) {
            $table->json('tokens')->nullable()->after('status');
            $table->json('draft_tokens')->nullable()->after('draft_sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_pages', function (Blueprint $table) {
            $table->dropColumn('tokens');
        });

        Schema::table('portal_page_sections', function (Blueprint $table) {
            $table->dropColumn(['tokens', 'draft_tokens']);
        });
    }
};
