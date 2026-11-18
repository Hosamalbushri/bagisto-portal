<?php

use Illuminate\Support\Facades\Route;
use Platform\Portal\Http\Controllers\API\MenuController;
use Platform\Portal\Http\Controllers\API\PageController;
use Platform\Portal\Http\Controllers\API\SectionController;

Route::group(['prefix' => 'api/portal'], function () {
    Route::controller(PageController::class)->prefix('pages')->group(function () {
        Route::get('', 'index')->name('portal.api.pages.index');
        Route::get('{slug}', 'show')->name('portal.api.pages.show');
    });

    Route::controller(MenuController::class)->prefix('menus')->group(function () {
        Route::get('{slug}', 'show')->name('portal.api.menus.show');
    });

    Route::controller(SectionController::class)->prefix('sections')->group(function () {
        Route::get('', 'index')->name('portal.api.sections.index');
    });
});
