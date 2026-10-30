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
        Schema::create('portal_menus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->boolean('status')->default(1);

            $table->integer('channel_id')->unsigned()->nullable();
            $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');

            $table->timestamps();

            $table->unique(['slug', 'channel_id']);
        });

        Schema::create('portal_menu_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->default('custom'); // 'page', 'custom', 'route'
            $table->string('url')->nullable();
            $table->string('target')->default('_self'); // '_self', '_blank'
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);

            $table->integer('portal_menu_id')->unsigned();
            $table->foreign('portal_menu_id')->references('id')->on('portal_menus')->onDelete('cascade');

            $table->integer('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('portal_menu_items')->onDelete('cascade');

            $table->integer('portal_page_id')->unsigned()->nullable();
            $table->foreign('portal_page_id')->references('id')->on('portal_pages')->onDelete('set null');

            $table->timestamps();
        });

        Schema::create('portal_menu_item_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('locale');
            $table->string('title');

            $table->integer('portal_menu_item_id')->unsigned();
            $table->foreign('portal_menu_item_id')->references('id')->on('portal_menu_items')->onDelete('cascade');

            $table->unique(['portal_menu_item_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_menu_item_translations');
        Schema::dropIfExists('portal_menu_items');
        Schema::dropIfExists('portal_menus');
    }
};
