<?php

namespace Platform\Portal\Tests\Feature\Tenant;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Platform\Portal\Helpers\PortalCache;
use Platform\Portal\Models\PortalMenu;
use Platform\Portal\Models\PortalMenuItem;
use Platform\Portal\Models\PortalMenuItemTranslation;
use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageTranslation;
use Platform\Portal\Repositories\PortalMenuRepository;
use Platform\Portal\Tests\TestCase;

class MenuUnpublishedPageFilteringTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureChannelsExist();
        PortalMenuRepository::clearCache();

        if (app()->bound(PortalCache::class)) {
            app(PortalCache::class)->flush();
        }
    }

    public function test_html_and_api_filter_unpublished_and_missing_pages_consistently(): void
    {
        $publishedPage = $this->createPage('published-leaf', 1, 1, 'Published Leaf');
        $unpublishedPage = $this->createPage('secret-unpublished', 1, 0, 'Secret Unpublished');
        $parentUnpublishedPage = $this->createPage('hidden-parent-slug', 1, 0, 'Hidden Parent');
        $publishedChildPage = $this->createPage('visible-child-slug', 1, 1, 'Visible Child');
        $unpublishedParentOnlyHidden = $this->createPage('all-hidden-parent', 1, 0, 'All Hidden Parent');
        $unpublishedChildPage = $this->createPage('all-hidden-child', 1, 0, 'All Hidden Child');

        $menu = PortalMenu::create([
            'name' => 'Filtered Nav',
            'slug' => 'filtered-nav',
            'channel_id' => 1,
            'status' => 1,
        ]);

        try {
            // 1. Published leaf
            $this->createItem($menu->id, null, 'page', $publishedPage->id, null, 0, 'Published Item');
            // 2. Unpublished leaf (must be omitted)
            $this->createItem($menu->id, null, 'page', $unpublishedPage->id, null, 1, 'Unpublished Item');
            // 3. Missing/deleted page leaf (portal_page_id = null with type = page, must be omitted)
            $this->createItem($menu->id, null, 'page', null, null, 2, 'Deleted Page Item');
            // 4. URL-only leaf (must be preserved)
            $this->createItem($menu->id, null, 'custom', null, '/docs', 3, 'Docs URL Item');
            // 5. Unpublished parent with published child (parent preserved as non-navigable group '#', no View All)
            $groupParent = $this->createItem($menu->id, null, 'page', $parentUnpublishedPage->id, null, 4, 'Solutions Group');
            $this->createItem($menu->id, $groupParent->id, 'page', $publishedChildPage->id, null, 0, 'Enterprise Child');
            // 6. Unpublished parent with only unpublished children (entire branch omitted)
            $hiddenBranch = $this->createItem($menu->id, null, 'page', $unpublishedParentOnlyHidden->id, null, 5, 'Hidden Branch');
            $this->createItem($menu->id, $hiddenBranch->id, 'page', $unpublishedChildPage->id, null, 0, 'Hidden Leaf');
            // 7. Nested mixed hierarchy (custom root -> unpublished mid -> published grandchild)
            $customRoot = $this->createItem($menu->id, null, 'custom', null, '/platform', 6, 'Platform Root');
            $midUnpublished = $this->createItem($menu->id, $customRoot->id, 'page', $unpublishedPage->id, null, 0, 'Mid Group');
            $this->createItem($menu->id, $midUnpublished->id, 'page', $publishedPage->id, null, 0, 'Deep Published');

            // Render HTML (desktop row + mobile column for View All check)
            $loadedMenu = app(PortalMenuRepository::class)->getActiveTree('filtered-nav', 1, 'en');
            $this->assertNotNull($loadedMenu);

            $rowHtml = Blade::render('<x-portal::menu :menu="$menu" direction="row" />', ['menu' => $loadedMenu]);
            $columnHtml = Blade::render('<x-portal::menu :menu="$menu" direction="column" />', ['menu' => $loadedMenu]);

            foreach ([$rowHtml, $columnHtml] as $html) {
                $this->assertStringContainsString('Published Item', $html);
                $this->assertStringContainsString('/pages/published-leaf', $html);
                $this->assertStringContainsString('Docs URL Item', $html);
                $this->assertStringContainsString('/docs', $html);
                $this->assertStringContainsString('Solutions Group', $html);
                $this->assertStringContainsString('Enterprise Child', $html);
                $this->assertStringContainsString('/pages/visible-child-slug', $html);
                $this->assertStringContainsString('Platform Root', $html);
                $this->assertStringContainsString('Mid Group', $html);
                $this->assertStringContainsString('Deep Published', $html);

                // Must NOT leak unpublished or deleted items/URLs
                $this->assertStringNotContainsString('Unpublished Item', $html);
                $this->assertStringNotContainsString('secret-unpublished', $html);
                $this->assertStringNotContainsString('Deleted Page Item', $html);
                $this->assertStringNotContainsString('hidden-parent-slug', $html);
                $this->assertStringNotContainsString('Hidden Branch', $html);
                $this->assertStringNotContainsString('Hidden Leaf', $html);
                $this->assertStringNotContainsString('all-hidden-parent', $html);
                $this->assertStringNotContainsString('all-hidden-child', $html);
            }

            // Column accordion must NOT render "View All Solutions Group" for non-navigable group
            $this->assertStringNotContainsString('View All Solutions Group', $columnHtml);

            // Query API and verify parity + bounded query count (no N+1)
            DB::flushQueryLog();
            DB::enableQueryLog();
            $apiResponse = $this->getJson('/api/portal/menus/filtered-nav');
            $queries = DB::getQueryLog();
            DB::disableQueryLog();

            $apiResponse->assertStatus(200);
            $this->assertLessThanOrEqual(16, count($queries), 'Nested menu API serialization must stay bounded.');
            foreach ($queries as $q) {
                $this->assertStringNotContainsString(
                    'where "portal_pages"."id" = ?',
                    strtolower($q['query'] ?? ''),
                    'Nested menu API serialization must not lazy-load individual portal_pages rows (N+1).'
                );
            }

            $items = $apiResponse->json('data.items');
            $titles = collect($items)->pluck('title')->all();

            $this->assertSame(['Published Item', 'Docs URL Item', 'Solutions Group', 'Platform Root'], $titles);

            $groupItem = collect($items)->firstWhere('title', 'Solutions Group');
            $this->assertSame('#', $groupItem['url']);
            $this->assertSame(['Enterprise Child'], collect($groupItem['children'])->pluck('title')->all());
            $this->assertStringEndsWith('/pages/visible-child-slug', $groupItem['children'][0]['url']);

            $platformItem = collect($items)->firstWhere('title', 'Platform Root');
            $this->assertSame(['Mid Group'], collect($platformItem['children'])->pluck('title')->all());
            $this->assertSame('#', $platformItem['children'][0]['url']);
            $this->assertSame(['Deep Published'], collect($platformItem['children'][0]['children'])->pluck('title')->all());

            // Confirm persisted DB records were not mutated by filtering
            $this->assertSame(11, PortalMenuItem::where('portal_menu_id', $menu->id)->count());
        } finally {
            $menu->delete();
            PortalPage::whereIn('id', [
                $publishedPage->id,
                $unpublishedPage->id,
                $parentUnpublishedPage->id,
                $publishedChildPage->id,
                $unpublishedParentOnlyHidden->id,
                $unpublishedChildPage->id,
            ])->delete();
        }
    }

    public function test_menu_cache_invalidates_after_page_update_mass_update_publish_and_delete(): void
    {
        $page = $this->createPage('toggle-vis-page', 1, 1, 'Toggle Page');
        $otherPage = $this->createPage('keep-vis-page', 1, 1, 'Keep Page');

        $menu = PortalMenu::create([
            'name' => 'Cache Inv Nav',
            'slug' => 'cache-inv-nav',
            'channel_id' => 1,
            'status' => 1,
        ]);

        $this->createItem($menu->id, null, 'page', $page->id, null, 0, 'Toggle Nav Item');
        $this->createItem($menu->id, null, 'page', $otherPage->id, null, 1, 'Keep Nav Item');

        $admin = $this->makeAdminUser(['portal.pages.edit', 'portal.pages.delete']);

        try {
            $this->actingAs($admin, 'admin');

            // Warm both PortalMenuRepository cache & PortalCache (API)
            $warmTree = app(PortalMenuRepository::class)->getActiveTree('cache-inv-nav', 1, 'en');
            $this->assertCount(2, $warmTree->rootItems);

            $this->getJson('/api/portal/menus/cache-inv-nav')
                ->assertStatus(200)
                ->assertJsonCount(2, 'data.items');

            // 1. Normal page update: unpublish $page
            $this->putJson(route('admin.portal.pages.update', $page->id), [
                'slug' => $page->slug,
                'channel_id' => 1,
                'status' => '0',
                'title' => 'Toggle Page',
                'locale' => 'en',
            ])->assertStatus(200);

            $afterUnpublish = $this->getJson('/api/portal/menus/cache-inv-nav');
            $afterUnpublish->assertStatus(200);
            $this->assertSame(['Keep Nav Item'], collect($afterUnpublish->json('data.items'))->pluck('title')->all());

            // 2. massUpdate: re-publish $page
            $this->postJson(route('admin.portal.pages.mass_update'), [
                'indices' => [$page->id],
                'value' => '1',
            ])->assertStatus(200);

            $afterMassPublish = $this->getJson('/api/portal/menus/cache-inv-nav');
            $afterMassPublish->assertStatus(200);
            $this->assertSame(['Toggle Nav Item', 'Keep Nav Item'], collect($afterMassPublish->json('data.items'))->pluck('title')->all());

            // 3. portal.page.published event invalidates menu cache
            $vBeforePub = Cache::get(PortalMenuRepository::CACHE_VERSION_KEY, 1);
            Event::dispatch('portal.page.published', $page->id);
            $vAfterPub = Cache::get(PortalMenuRepository::CACHE_VERSION_KEY, 1);
            $this->assertNotSame($vBeforePub, $vAfterPub);

            // 4. Delete page invalidates menu cache & omits deleted page item
            $this->deleteJson(route('admin.portal.pages.delete', $page->id))->assertStatus(200);

            $afterDelete = $this->getJson('/api/portal/menus/cache-inv-nav');
            $afterDelete->assertStatus(200);
            $this->assertSame(['Keep Nav Item'], collect($afterDelete->json('data.items'))->pluck('title')->all());
        } finally {
            $menu->delete();
            PortalPage::whereIn('id', [$page->id, $otherPage->id])->delete();
        }
    }

    protected function ensureChannelsExist(): void
    {
        if (! Schema::hasTable('channels')) {
            Schema::create('channels', function ($table) {
                $table->increments('id');
                $table->string('code')->unique();
            });
        }

        if (! DB::table('channels')->where('id', 1)->exists()) {
            DB::table('channels')->insert(['id' => 1, 'code' => 'default']);
        }
    }

    protected function makeAdminUser(array $permissions): User
    {
        $user = new User;
        $user->id = 1004;
        $user->name = 'Menu Filter Admin';
        $user->email = 'menuadmin@example.com';
        $user->setAttribute('role', (object) [
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $user;
    }

    protected function createPage(string $slug, int $channelId, int $status, string $title): PortalPage
    {
        $page = PortalPage::create([
            'slug' => $slug,
            'channel_id' => $channelId,
            'status' => $status,
        ]);

        PortalPageTranslation::create([
            'portal_page_id' => $page->id,
            'locale' => 'en',
            'title' => $title,
        ]);

        return $page->fresh();
    }

    protected function createItem(
        int $menuId,
        ?int $parentId,
        string $type,
        ?int $pageId,
        ?string $url,
        int $sortOrder,
        string $title
    ): PortalMenuItem {
        $item = PortalMenuItem::create([
            'portal_menu_id' => $menuId,
            'parent_id' => $parentId,
            'type' => $type,
            'portal_page_id' => $pageId,
            'url' => $url,
            'target' => '_self',
            'sort_order' => $sortOrder,
            'status' => 1,
        ]);

        PortalMenuItemTranslation::create([
            'portal_menu_item_id' => $item->id,
            'locale' => 'en',
            'title' => $title,
        ]);

        return $item;
    }
}
