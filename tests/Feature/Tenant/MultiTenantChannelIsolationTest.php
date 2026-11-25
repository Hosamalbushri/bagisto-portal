<?php

namespace Platform\Portal\Tests\Feature\Tenant;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Platform\Portal\Helpers\PortalCache;
use Platform\Portal\Helpers\PortalStorefront;
use Platform\Portal\Models\PortalMenu;
use Platform\Portal\Models\PortalMenuItem;
use Platform\Portal\Models\PortalMenuItemTranslation;
use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageSection;
use Platform\Portal\Models\PortalPageSectionTranslation;
use Platform\Portal\Models\PortalPageTranslation;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Repositories\PortalMenuRepository;
use Platform\Portal\Repositories\PortalPageRepository;
use Platform\Portal\Tests\TestCase;
use Webkul\Core\Core;
use Webkul\Core\Repositories\CoreConfigRepository;

class MultiTenantChannelIsolationTest extends TestCase
{
    protected object $activeChannel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureChannelsExist();
        $this->setActiveChannel(1, 'default', 'Default Store');

        PortalStorefront::clearMemo();
        PortalMenuRepository::clearCache();
        PortalLayoutSettingRepository::clearCache();

        if (app()->bound(PortalCache::class)) {
            app(PortalCache::class)->flush();
        }
    }

    protected function tearDown(): void
    {
        PortalStorefront::clearMemo();
        PortalMenuRepository::clearCache();
        PortalLayoutSettingRepository::clearCache();

        parent::tearDown();
    }

    public function test_same_slug_across_channels_and_wrong_channel_lookup_on_storefront_and_api(): void
    {
        $pageCh1 = $this->createPageWithSection('about', 1, 'About Channel 1', 'Channel One Exclusive Body');
        $pageCh2 = $this->createPageWithSection('about', 2, 'About Channel 2', 'Channel Two Exclusive Body');
        $onlyCh1 = $this->createPageWithSection('ch1-only', 1, 'Channel 1 Only', 'Only in Default');

        try {
            // Channel 1 resolves Channel 1's 'about' and 'ch1-only'
            $this->setActiveChannel(1, 'default', 'Default Store');

            $this->get(route('portal.page.show', 'about'))
                ->assertStatus(200)
                ->assertSee('Channel One Exclusive Body')
                ->assertDontSee('Channel Two Exclusive Body');

            $this->getJson(route('portal.api.pages.show', 'about'))
                ->assertStatus(200)
                ->assertJsonPath('data.id', $pageCh1->id)
                ->assertJsonPath('data.title', 'About Channel 1');

            // Switch to Channel 2: 'about' resolves Channel 2's page, 'ch1-only' returns 404
            $this->setActiveChannel(2, 'b2b', 'B2B Store');

            $this->get(route('portal.page.show', 'about'))
                ->assertStatus(200)
                ->assertSee('Channel Two Exclusive Body')
                ->assertDontSee('Channel One Exclusive Body');

            $this->getJson(route('portal.api.pages.show', 'about'))
                ->assertStatus(200)
                ->assertJsonPath('data.id', $pageCh2->id)
                ->assertJsonPath('data.title', 'About Channel 2');

            $this->get(route('portal.page.show', 'ch1-only'))
                ->assertStatus(404);

            $this->getJson(route('portal.api.pages.show', 'ch1-only'))
                ->assertStatus(404);
        } finally {
            $pageCh1->delete();
            $pageCh2->delete();
            $onlyCh1->delete();
        }
    }

    public function test_home_page_designation_and_resolution_are_isolated_per_channel(): void
    {
        $repo = app(PortalPageRepository::class);

        $homeCh1 = $repo->create([
            'slug' => 'home-ch1',
            'channel_id' => 1,
            'status' => 1,
            'is_home_page' => 1,
        ]);
        PortalPageTranslation::create([
            'portal_page_id' => $homeCh1->id,
            'locale' => 'en',
            'title' => 'Channel 1 Home',
        ]);
        $this->attachSection($homeCh1->id, 'Channel One Home Hero');

        $homeCh2 = $repo->create([
            'slug' => 'home-ch2',
            'channel_id' => 2,
            'status' => 1,
            'is_home_page' => 1,
        ]);
        PortalPageTranslation::create([
            'portal_page_id' => $homeCh2->id,
            'locale' => 'en',
            'title' => 'Channel 2 Home',
        ]);
        $this->attachSection($homeCh2->id, 'Channel Two Home Hero');

        try {
            // Setting Channel 2's home page must NOT unset Channel 1's home page
            $this->assertTrue((bool) PortalPage::findOrFail($homeCh1->id)->is_home_page);
            $this->assertTrue((bool) PortalPage::findOrFail($homeCh2->id)->is_home_page);

            $this->assertSame($homeCh1->id, $repo->findHomePage(1)?->id);
            $this->assertSame($homeCh2->id, $repo->findHomePage(2)?->id);

            $this->setActiveChannel(1, 'default', 'Default Store');
            $this->get(route('portal.home'))
                ->assertStatus(200)
                ->assertSee('Channel One Home Hero')
                ->assertDontSee('Channel Two Home Hero');

            $this->setActiveChannel(2, 'b2b', 'B2B Store');
            $this->get(route('portal.home'))
                ->assertStatus(200)
                ->assertSee('Channel Two Home Hero')
                ->assertDontSee('Channel One Home Hero');
        } finally {
            $homeCh1->delete();
            $homeCh2->delete();
        }
    }

    public function test_search_and_sitemap_return_only_active_channel_pages(): void
    {
        $ch1Page = $this->createPageWithSection('tenant-alpha-offer', 1, 'Alpha Enterprise Plan', 'Alpha body');
        $ch2Page = $this->createPageWithSection('tenant-beta-offer', 2, 'Beta Enterprise Plan', 'Beta body');

        try {
            // Channel 1 search & sitemap
            $this->setActiveChannel(1, 'default', 'Default Store');

            $searchCh1 = $this->getJson('/search?query=Enterprise');
            $searchCh1->assertStatus(200);
            $slugsCh1 = collect($searchCh1->json('data'))->pluck('slug')->all();
            $this->assertContains('tenant-alpha-offer', $slugsCh1);
            $this->assertNotContains('tenant-beta-offer', $slugsCh1);

            $sitemapCh1 = $this->get('/sitemap.xml');
            $sitemapCh1->assertStatus(200);
            $this->assertStringContainsString('tenant-alpha-offer', $sitemapCh1->getContent());
            $this->assertStringNotContainsString('tenant-beta-offer', $sitemapCh1->getContent());

            // Channel 2 search & sitemap
            $this->setActiveChannel(2, 'b2b', 'B2B Store');

            $searchCh2 = $this->getJson('/search?query=Enterprise');
            $searchCh2->assertStatus(200);
            $slugsCh2 = collect($searchCh2->json('data'))->pluck('slug')->all();
            $this->assertContains('tenant-beta-offer', $slugsCh2);
            $this->assertNotContains('tenant-alpha-offer', $slugsCh2);

            $sitemapCh2 = $this->get('/sitemap.xml');
            $sitemapCh2->assertStatus(200);
            $this->assertStringContainsString('tenant-beta-offer', $sitemapCh2->getContent());
            $this->assertStringNotContainsString('tenant-alpha-offer', $sitemapCh2->getContent());
        } finally {
            $ch1Page->delete();
            $ch2Page->delete();
        }
    }

    public function test_caches_isolate_distinct_channel_data_when_switching_channels_in_process(): void
    {
        $pageCh1 = $this->createPageWithSection('cached-guide', 1, 'Guide CH1', 'CH1 Guide');
        $pageCh2 = $this->createPageWithSection('cached-guide', 2, 'Guide CH2', 'CH2 Guide');
        $menuCh1 = $this->createMenuWithItem('Menu CH1', 'main-nav', 1, 'CH1 Nav Item');
        $menuCh2 = $this->createMenuWithItem('Menu CH2', 'main-nav', 2, 'CH2 Nav Item');

        try {
            // 1. PortalCache isolation across channel switches
            $portalCache = app(PortalCache::class);
            $this->setActiveChannel(1, 'default', 'Default Store');
            $cached1 = $portalCache->remember('tenant_probe', ['slug' => 'cached-guide'], fn () => 'payload-ch1');
            $this->assertSame('payload-ch1', $cached1);

            $this->setActiveChannel(2, 'b2b', 'B2B Store');
            $cached2 = $portalCache->remember('tenant_probe', ['slug' => 'cached-guide'], fn () => 'payload-ch2');
            $this->assertSame('payload-ch2', $cached2);

            // API endpoint cache isolation
            $this->setActiveChannel(1, 'default', 'Default Store');
            $this->getJson(route('portal.api.pages.show', 'cached-guide'))
                ->assertStatus(200)
                ->assertJsonPath('data.title', 'Guide CH1');

            $this->setActiveChannel(2, 'b2b', 'B2B Store');
            $this->getJson(route('portal.api.pages.show', 'cached-guide'))
                ->assertStatus(200)
                ->assertJsonPath('data.title', 'Guide CH2');

            // 2. PortalMenuRepository tree cache isolation
            $menuRepo = app(PortalMenuRepository::class);
            $tree1 = $menuRepo->getActiveTree('main-nav', 1, 'en');
            $tree2 = $menuRepo->getActiveTree('main-nav', 2, 'en');
            $this->assertSame('CH1 Nav Item', $tree1?->rootItems->first()?->translate('en')?->title);
            $this->assertSame('CH2 Nav Item', $tree2?->rootItems->first()?->translate('en')?->title);

            // 3. PortalLayoutSettingRepository cache isolation + scoped invalidation
            $layoutRepo = app(PortalLayoutSettingRepository::class);
            $layoutRepo->saveSettings('header', ['cta_text' => 'CH1 CTA'], 1, 'portal', 'en');
            $layoutRepo->saveSettings('header', ['cta_text' => 'CH2 CTA'], 2, 'portal', 'en');

            $this->assertSame('CH1 CTA', $layoutRepo->getSettings('header', 1, 'portal', 'en')['cta_text'] ?? null);
            $this->assertSame('CH2 CTA', $layoutRepo->getSettings('header', 2, 'portal', 'en')['cta_text'] ?? null);

            $layoutRepo->saveSettings('header', ['cta_text' => 'CH1 CTA Updated'], 1, 'portal', 'en');
            $this->assertSame('CH1 CTA Updated', $layoutRepo->getSettings('header', 1, 'portal', 'en')['cta_text'] ?? null);
            $this->assertSame('CH2 CTA', $layoutRepo->getSettings('header', 2, 'portal', 'en')['cta_text'] ?? null);

            // 4. PortalStorefront in-memory memoization isolation per channel
            $this->app->instance(CoreConfigRepository::class, new class extends CoreConfigRepository
            {
                public function findOneWhere(array $where, $columns = ['*'])
                {
                    if (($where['code'] ?? null) === 'portal.general.storefront.enabled') {
                        return match ($where['channel_code'] ?? null) {
                            'default' => (object) ['value' => '1'],
                            'b2b' => (object) ['value' => '0'],
                            default => null,
                        };
                    }

                    return null;
                }
            });

            PortalStorefront::clearMemo();

            $this->setActiveChannel(1, 'default', 'Default Store');
            $this->assertTrue(PortalStorefront::isEnabled());

            $this->setActiveChannel(2, 'b2b', 'B2B Store');
            $this->assertFalse(PortalStorefront::isEnabled());

            // Switching back to Channel 1 reads Channel 1's memoized value, not Channel 2's
            $this->setActiveChannel(1, 'default', 'Default Store');
            $this->assertTrue(PortalStorefront::isEnabled());
        } finally {
            $pageCh1->delete();
            $pageCh2->delete();
            $menuCh1->delete();
            $menuCh2->delete();
        }
    }

    protected function setActiveChannel(int $id, string $code, string $name): void
    {
        $this->activeChannel = (object) [
            'id' => $id,
            'code' => $code,
            'name' => $name,
            'theme' => 'portal',
            'logo_url' => null,
            'favicon_url' => null,
            'locales' => collect([(object) ['code' => 'en', 'name' => 'English']]),
            'currencies' => collect([(object) ['code' => 'USD']]),
            'default_locale' => (object) ['code' => 'en'],
            'base_currency' => (object) ['code' => 'USD'],
        ];

        $test = $this;

        $this->app->instance(Core::class, new class($test) extends Core
        {
            public function __construct(protected MultiTenantChannelIsolationTest $test) {}

            public function getCurrentChannel()
            {
                return $this->test->getActiveChannelObject();
            }

            public function getAllChannels()
            {
                return collect([
                    (object) [
                        'id' => 1,
                        'code' => 'default',
                        'name' => 'Default Store',
                        'theme' => 'portal',
                        'locales' => collect([(object) ['code' => 'en', 'name' => 'English']]),
                        'default_locale' => (object) ['code' => 'en'],
                    ],
                    (object) [
                        'id' => 2,
                        'code' => 'b2b',
                        'name' => 'B2B Store',
                        'theme' => 'portal',
                        'locales' => collect([(object) ['code' => 'en', 'name' => 'English']]),
                        'default_locale' => (object) ['code' => 'en'],
                    ],
                ]);
            }
        });
    }

    public function getActiveChannelObject(): object
    {
        return $this->activeChannel;
    }

    protected function ensureChannelsExist(): void
    {
        if (! Schema::hasTable('channels')) {
            Schema::create('channels', function ($table) {
                $table->increments('id');
                $table->string('code')->unique();
            });
        }

        foreach ([1 => 'default', 2 => 'b2b'] as $id => $code) {
            if (! DB::table('channels')->where('id', $id)->exists()) {
                DB::table('channels')->insert(['id' => $id, 'code' => $code]);
            }
        }
    }

    protected function createPageWithSection(string $slug, int $channelId, string $title, string $body): PortalPage
    {
        $page = PortalPage::create([
            'slug' => $slug,
            'channel_id' => $channelId,
            'status' => 1,
        ]);

        PortalPageTranslation::create([
            'portal_page_id' => $page->id,
            'locale' => 'en',
            'title' => $title,
        ]);

        $this->attachSection($page->id, $body);

        return $page->fresh();
    }

    protected function attachSection(int $pageId, string $body): PortalPageSection
    {
        $section = PortalPageSection::create([
            'portal_page_id' => $pageId,
            'type' => 'rich_content',
            'name' => 'Section',
            'sort_order' => 1,
            'status' => 1,
        ]);

        PortalPageSectionTranslation::create([
            'portal_page_section_id' => $section->id,
            'locale' => 'en',
            'options' => [
                'title' => $body,
                'html' => "<p>{$body}</p>",
            ],
        ]);

        return $section->fresh();
    }

    protected function createMenuWithItem(string $name, string $slug, int $channelId, string $itemTitle): PortalMenu
    {
        $menu = PortalMenu::create([
            'name' => $name,
            'slug' => $slug,
            'channel_id' => $channelId,
            'status' => 1,
        ]);

        $item = PortalMenuItem::create([
            'portal_menu_id' => $menu->id,
            'type' => 'custom',
            'url' => '/test',
            'target' => '_self',
            'sort_order' => 0,
            'status' => 1,
        ]);

        PortalMenuItemTranslation::create([
            'portal_menu_item_id' => $item->id,
            'locale' => 'en',
            'title' => $itemTitle,
        ]);

        return $menu->fresh();
    }
}
