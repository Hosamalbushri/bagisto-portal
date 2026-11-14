<?php

namespace Platform\PortalSectionsApi\Providers;

use Illuminate\Support\ServiceProvider;
use Platform\PortalSectionsApi\SectionTypeRegistry;

class SectionsApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SectionTypeRegistry::class, function () {
            return new SectionTypeRegistry;
        });

        $this->app->alias(SectionTypeRegistry::class, 'portal.section');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Pure API contract provider; registration of concrete sections is owned by feature packages.
    }
}
