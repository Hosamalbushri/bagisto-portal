<?php

namespace Platform\Portal\Tests\Feature\Tenant;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Platform\Portal\Models\PortalMenu;
use Platform\Portal\Models\PortalMenuItem;
use Platform\Portal\Models\PortalMenuItemTranslation;
use Platform\Portal\Repositories\PortalMenuRepository;
use Platform\Portal\Tests\TestCase;

class MenuChannelPrecedenceTest extends TestCase
{
    protected PortalMenuRepository $menuRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureChannelsExist();
        $this->menuRepository = app(PortalMenuRepository::class);
        PortalMenuRepository::clearCache();
    }

    public function test_a1_channel_specific_menu_overrides_global_menu_when_global_inserted_first(): void
    {
        $globalMenu = $this->createMenuWithItem('Global Header', 'header-nav', null, 'Global Link');
        $channelMenu = $this->createMenuWithItem('Channel 2 Header', 'header-nav', 2, 'Channel 2 Link');

        try {
            $found = $this->menuRepository->findBySlug('header-nav', 2);
            $this->assertNotNull($found);
            $this->assertSame($channelMenu->id, $found->id);
            $this->assertSame(2, (int) $found->channel_id);

            $tree = $this->menuRepository->getActiveTree('header-nav', 2, 'en');
            $this->assertNotNull($tree);
            $this->assertSame($channelMenu->id, $tree->id);
            $this->assertSame('Channel 2 Link', $tree->rootItems->first()?->translate('en')?->title);
        } finally {
            $globalMenu->delete();
            $channelMenu->delete();
        }
    }

    public function test_a2_global_menu_is_used_as_fallback_when_channel_has_no_override(): void
    {
        $globalMenu = $this->createMenuWithItem('Global Footer', 'footer-nav', null, 'Global Footer Link');

        try {
            $found = $this->menuRepository->findBySlug('footer-nav', 2);
            $this->assertNotNull($found);
            $this->assertSame($globalMenu->id, $found->id);
            $this->assertNull($found->channel_id);

            $tree = $this->menuRepository->getActiveTree('footer-nav', 2, 'en');
            $this->assertNotNull($tree);
            $this->assertSame($globalMenu->id, $tree->id);
            $this->assertSame('Global Footer Link', $tree->rootItems->first()?->translate('en')?->title);
        } finally {
            $globalMenu->delete();
        }
    }

    public function test_a3_channel_specific_menu_still_wins_when_inserted_before_global_menu(): void
    {
        $channelMenu = $this->createMenuWithItem('Channel 2 First', 'reverse-order-nav', 2, 'Channel 2 Item');
        $globalMenu = $this->createMenuWithItem('Global Second', 'reverse-order-nav', null, 'Global Item');

        try {
            $found = $this->menuRepository->findBySlug('reverse-order-nav', 2);
            $this->assertNotNull($found);
            $this->assertSame($channelMenu->id, $found->id);

            $tree = $this->menuRepository->getActiveTree('reverse-order-nav', 2, 'en');
            $this->assertNotNull($tree);
            $this->assertSame($channelMenu->id, $tree->id);
            $this->assertSame('Channel 2 Item', $tree->rootItems->first()?->translate('en')?->title);
        } finally {
            $channelMenu->delete();
            $globalMenu->delete();
        }
    }

    public function test_a4_two_channels_with_same_slug_resolve_their_own_menus(): void
    {
        $channel1Menu = $this->createMenuWithItem('Channel 1 Nav', 'shared-nav', 1, 'Channel 1 Home');
        $channel2Menu = $this->createMenuWithItem('Channel 2 Nav', 'shared-nav', 2, 'Channel 2 Home');

        try {
            $tree1 = $this->menuRepository->getActiveTree('shared-nav', 1, 'en');
            $tree2 = $this->menuRepository->getActiveTree('shared-nav', 2, 'en');

            $this->assertNotNull($tree1);
            $this->assertNotNull($tree2);
            $this->assertSame($channel1Menu->id, $tree1->id);
            $this->assertSame($channel2Menu->id, $tree2->id);
            $this->assertSame('Channel 1 Home', $tree1->rootItems->first()?->translate('en')?->title);
            $this->assertSame('Channel 2 Home', $tree2->rootItems->first()?->translate('en')?->title);
        } finally {
            $channel1Menu->delete();
            $channel2Menu->delete();
        }
    }

    public function test_a5_nonexistent_or_other_channel_menu_returns_null(): void
    {
        $channel1Menu = $this->createMenuWithItem('Channel 1 Only', 'only-ch1', 1, 'Ch1 Item');

        try {
            $this->assertNull($this->menuRepository->findBySlug('only-ch1', 2));
            $this->assertNull($this->menuRepository->getActiveTree('only-ch1', 2, 'en'));
            $this->assertNull($this->menuRepository->findBySlug('does-not-exist', 1));
            $this->assertNull($this->menuRepository->getActiveTree('does-not-exist', 1, 'en'));
        } finally {
            $channel1Menu->delete();
        }
    }

    public function test_find_by_slug_and_get_active_tree_emit_explicit_channel_ordering_clause(): void
    {
        $globalMenu = $this->createMenuWithItem('Global Contract', 'contract-nav', null, 'Global');
        $channelMenu = $this->createMenuWithItem('Channel Contract', 'contract-nav', 2, 'Channel');

        try {
            PortalMenuRepository::clearCache();
            DB::flushQueryLog();
            DB::enableQueryLog();

            $this->menuRepository->findBySlug('contract-nav', 2);
            $findQueries = collect(DB::getQueryLog())
                ->pluck('query')
                ->filter(fn (string $sql) => str_contains(strtolower($sql), 'portal_menus'))
                ->values();

            $this->assertNotEmpty($findQueries);
            $findSql = strtolower($findQueries->first());
            $this->assertStringContainsString('order by', $findSql);
            $this->assertMatchesRegularExpression('/order by\b.*\bchannel_id\b/', $findSql);

            DB::flushQueryLog();
            $this->menuRepository->getActiveTree('contract-nav', 2, 'en');
            $treeQueries = collect(DB::getQueryLog())
                ->pluck('query')
                ->filter(fn (string $sql) => str_contains(strtolower($sql), 'portal_menus'))
                ->values();

            $this->assertNotEmpty($treeQueries);
            $treeSql = strtolower($treeQueries->first());
            $this->assertStringContainsString('order by', $treeSql);
            $this->assertMatchesRegularExpression('/order by\b.*\bchannel_id\b/', $treeSql);
        } finally {
            DB::disableQueryLog();
            $globalMenu->delete();
            $channelMenu->delete();
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

        foreach ([1 => 'default', 2 => 'b2b'] as $id => $code) {
            if (! DB::table('channels')->where('id', $id)->exists()) {
                DB::table('channels')->insert(['id' => $id, 'code' => $code]);
            }
        }
    }

    protected function createMenuWithItem(string $name, string $slug, ?int $channelId, string $itemTitle): PortalMenu
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
