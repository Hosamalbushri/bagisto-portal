<?php

namespace Platform\Portal\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Platform\Portal\Http\Middleware\CacheResponse;
use Platform\Portal\Http\Middleware\Currency;
use Platform\Portal\Http\Middleware\Locale;
use Platform\Portal\Http\Middleware\Theme;

class PortalServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/bagisto-vite.php',
            'bagisto-vite.viters'
        );

        $portalThemes = require dirname(__DIR__).'/Config/themes.php';

        $this->app['config']->set(
            'themes.shop',
            array_merge($this->app['config']->get('themes.shop', []), $portalThemes)
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        $router->middlewareGroup('shop', [
            Theme::class,
            Locale::class,
            Currency::class,
        ]);

        $router->middlewareGroup('portal', [
            Theme::class,
            Locale::class,
            Currency::class,
        ]);

        $router->aliasMiddleware('theme', Theme::class);
        $router->aliasMiddleware('locale', Locale::class);
        $router->aliasMiddleware('currency', Currency::class);
        $router->aliasMiddleware('cache.response', CacheResponse::class);

        Route::middleware(['web'])->group(__DIR__.'/../Routes/web.php');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'portal');
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'shop');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'portal');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'shop');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'portal');
        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'shop');

        Paginator::defaultView('portal::partials.pagination');
        Paginator::defaultSimpleView('portal::partials.pagination');
    }
}
