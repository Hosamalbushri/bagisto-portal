<?php

namespace Platform\Portal\Tests\Unit\Providers;

use Illuminate\Support\Facades\Event;
use Mockery;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Services\ThemeTokenService;
use Platform\Portal\Services\TokenCompilerService;
use Tests\TestCase;

class PortalServiceProviderCacheTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Helper to dynamically register the dummy Spatie ResponseCache class if not present.
     */
    protected function ensureResponseCacheClassExists(): void
    {
        if (! class_exists('Spatie\ResponseCache\ResponseCache')) {
            eval('namespace Spatie\ResponseCache; class ResponseCache { public function clear(): void {} }');
        }
    }

    /**
     * Scenario A: Optional integration absent.
     * When ResponseCache class is unavailable, dispatching portal.tokens.updated
     * causes no exception and the application remains fully operational.
     */
    public function test_scenario_a_dispatches_cleanly_when_responsecache_class_absent(): void
    {
        // If class already exists from previous runs, this test checks absent container binding
        $wasBound = app()->bound('Spatie\ResponseCache\ResponseCache');
        $this->assertFalse($wasBound, 'ResponseCache should not be bound by default.');

        $dispatched = false;
        try {
            Event::dispatch('portal.tokens.updated', [[
                'channel_id' => 1,
                'theme_code' => 'portal',
                'operation' => 'apply_preset',
            ]]);
            $dispatched = true;
        } catch (\Throwable $e) {
            $this->fail('Event dispatch threw an unexpected exception: '.$e->getMessage());
        }

        $this->assertTrue($dispatched, 'Dispatch should complete without errors when optional package is absent.');
    }

    /**
     * Scenario B: Class exists but container binding is absent.
     * When ResponseCache class exists but is not bound in container,
     * the listener must not attempt to resolve the unbound service.
     */
    public function test_scenario_b_listener_does_not_attempt_to_resolve_unbound_responsecache(): void
    {
        $this->ensureResponseCacheClassExists();

        $this->assertTrue(class_exists('Spatie\ResponseCache\ResponseCache'));
        $this->assertFalse(app()->bound('Spatie\ResponseCache\ResponseCache'));

        // If app() attempted to resolve an unbound class without binding, it could trigger resolution issues.
        // We ensure dispatch executes cleanly without resolving.
        $executed = false;
        try {
            Event::dispatch('portal.tokens.updated', [[
                'channel_id' => 2,
                'theme_code' => 'midnight',
                'operation' => 'customize_tokens',
            ]]);
            $executed = true;
        } catch (\Throwable $e) {
            $this->fail('Listener attempted to resolve unbound ResponseCache: '.$e->getMessage());
        }

        $this->assertTrue($executed);
    }

    /**
     * Scenario C: ResponseCache available and bound.
     * Dispatching portal.tokens.updated invokes the existing registered clear() method exactly once.
     */
    public function test_scenario_c_dispatches_and_invokes_responsecache_clear_exactly_once(): void
    {
        $this->ensureResponseCacheClassExists();

        $mockCache = Mockery::mock('Spatie\ResponseCache\ResponseCache');
        $mockCache->shouldReceive('clear')
            ->once();

        $this->app->instance('Spatie\ResponseCache\ResponseCache', $mockCache);
        $this->assertTrue(app()->bound('Spatie\ResponseCache\ResponseCache'));

        // Dispatch through real Event dispatcher to exercise the listener registered by PortalServiceProvider
        Event::dispatch('portal.tokens.updated', [[
            'channel_id' => 1,
            'theme_code' => 'portal',
            'operation' => 'reset_tokens',
        ]]);
    }

    /**
     * Scenario D: ResponseCache clear throws an exception.
     * The exception must not escape the listener, the dispatch must complete without propagating,
     * and a successful token persistence operation must not be misrepresented as a failure.
     */
    public function test_scenario_d_clear_exception_does_not_escape_listener_and_token_save_succeeds(): void
    {
        $this->ensureResponseCacheClassExists();

        $mockCache = Mockery::mock('Spatie\ResponseCache\ResponseCache');
        $mockCache->shouldReceive('clear')
            ->once()
            ->andThrow(new \RuntimeException('Redis connection failed during response cache clear'));

        $this->app->instance('Spatie\ResponseCache\ResponseCache', $mockCache);

        // 1. Direct dispatch test: exception does not escape listener
        $dispatchCompleted = false;
        try {
            Event::dispatch('portal.tokens.updated', [[
                'channel_id' => 1,
                'theme_code' => 'portal',
                'operation' => 'customize_tokens',
            ]]);
            $dispatchCompleted = true;
        } catch (\Throwable $e) {
            $this->fail('Exception escaped the ResponseCache listener: '.$e->getMessage());
        }
        $this->assertTrue($dispatchCompleted, 'Direct event dispatch must not fail when ResponseCache::clear throws.');

        // 2. End-to-end service test: ThemeTokenService succeeds despite ResponseCache::clear throwing
        $mockRepo = Mockery::mock(PortalLayoutSettingRepository::class);
        $mockRepo->shouldReceive('saveSettings')
            ->once()
            ->with('theme_tokens', Mockery::type('array'), 1, 'portal')
            ->andReturn(true);

        $mockCacheEndToEnd = Mockery::mock('Spatie\ResponseCache\ResponseCache');
        $mockCacheEndToEnd->shouldReceive('clear')
            ->once()
            ->andThrow(new \RuntimeException('Network timeout during FPC clear'));

        $this->app->instance('Spatie\ResponseCache\ResponseCache', $mockCacheEndToEnd);

        $service = new ThemeTokenService(
            app(TokenCompilerService::class),
            $mockRepo
        );

        $result = $service->customizeTokens(1, 'portal', ['--brand-primary' => '#123456']);

        $this->assertIsArray($result);
        $this->assertSame('#123456', $result['--brand-primary']);
    }
}
