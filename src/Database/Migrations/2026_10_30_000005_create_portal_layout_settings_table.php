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
        Schema::create('portal_layout_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('channel_id')->unsigned()->nullable();
            $table->string('theme_code')->default('default');
            $table->string('locale_code')->nullable();
            $table->string('group'); // 'header', 'footer'
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->foreign('channel_id')
                ->references('id')
                ->on('channels')
                ->onDelete('cascade');

            $table->index(['channel_id', 'theme_code', 'group'], 'portal_layout_settings_scope_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_layout_settings');
    }
};
