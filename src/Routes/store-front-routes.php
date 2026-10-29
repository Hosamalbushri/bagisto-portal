<?php

use Illuminate\Support\Facades\Route;
use Platform\Portal\Http\Controllers\Auth\LoginController;
use Platform\Portal\Http\Controllers\Auth\RegisterController;
use Platform\Portal\Http\Controllers\CurrencyController;
use Platform\Portal\Http\Controllers\HomeController;
use Platform\Portal\Http\Controllers\LocaleController;
use Platform\Portal\Http\Controllers\PageController;
use Platform\Portal\Http\Controllers\SitemapController;

/**
 * Dynamic Modular and CMS pages.
 */
Route::get('page/{slug}', [PageController::class, 'view'])
    ->name('shop.cms.page')
    ->middleware('cache.response');

Route::get('pages/{slug}', [PageController::class, 'view'])
    ->name('portal.page.show')
    ->middleware('cache.response');

Route::get('pages/{slug}/preview', [PageController::class, 'preview'])
    ->name('portal.page.preview');

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

/**
 * Locale switch.
 */
Route::get('locale/{code}', [LocaleController::class, 'switch'])
    ->name('portal.locale.switch');

/**
 * Currency switch.
 */
Route::get('currency/{code}', [CurrencyController::class, 'switch'])
    ->name('portal.currency.switch');

/**
 * Portal Authentication Routes (Available when enabled).
 */
Route::get('login', [LoginController::class, 'showLoginForm'])
    ->name('portal.auth.login');

Route::post('login', [LoginController::class, 'login'])
    ->name('portal.auth.login.store');

Route::any('logout', [LoginController::class, 'logout'])
    ->name('portal.auth.logout');

Route::get('register', [RegisterController::class, 'showRegistrationForm'])
    ->name('portal.auth.register');

Route::post('register', [RegisterController::class, 'register'])
    ->name('portal.auth.register.store');
