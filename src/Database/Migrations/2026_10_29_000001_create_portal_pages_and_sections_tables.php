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
        Schema::create('portal_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
            $table->string('template')->default('default');
            $table->string('theme_code')->nullable();
            $table->boolean('status')->default(1);
            $table->integer('sort_order')->default(0);

            $table->integer('channel_id')->unsigned();
            $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');

            $table->timestamps();

            $table->unique(['slug', 'channel_id']);
        });

        Schema::create('portal_page_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('locale');
            $table->string('title');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            $table->integer('portal_page_id')->unsigned();
            $table->foreign('portal_page_id')->references('id')->on('portal_pages')->onDelete('cascade');

            $table->unique(['portal_page_id', 'locale']);
        });

        Schema::create('portal_page_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(1);
            $table->boolean('draft_status')->nullable();
            $table->integer('draft_sort_order')->nullable();

            $table->integer('portal_page_id')->unsigned();
            $table->foreign('portal_page_id')->references('id')->on('portal_pages')->onDelete('cascade');

            $table->timestamps();
        });

        Schema::create('portal_page_section_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('locale');
            $table->json('options')->nullable();
            $table->json('draft_options')->nullable();

            $table->integer('portal_page_section_id')->unsigned();
            $table->foreign('portal_page_section_id')->references('id')->on('portal_page_sections')->onDelete('cascade');

            $table->unique(['portal_page_section_id', 'locale'], 'portal_section_trans_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_page_section_translations');
        Schema::dropIfExists('portal_page_sections');
        Schema::dropIfExists('portal_page_translations');
        Schema::dropIfExists('portal_pages');
    }
};
