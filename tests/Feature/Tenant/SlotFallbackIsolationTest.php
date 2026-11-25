<?php

namespace Platform\Portal\Tests\Feature\Tenant;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Platform\Portal\Models\PortalMenu;
use Platform\Portal\Models\PortalMenuItem;
use Platform\Portal\Models\PortalMenuItemTranslation;
use Platform\Portal\Models\PortalMenuSlotAssignment;
use Platform\Portal\Repositories\PortalMenuRepository;
use Platform\Portal\Repositories\PortalMenuSlotAssignmentRepository;
use Platform\Portal\Tests\TestCase;
use Webkul\Core\Core;

class SlotFallbackIsolationTest extends TestCase
{
    protected PortalMenuSlotAssignmentRepository $slotRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureChannelsExist();
        $this->bindMultiChannelCore();
        $this->slotRepo = app(PortalMenuSlotAssignmentRepository::class);
        PortalMenuRepository::clearCache();
    }

    public function test_secondary_channel_does_not_inherit_default_channel_slot_assignment(): void
    {
        $menu = $this->createMenuWithItem('Default Footer', 'default-footer', null, 'Default Link');

        $ch1Assignment = PortalMenuSlotAssignment::create([
            'slot_id' => 'footer_col_1',
            'location' => 'footer',
            'portal_menu_id' => $menu->id,
            'channel_id' => 1,
            'theme_code' => 'portal',
            'locale_code' => null,
            'status' => 1,
        ]);

        try {
            $resolvedCh1 = $this->slotRepo->findForSlot('footer_col_1', 1, 'portal', 'en');
            $this->assertNotNull($resolvedCh1);
            $this->assertSame($ch1Assignment->id, $resolvedCh1->id);

            $resolvedCh2 = $this->slotRepo->findForSlot('footer_col_1', 2, 'portal', 'en');
            $this->assertNull($resolvedCh2);

            $this->assertNull(portal_menu_slot('footer_col_1', 2, 'portal'));
        } finally {
            $ch1Assignment->delete();
            $menu->delete();
        }
    }

    public function test_same_channel_locale_and_theme_fallbacks_are_preserved(): void
    {
        $menu = $this->createMenuWithItem('Channel 2 Nav', 'ch2-nav', 2, 'CH2 Item');

        $ch2DefaultTheme = PortalMenuSlotAssignment::create([
            'slot_id' => 'footer_links',
            'location' => 'footer',
            'portal_menu_id' => $menu->id,
            'channel_id' => 2,
            'theme_code' => 'default',
            'locale_code' => null,
            'status' => 1,
        ]);

        try {
            // Same-channel theme fallback ('corporate' -> 'default') + locale fallback ('ar' -> null)
            $resolved = $this->slotRepo->findForSlot('footer_links', 2, 'corporate', 'ar');
            $this->assertNotNull($resolved);
            $this->assertSame($ch2DefaultTheme->id, $resolved->id);
            $this->assertSame(2, (int) $resolved->channel_id);
        } finally {
            $ch2DefaultTheme->delete();
            $menu->delete();
        }
    }

    public function test_stale_cached_slot_fallback_is_evicted_after_cache_invalidation(): void
    {
        $menu = $this->createMenuWithItem('Default Slot Menu', 'stale-slot-menu', null, 'Stale Item');

        $ch1Assignment = PortalMenuSlotAssignment::create([
            'slot_id' => 'footer_about',
            'location' => 'footer',
            'portal_menu_id' => $menu->id,
            'channel_id' => 1,
            'theme_code' => 'portal',
            'locale_code' => 'en',
            'status' => 1,
        ]);

        try {
            $vBefore = Cache::get(PortalMenuRepository::CACHE_VERSION_KEY, 1);
            $staleKey = "portal_slot_v{$vBefore}_footer_about_2_portal_en";
            Cache::put($staleKey, $ch1Assignment, now()->addDay());

            // Simulate cache invalidation upon deploy / assignment save
            PortalMenuRepository::clearCache();
            $vAfter = Cache::get(PortalMenuRepository::CACHE_VERSION_KEY, 1);
            $this->assertNotSame($vBefore, $vAfter);

            // Channel 2 must NOT receive the stale Channel 1 assignment
            $this->assertNull($this->slotRepo->findForSlot('footer_about', 2, 'portal', 'en'));

            // Channel 1 remains unaffected
            $ch1Resolved = $this->slotRepo->findForSlot('footer_about', 1, 'portal', 'en');
            $this->assertNotNull($ch1Resolved);
            $this->assertSame($ch1Assignment->id, $ch1Resolved->id);
        } finally {
            $ch1Assignment->delete();
            $menu->delete();
        }
    }

    protected function bindMultiChannelCore(): void
    {
        $this->app->instance(Core::class, new class extends Core
        {
            public function getDefaultChannel()
            {
                return (object) [
                    'id' => 1,
                    'code' => 'default',
                    'name' => 'Default Store',
                    'theme' => 'portal',
                    'default_locale' => (object) ['code' => 'en'],
                ];
            }

            public function getCurrentChannel()
            {
                return $this->getDefaultChannel();
            }

            public function getAllChannels()
            {
                return collect([
                    $this->getDefaultChannel(),
                    (object) [
                        'id' => 2,
                        'code' => 'b2b',
                        'name' => 'B2B Store',
                        'theme' => 'portal',
                        'default_locale' => (object) ['code' => 'en'],
                    ],
                ]);
            }
        });
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
