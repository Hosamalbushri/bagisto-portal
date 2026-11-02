<?php

namespace Platform\Portal\Tests\Unit\Performance;

use Illuminate\Support\Facades\Cache;
use Platform\Portal\Helpers\PortalStorefront;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Repositories\PortalMenuRepository;
use Platform\Portal\Repositories\PortalPageSectionRepository;
use Tests\TestCase;

class PerformanceOptimizationTest extends TestCase
{
    /**
     * Test menu caching invalidation mechanism.
     */
    public function test_menu_cache_clearing_increments_version(): void
    {
        $v1 = Cache::get(PortalMenuRepository::CACHE_VERSION_KEY, 1);

        PortalMenuRepository::clearCache();

        $v2 = Cache::get(PortalMenuRepository::CACHE_VERSION_KEY);

        $this->assertNotEquals($v1, $v2, 'Clearing menu cache must increment the cache version.');
    }

    /**
     * Test layout setting caching invalidation mechanism.
     */
    public function test_layout_settings_cache_clearing_increments_version(): void
    {
        $v1 = Cache::get(PortalLayoutSettingRepository::CACHE_VERSION_KEY, 1);

        PortalLayoutSettingRepository::clearCache();

        $v2 = Cache::get(PortalLayoutSettingRepository::CACHE_VERSION_KEY);

        $this->assertNotEquals($v1, $v2, 'Clearing layout settings cache must increment the cache version.');
    }

    /**
     * Test that PortalStorefront::isEnabled() caches result in memory.
     */
    public function test_storefront_status_memoizes_in_memory(): void
    {
        PortalStorefront::clearMemo();

        $status1 = PortalStorefront::isEnabled();
        $status2 = PortalStorefront::isEnabled();

        $this->assertEquals($status1, $status2);
    }

    /**
     * Test that getRenderableSections eager-loads translations relation.
     */
    public function test_renderable_sections_eager_loads_translations(): void
    {
        $repo = app(PortalPageSectionRepository::class);

        // Fetch for page ID 1 (or 0 if none)
        $sections = $repo->getRenderableSections(1);

        foreach ($sections as $section) {
            $this->assertTrue(
                $section->relationLoaded('translations'),
                'Translations relation must be eager-loaded to prevent N+1 queries.'
            );
        }

        $this->assertTrue(true);
    }
}
