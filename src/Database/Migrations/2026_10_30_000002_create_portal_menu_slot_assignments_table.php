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
        Schema::create('portal_menu_slot_assignments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slot_id');
            $table->string('location')->default('footer'); // header, footer, etc.
            $table->integer('portal_menu_id')->unsigned()->nullable();
            $table->integer('channel_id')->unsigned()->nullable();
            $table->string('theme_code')->default('default');
            $table->string('title')->nullable();
            $table->json('options')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('portal_menu_id')
                ->references('id')
                ->on('portal_menus')
                ->onDelete('set null');

            $table->foreign('channel_id')
                ->references('id')
                ->on('channels')
                ->onDelete('cascade');

            $table->index(['slot_id', 'channel_id', 'theme_code'], 'slot_channel_theme_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_menu_slot_assignments');
    }
};
