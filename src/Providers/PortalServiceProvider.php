<?php

namespace Platform\Portal\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Platform\Portal\Console\Commands\InstallPortalCommand;
use Platform\Portal\Console\Commands\MakeSectionCommand;
use Platform\Portal\Console\Commands\MakeThemeCommand;
use Platform\Portal\Helpers\PortalCache;
use Platform\Portal\Http\Middleware\CacheResponse;
use Platform\Portal\Http\Middleware\CheckStorefrontStatus;
use Platform\Portal\Http\Middleware\Currency;
use Platform\Portal\Http\Middleware\Locale;
use Platform\Portal\Http\Middleware\Theme;
use Platform\Portal\Repositories\PortalMenuRepository;
use Platform\Portal\Sections\SectionTypeRegistry;
use Platform\Portal\Services\ExtendedThemeCatalog;
use Platform\Portal\Services\ThemeManager;
use Spatie\ResponseCache\ResponseCache;
use Webkul\Core\Http\Middleware\PreventRequestsDuringMaintenance;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Theme\Repositories\SectionRepository;
use Webkul\Theme\ThemeCatalog;

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

        $this->app->alias(SectionTypeRegistry::class, 'portal.section');

        $this->app->singleton(PortalCache::class, function () {
            return new PortalCache;
        });

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/bagisto-vite.php',
            'bagisto-vite.viters'
        );

        // Bind ThemeManager singleton & auto-discover themes cleanly
        $this->app->singleton(ThemeManager::class, function () {
            return new ThemeManager;
        });

        $portalThemes = require dirname(__DIR__).'/Config/themes.php';
        $discoveredThemes = $this->app->make(ThemeManager::class)->toConfigArray();

        $this->app['config']->set(
            'themes.portal',
            array_merge($portalThemes, $discoveredThemes)
        );

        $this->app['config']->set(
            'themes.shop',
            array_merge($this->app['config']->get('themes.shop', []), $portalThemes, $discoveredThemes)
        );

        // Clean container extension: enhance ThemeCatalog with local theme configs without touching Bagisto core
        if (class_exists(ThemeCatalog::class)) {
            $this->app->extend(ThemeCatalog::class, function ($catalog, $app) {
                return new ExtendedThemeCatalog(
                    $app[ChannelRepository::class],
                    $app[SectionRepository::class]
                );
            });
        }

        $this->registerConfig();
    }

    /**
     * Register package configurations conforming to Bagisto core standards.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/portal.php',
            'portal'
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

        $this->app->booted(function () {
            Route::middleware(['web'])->group(__DIR__.'/../Routes/web.php');
            Route::middleware(['web', 'portal', PreventRequestsDuringMaintenance::class])
                ->group(__DIR__.'/../Routes/api.php');

            // Seamlessly resolve missing core Bagisto named routes (e.g. admin header's "visit-shop" link)
            if ($this->app->bound('url')) {
                try {
                    $url = $this->app['url'];
                    if (method_exists($url, 'resolveMissingNamedRoutesUsing')) {
                        $url->resolveMissingNamedRoutesUsing(function ($name, $parameters = [], $absolute = true) {
                            return match ($name) {
                                'shop.home.index' => Route::has('portal.home') ? route('portal.home', $parameters, $absolute) : url('/'),
                                'shop.cms.page' => Route::has('portal.page.show') ? route('portal.page.show', $parameters, $absolute) : url('/pages/'.(is_array($parameters) ? ($parameters['slug'] ?? reset($parameters)) : $parameters)),
                                'shop.appearance.preview' => Route::has('portal.appearance.preview') ? route('portal.appearance.preview', $parameters, $absolute) : url('/appearance-preview'),
                                default => null,
                            };
                        });
                    }
                } catch (\Throwable) {
                    // Ignore if url generator is not resolvable during early non-HTTP bootstrap
                }
            }
        });

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'portal');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'portal');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'portal');

        Blade::directive('portalMenuSlot', function ($expression) {
            return "<?php echo \$__env->make('portal::components.menu-slot.index', ['id' => {$expression}], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
        });

        // Listen for Bagisto configuration updates to invalidate cached design tokens & FPC
        Event::listen('core.configuration.save.after', function () {
            ThemeManager::clearTokenCache();
            event('portal.tokens.updated');
        });

        // Listen for Bagisto theme activations and dispatch portal domain event
        Event::listen('appearance.theme.activate.after', function ($channel) {
            $channelId = is_object($channel) ? ($channel->id ?? 0) : (int) $channel;
            $themeCode = is_object($channel) ? ($channel->theme ?? '') : (core()->getCurrentChannel()?->theme ?? '');

            app(ThemeManager::class)->clearCache();
            event('portal.theme.activated', [$channelId, $themeCode]);
        });

        // Event-driven cache invalidation (FPC + PortalCache)
        Event::listen([
            'portal.page.created',
            'portal.page.updated',
            'portal.page.deleted',
            'portal.page.published',
            'portal.theme.activated',
            'portal.menu.updated',
            'portal.tokens.updated',
        ], function () {
            try {
                if (app()->bound(PortalCache::class)) {
                    app(PortalCache::class)->flush();
                }
            } catch (\Throwable) {
                // Graceful fallback
            }

            if (class_exists(ResponseCache::class) && app()->bound(ResponseCache::class)) {
                try {
                    app(ResponseCache::class)->clear();
                } catch (\Throwable) {
                    // Graceful fallback
                }
            }
        });

        Event::listen([
            'portal.page.created',
            'portal.page.updated',
            'portal.page.deleted',
            'portal.page.published',
        ], function () {
            try {
                PortalMenuRepository::clearCache(false);
            } catch (\Throwable) {
                // Graceful fallback
            }
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallPortalCommand::class,
                MakeSectionCommand::class,
                MakeThemeCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../Resources/views' => resource_path('views/vendor/portal'),
            ], 'portal-views');

            $this->publishes([
                dirname(__DIR__).'/Config/portal.php' => config_path('portal.php'),
            ], 'portal-config');

            $this->publishes([
                __DIR__.'/../Resources/lang' => $this->app->langPath('vendor/portal'),
            ], 'portal-lang');

            $this->publishes([
                __DIR__.'/../Resources/assets' => public_path('vendor/portal'),
            ], 'portal-assets');

            $this->publishes([
                dirname(__DIR__, 2).'/stubs' => base_path('stubs/portal'),
            ], 'portal-stubs');

            $this->publishes([
                dirname(__DIR__).'/Config/portal.php' => config_path('portal.php'),
                __DIR__.'/../Resources/views' => resource_path('views/vendor/portal'),
                __DIR__.'/../Resources/lang' => $this->app->langPath('vendor/portal'),
            ], 'portal');
        }
    }
}
