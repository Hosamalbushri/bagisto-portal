<?php

namespace Platform\Portal\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Platform\Portal\Http\Middleware\CacheResponse;
use Platform\Portal\Http\Middleware\CheckStorefrontStatus;
use Platform\Portal\Http\Middleware\Currency;
use Platform\Portal\Http\Middleware\Locale;
use Platform\Portal\Http\Middleware\Theme;
use Platform\Portal\Sections\SectionTypeRegistry;

class PortalServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        require_once __DIR__.'/../Helpers/helpers.php';

        $this->app->register(ModuleServiceProvider::class);

        $this->app->singleton(SectionTypeRegistry::class, function () {
            return new SectionTypeRegistry;
        });

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/bagisto-vite.php',
            'bagisto-vite.viters'
        );

        $portalThemes = require dirname(__DIR__).'/Config/themes.php';

        $this->app['config']->set(
            'themes.shop',
            array_merge($this->app['config']->get('themes.shop', []), $portalThemes)
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/menu.php',
            'menu.admin'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/acl.php',
            'acl'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/portal.php',
            'portal'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/system.php',
            'core'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        $router->middlewareGroup('portal', [
            Theme::class,
            Locale::class,
            Currency::class,
            CheckStorefrontStatus::class,
        ]);

        $router->aliasMiddleware('theme', Theme::class);
        $router->aliasMiddleware('locale', Locale::class);
        $router->aliasMiddleware('currency', Currency::class);
        $router->aliasMiddleware('cache.response', CacheResponse::class);
        $router->aliasMiddleware('portal.storefront', CheckStorefrontStatus::class);

        $this->app->booted(function () use ($router) {
            $router->pushMiddlewareToGroup('portal', CheckStorefrontStatus::class);

            Route::middleware(['web'])->group(__DIR__.'/../Routes/web.php');
        });

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'portal');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'portal');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'portal');

        Blade::directive('portalMenuSlot', function ($expression) {
            return "<?php echo Blade::render('<x-portal::menu-slot id=\"' . {$expression} . '\" />'); ?>";
        });

        $portalMenu = require dirname(__DIR__).'/Config/menu.php';
        $currentMenu = config('menu.admin', []);
        config(['menu.admin' => collect($currentMenu)->concat($portalMenu)->unique('key')->values()->toArray()]);

        $portalAcl = require dirname(__DIR__).'/Config/acl.php';
        $currentAcl = config('acl', []);
        config(['acl' => collect($currentAcl)->concat($portalAcl)->unique('key')->values()->toArray()]);

        $portalSystem = require dirname(__DIR__).'/Config/system.php';
        $currentCore = config('core', []);
        config(['core' => collect($currentCore)->concat($portalSystem)->unique('key')->values()->toArray()]);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../Resources/views' => resource_path('views/vendor/portal'),
            ], 'portal-views');

            $this->publishes([
                dirname(__DIR__).'/Config/portal.php' => config_path('portal.php'),
            ], 'portal-config');

            $this->publishes([
                __DIR__.'/../Resources/lang' => $this->app->langPath('vendor/portal'),
            ], 'portal-lang');
        }
    }
}
