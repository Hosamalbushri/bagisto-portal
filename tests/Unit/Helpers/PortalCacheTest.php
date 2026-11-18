<?php

namespace Platform\Portal\Tests\Unit\Helpers;

use Illuminate\Support\Facades\Cache;
use Platform\Portal\Helpers\PortalCache;
use Platform\Portal\Tests\TestCase;

class PortalCacheTest extends TestCase
{
    protected PortalCache $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = new PortalCache;
    }

    public function test_initial_version_is_integer(): void
    {
        $version = $this->cache->version();
        $this->assertIsInt($version);
        $this->assertGreaterThanOrEqual(1, $version);
    }

    public function test_flush_increments_version_atomically(): void
    {
        $initial = $this->cache->version();
        $this->cache->flush();
        $updated = $this->cache->version();

        $this->assertEquals($initial + 1, $updated);
    }

    public function test_key_generation_is_version_aware(): void
    {
        $key1 = $this->cache->key('test_segment', ['foo' => 'bar']);
        $this->cache->flush();
        $key2 = $this->cache->key('test_segment', ['foo' => 'bar']);

        $this->assertNotEquals($key1, $key2);
        $this->assertStringContainsString('portal_api', $key1);
        $this->assertStringContainsString('test_segment', $key1);
    }

    public function test_remember_caches_and_returns_value(): void
    {
        $callCount = 0;
        $callback = function () use (&$callCount) {
            $callCount++;
            return 'computed_result';
        };

        $result1 = $this->cache->remember('unit_test', ['id' => 42], $callback);
        $result2 = $this->cache->remember('unit_test', ['id' => 42], $callback);

        $this->assertEquals('computed_result', $result1);
        $this->assertEquals('computed_result', $result2);
        $this->assertEquals(1, $callCount, 'Callback should only be executed once when cached.');
    }
}
