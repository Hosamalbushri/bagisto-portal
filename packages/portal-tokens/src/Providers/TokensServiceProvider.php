<?php

namespace Platform\PortalTokens\Providers;

use Illuminate\Support\ServiceProvider;
use Platform\PortalTokens\ComponentBindingRegistry;
use Platform\PortalTokens\DtcgExporter;
use Platform\PortalTokens\DtcgImporter;
use Platform\PortalTokens\TokenCompiler;
use Platform\PortalTokens\TokenCompilerService;
use Platform\PortalTokens\TokenDefaultResolver;
use Platform\PortalTokens\TokenDeriver;
use Platform\PortalTokens\TokenSanitizer;

class TokensServiceProvider extends ServiceProvider
{
    /**
     * Register token engine singletons.
     */
    public function register(): void
    {
        $this->app->singleton(TokenSanitizer::class, function () {
            if (class_exists('Platform\Portal\Services\Tokens\TokenSanitizer')) {
                return new \Platform\Portal\Services\Tokens\TokenSanitizer;
            }

            return new TokenSanitizer;
        });

        if (class_exists('Platform\Portal\Services\Tokens\TokenSanitizer')) {
            $this->app->alias(TokenSanitizer::class, 'Platform\Portal\Services\Tokens\TokenSanitizer');
        }

        $this->app->singleton(TokenDefaultResolver::class, function () {
            return new TokenDefaultResolver;
        });

        $this->app->singleton(TokenDeriver::class, function ($app) {
            return new TokenDeriver(
                $app->make(TokenDefaultResolver::class)
            );
        });

        $this->app->singleton(ComponentBindingRegistry::class, function () {
            return new ComponentBindingRegistry;
        });

        $this->app->singleton(TokenCompiler::class, function ($app) {
            return new TokenCompiler(
                $app->make(ComponentBindingRegistry::class)
            );
        });

        $this->app->singleton(DtcgExporter::class, function () {
            return new DtcgExporter;
        });

        $this->app->singleton(DtcgImporter::class, function ($app) {
            return new DtcgImporter(
                $app->make(TokenSanitizer::class)
            );
        });

        $this->app->singleton(TokenCompilerService::class, function ($app) {
            return new TokenCompilerService(
                $app->make(TokenCompiler::class),
                $app->make(TokenSanitizer::class),
                $app->make(TokenDeriver::class),
                $app->make(DtcgExporter::class),
                $app->make(DtcgImporter::class),
                $app->make(TokenDefaultResolver::class)
            );
        });
    }

    /**
     * Bootstrap token services.
     */
    public function boot(): void
    {
        // Standalone engine; configuration is merged when present
    }
}
