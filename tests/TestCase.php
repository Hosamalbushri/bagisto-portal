<?php

namespace Platform\Portal\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Route;
use Konekt\Concord\ConcordServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Platform\Portal\Providers\PortalServiceProvider;
use Platform\Portal\Tests\Support\TestAdminAuthenticate;
use Platform\PortalTokens\Providers\TokensServiceProvider;

if (class_exists('Tests\TestCase') && ! is_subclass_of('Tests\TestCase', self::class)) {
    class_alias('Tests\TestCase', __NAMESPACE__.'\BaseTestCase');
} elseif (class_exists(OrchestraTestCase::class)) {
    class_alias(OrchestraTestCase::class, __NAMESPACE__.'\BaseTestCase');
} else {
    class_alias(PhpUnitTestCase::class, __NAMESPACE__.'\BaseTestCase');
}

abstract class TestCase extends BaseTestCase
{
    /**
     * Get package providers.
     */
    protected function getPackageProviders($app)
    {
        return [
            ConcordServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            PortalServiceProvider::class,
            TokensServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:m9qK1e4t5mE2YQoX6x8mK/bB5yQ1U2I3o4P5a6S7d8E=');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('auth.guards.admin', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => User::class,
        ]);
    }

    /**
     * Set up before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (method_exists(bouncer(), 'reset')) {
            bouncer()::reset();
        }

        $router = $this->app['router'];
        if (! array_key_exists('admin', $router->getMiddleware())) {
            $router->aliasMiddleware('admin', TestAdminAuthenticate::class);
        }

        if (! Route::has('admin.session.create')) {
            Route::get('admin/login', fn () => 'login')->name('admin.session.create');
            $router->getRoutes()->refreshNameLookups();
        }

        $router = $this->app['router'];
        if (! array_key_exists('admin', $router->getMiddleware())) {
            $router->aliasMiddleware('admin', \Platform\Portal\Tests\Support\TestAdminAuthenticate::class);
        }

        if (! Route::has('admin.session.create')) {
            Route::get('admin/login', fn () => 'login')->name('admin.session.create');
            $router->getRoutes()->refreshNameLookups();
        }

        $this->loadMigrationsFrom(__DIR__.'/../src/Database/Migrations');
    }

    /**
     * Clean up after each test to prevent static authorization state leakage.
     */
    protected function tearDown(): void
    {
        if (method_exists(bouncer(), 'reset')) {
            bouncer()::reset();
        }

        if ($this->app && $this->app->bound('auth')) {
            auth()->guard('admin')->logout();
        }

        parent::tearDown();
    }
}

// Ensure global \Tests\TestCase alias exists for compatibility
if (! class_exists('Tests\TestCase')) {
    class_alias(TestCase::class, 'Tests\TestCase');
}
