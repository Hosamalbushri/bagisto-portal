<?php

use Illuminate\Support\Facades\Route;
use Platform\Portal\Http\Controllers\HomeController;
use Platform\Portal\Http\Controllers\PageController;
use Platform\Portal\Http\Controllers\SitemapController;

/**
 * CMS pages.
 */
Route::get('page/{slug}', [PageController::class, 'view'])
    ->name('shop.cms.page')
    ->middleware('cache.response');

/**
 * Search-engine discovery.
 */
Route::get('sitemap.xml', [SitemapController::class, 'index'])
    ->name('shop.sitemap.index');

Route::get('robots.txt', [SitemapController::class, 'robots'])
    ->name('shop.robots');

/**
 * Store front home.
 */
Route::get('/', [HomeController::class, 'index'])
    ->name('shop.home.index')
    ->middleware('cache.response');

/**
 * Appearance editor preview, never cached.
 *
 * The path must stay one segment deep: storefront markup emits relative asset urls.
 */
Route::get('appearance-preview', [HomeController::class, 'preview'])
    ->name('shop.appearance.preview');

/**
 * Contact Us.
 */
Route::get('contact-us', [HomeController::class, 'contactUs'])
    ->name('shop.home.contact_us')
    ->middleware('cache.response');

Route::post('contact-us/send-mail', [HomeController::class, 'sendContactUsMail'])
    ->name('shop.home.contact_us.send_mail')
    ->middleware('cache.response');
