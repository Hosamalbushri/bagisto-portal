<?php

use Illuminate\Support\Facades\Route;
use Platform\Portal\Http\Controllers\Admin\PageController;
use Platform\Portal\Http\Controllers\Admin\PageCustomizeController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => config('app.admin_url')], function () {
    Route::prefix('portal/pages')->group(function () {
        Route::get('', [PageController::class, 'index'])->name('admin.portal.pages.index');
        Route::get('create', [PageController::class, 'create'])->name('admin.portal.pages.create');
        Route::post('create', [PageController::class, 'store'])->name('admin.portal.pages.store');
        Route::get('edit/{id}', [PageController::class, 'edit'])->name('admin.portal.pages.edit');
        Route::put('edit/{id}', [PageController::class, 'update'])->name('admin.portal.pages.update');
        Route::delete('delete/{id}', [PageController::class, 'delete'])->name('admin.portal.pages.delete');

        // Customizer & Appearance Sections Editor routes
        Route::get('customize/{id}', [PageCustomizeController::class, 'index'])->name('admin.portal.pages.customize');
        Route::post('customize/{id}/sections', [PageCustomizeController::class, 'store'])->name('admin.portal.pages.sections.store');
        Route::post('customize/{id}/reorder', [PageCustomizeController::class, 'reorder'])->name('admin.portal.pages.sections.reorder');
        Route::post('customize/{id}/publish', [PageCustomizeController::class, 'publish'])->name('admin.portal.pages.publish');
        Route::post('customize/{id}/discard', [PageCustomizeController::class, 'discard'])->name('admin.portal.pages.discard');

        Route::get('customize/{id}/sections/{sectionId}/fields', [PageCustomizeController::class, 'fields'])->name('admin.portal.pages.sections.fields');
        Route::post('customize/{id}/sections/{sectionId}/draft', [PageCustomizeController::class, 'saveDraft'])->name('admin.portal.pages.sections.draft');
        Route::post('customize/{id}/sections/{sectionId}/media', [PageCustomizeController::class, 'uploadMedia'])->name('admin.portal.pages.sections.media');
        Route::post('customize/{id}/sections/{sectionId}/status', [PageCustomizeController::class, 'status'])->name('admin.portal.pages.sections.status');
        Route::post('customize/{id}/sections/{sectionId}/duplicate', [PageCustomizeController::class, 'duplicate'])->name('admin.portal.pages.sections.duplicate');
        Route::delete('customize/{id}/sections/{sectionId}', [PageCustomizeController::class, 'destroy'])->name('admin.portal.pages.sections.delete');
    });
});
