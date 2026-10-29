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

        // Customizer & Section routes
        Route::get('customize/{id}', [PageCustomizeController::class, 'index'])->name('admin.portal.pages.customize');
        Route::post('customize/{id}/sections', [PageCustomizeController::class, 'storeSection'])->name('admin.portal.pages.sections.store');
        Route::post('customize/{id}/sections/{sectionId}', [PageCustomizeController::class, 'updateSection'])->name('admin.portal.pages.sections.update');
        Route::delete('customize/{id}/sections/{sectionId}', [PageCustomizeController::class, 'deleteSection'])->name('admin.portal.pages.sections.delete');
        Route::post('customize/{id}/reorder', [PageCustomizeController::class, 'reorderSections'])->name('admin.portal.pages.reorder');
        Route::post('customize/{id}/publish', [PageCustomizeController::class, 'publish'])->name('admin.portal.pages.publish');
        Route::post('customize/{id}/discard', [PageCustomizeController::class, 'discard'])->name('admin.portal.pages.discard');
    });
});
