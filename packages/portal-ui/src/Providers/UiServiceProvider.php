<?php

namespace Platform\PortalUi\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class UiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap UI component paths.
     */
    public function boot(): void
    {
        $viewsPath = dirname(__DIR__, 2).'/resources/views';

        // Primary namespace for modular consumers: <x-portal-ui::button>
        Blade::anonymousComponentPath($viewsPath, 'portal-ui');

        // Backward compatibility namespace for legacy consumers: <x-portal::button>
        Blade::anonymousComponentPath($viewsPath, 'portal');
    }
}
