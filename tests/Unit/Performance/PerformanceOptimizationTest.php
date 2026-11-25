<?php

namespace Platform\Portal\Tests\Unit\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Platform\Portal\Helpers\PortalStorefront;
use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageSection;
use Platform\Portal\Models\PortalPageSectionTranslation;
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
     * Test that PortalStorefront::isEnabled() caches result in memory until clearMemo() is called.
     */
    public function test_storefront_status_memoizes_in_memory(): void
    {
        $originalConfig = config('portal.storefront.enabled');
        PortalStorefront::clearMemo();

        try {
            config()->set('portal.storefront.enabled', true);
            $this->assertTrue(PortalStorefront::isEnabled());

            // Change underlying config without clearing memo -> must retain memoized true
            config()->set('portal.storefront.enabled', false);
            $this->assertTrue(
                PortalStorefront::isEnabled(),
                'PortalStorefront::isEnabled() must return the memoized result until clearMemo() is called.'
            );

            // Clear memo -> must now reflect updated config (false)
            PortalStorefront::clearMemo();
            $this->assertFalse(
                PortalStorefront::isEnabled(),
                'PortalStorefront::isEnabled() must reflect updated configuration after clearMemo().'
            );
        } finally {
            config()->set('portal.storefront.enabled', $originalConfig);
            PortalStorefront::clearMemo();
        }
    }

    /**
     * Test that getRenderableSections eager-loads translations without triggering N+1 queries.
     */
    public function test_renderable_sections_eager_loads_translations(): void
    {
        $channel = core()->getCurrentChannel();

        $page = PortalPage::create([
            'slug' => 'perf-eager-test-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
        ]);

        try {
            $sectionSecond = PortalPageSection::create([
                'portal_page_id' => $page->id,
                'type' => 'rich_content',
                'name' => 'Second Section',
                'sort_order' => 2,
                'status' => 1,
            ]);

            PortalPageSectionTranslation::create([
                'portal_page_section_id' => $sectionSecond->id,
                'locale' => 'en',
                'options' => ['title' => 'Second Title'],
            ]);

            $sectionFirst = PortalPageSection::create([
                'portal_page_id' => $page->id,
                'type' => 'hero_scaffold',
                'name' => 'First Section',
                'sort_order' => 1,
                'status' => 1,
            ]);

            PortalPageSectionTranslation::create([
                'portal_page_section_id' => $sectionFirst->id,
                'locale' => 'en',
                'options' => ['title' => 'First Title'],
            ]);

            $sectionInactive = PortalPageSection::create([
                'portal_page_id' => $page->id,
                'type' => 'rich_content',
                'name' => 'Inactive Section',
                'sort_order' => 3,
                'status' => 0,
            ]);

            PortalPageSectionTranslation::create([
                'portal_page_section_id' => $sectionInactive->id,
                'locale' => 'en',
                'options' => ['title' => 'Hidden Title'],
            ]);

            $repo = app(PortalPageSectionRepository::class);

            DB::flushQueryLog();
            DB::enableQueryLog();

            $sections = $repo->getRenderableSections($page->id);
            $queriesAfterFetch = count(DB::getQueryLog());

            $this->assertCount(2, $sections, 'Only active sections for the page must be returned.');
            $this->assertSame([$sectionFirst->id, $sectionSecond->id], $sections->pluck('id')->all());

            foreach ($sections as $section) {
                $this->assertTrue(
                    $section->relationLoaded('translations'),
                    'Translations relation must be eager-loaded to prevent N+1 queries.'
                );
                $this->assertNotEmpty($section->translations->first()?->options['title'] ?? null);
            }

            $queriesAfterAccess = count(DB::getQueryLog());
            $this->assertSame(
                $queriesAfterFetch,
                $queriesAfterAccess,
                'Accessing section translations must not execute additional lazy-load queries.'
            );
        } finally {
            DB::disableQueryLog();
            $page->delete();
        }
    }
}
