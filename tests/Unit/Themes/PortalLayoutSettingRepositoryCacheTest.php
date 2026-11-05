<?php

namespace Platform\Portal\Tests\Unit\Themes;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Exceptions;
use Platform\Portal\Models\PortalLayoutSetting;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Tests\TestCase;

class PortalLayoutSettingRepositoryCacheTest extends TestCase
{
    protected PortalLayoutSettingRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(PortalLayoutSettingRepository::class);
        Cache::flush();
    }

    /**
     * Test resolveVersionToken initializes a missing key and confirms storage.
     */
    public function test_resolve_version_token_initializes_missing_key_and_confirms_storage(): void
    {
        $key = 'test_generation_key_init';
        $this->assertNull(Cache::get($key));

        $token = PortalLayoutSettingRepository::resolveVersionToken($key);

        $this->assertNotNull($token);
        $this->assertIsString($token);
        $this->assertSame(32, strlen($token));
        $this->assertSame($token, Cache::get($key));

        // Subsequent call returns the exact same confirmed token
        $this->assertSame($token, PortalLayoutSettingRepository::resolveVersionToken($key));
    }

    /**
     * Test resolveVersionToken returns null and reports exception when cache fails.
     */
    public function test_resolve_version_token_returns_null_when_cache_fails_to_confirm(): void
    {
        Exceptions::fake();

        $mockStore = $this->createMock(Store::class);
        $mockStore->method('get')->willThrowException(new \RuntimeException('Redis connection timeout'));

        $mockRepository = new Repository($mockStore);
        Cache::swap($mockRepository);

        $token = PortalLayoutSettingRepository::resolveVersionToken('unreachable_key');

        $this->assertNull($token);
        Exceptions::assertReported(function (\RuntimeException $e) {
            return $e->getMessage() === 'Redis connection timeout';
        });
    }

    /**
     * Test getSettings bypasses persistent caching and reads DB directly when a generation token is null.
     */
    public function test_get_settings_bypasses_cache_and_reads_db_when_generation_token_is_null(): void
    {
        Exceptions::fake();

        // Seed settings directly in MySQL
        $this->repository->create([
            'group' => 'header',
            'channel_id' => 1,
            'theme_code' => 'portal',
            'locale_code' => null,
            'settings' => ['logo' => 'direct-db-logo.svg'],
        ]);

        // Break cache by throwing on version key
        $mockStore = $this->createMock(Store::class);
        $mockStore->method('get')->willThrowException(new \RuntimeException('Redis unreachable'));

        $mockRepository = new Repository($mockStore);
        Cache::swap($mockRepository);

        $settings = $this->repository->getSettings('header', 1, 'portal');

        $this->assertIsArray($settings);
        $this->assertSame('direct-db-logo.svg', $settings['logo'] ?? null);
        Exceptions::assertReported(\RuntimeException::class);
    }

    /**
     * Test clearCache scoped by theme invalidates only targeted theme generation.
     */
    public function test_clear_cache_scoped_by_theme_invalidates_theme_generation_only(): void
    {
        // 1. Initialize tokens for all scopes
        $globalKey = PortalLayoutSettingRepository::CACHE_VERSION_KEY;
        $channel1Key = 'portal_layout_v_c1';
        $theme1PortalKey = 'portal_layout_v_c1_tportal';
        $theme1DarkKey = 'portal_layout_v_c1_tdark';
        $channel2PortalKey = 'portal_layout_v_c2_tportal';

        $gInit = PortalLayoutSettingRepository::resolveVersionToken($globalKey);
        $c1Init = PortalLayoutSettingRepository::resolveVersionToken($channel1Key);
        $t1PortalInit = PortalLayoutSettingRepository::resolveVersionToken($theme1PortalKey);
        $t1DarkInit = PortalLayoutSettingRepository::resolveVersionToken($theme1DarkKey);
        $c2PortalInit = PortalLayoutSettingRepository::resolveVersionToken($channel2PortalKey);

        // 2. Invalidate theme 'portal' on channel 1 only
        PortalLayoutSettingRepository::clearCache(1, 'portal');

        // 3. Verify targeted theme changed
        $t1PortalAfter = PortalLayoutSettingRepository::resolveVersionToken($theme1PortalKey);
        $this->assertNotSame($t1PortalInit, $t1PortalAfter);

        // 4. Verify all unrelated scopes remained completely untouched
        $this->assertSame($t1DarkInit, PortalLayoutSettingRepository::resolveVersionToken($theme1DarkKey));
        $this->assertSame($c2PortalInit, PortalLayoutSettingRepository::resolveVersionToken($channel2PortalKey));
        $this->assertSame($c1Init, PortalLayoutSettingRepository::resolveVersionToken($channel1Key));
        $this->assertSame($gInit, PortalLayoutSettingRepository::resolveVersionToken($globalKey));
    }

    /**
     * Test clearCache scoped by channel invalidates channel generation without affecting other channels.
     */
    public function test_clear_cache_scoped_by_channel_invalidates_channel_generation(): void
    {
        $globalKey = PortalLayoutSettingRepository::CACHE_VERSION_KEY;
        $c1Key = 'portal_layout_v_c1';
        $c2Key = 'portal_layout_v_c2';

        $gInit = PortalLayoutSettingRepository::resolveVersionToken($globalKey);
        $c1Init = PortalLayoutSettingRepository::resolveVersionToken($c1Key);
        $c2Init = PortalLayoutSettingRepository::resolveVersionToken($c2Key);

        // Invalidate channel 1 only
        PortalLayoutSettingRepository::clearCache(1);

        $c1After = PortalLayoutSettingRepository::resolveVersionToken($c1Key);
        $this->assertNotSame($c1Init, $c1After);

        // Other channel and global master version must not change
        $this->assertSame($c2Init, PortalLayoutSettingRepository::resolveVersionToken($c2Key));
        $this->assertSame($gInit, PortalLayoutSettingRepository::resolveVersionToken($globalKey));
    }

    /**
     * Test clearCache with null channel falls back to global generation invalidation.
     */
    public function test_clear_cache_global_scope_invalidates_master_generation(): void
    {
        $globalKey = PortalLayoutSettingRepository::CACHE_VERSION_KEY;
        $gInit = PortalLayoutSettingRepository::resolveVersionToken($globalKey);

        // No args
        PortalLayoutSettingRepository::clearCache();
        $gAfterNoArgs = PortalLayoutSettingRepository::resolveVersionToken($globalKey);
        $this->assertNotSame($gInit, $gAfterNoArgs);

        // Null channel with theme
        PortalLayoutSettingRepository::clearCache(null, 'portal');
        $gAfterThemeOnly = PortalLayoutSettingRepository::resolveVersionToken($globalKey);
        $this->assertNotSame($gAfterNoArgs, $gAfterThemeOnly);
    }

    /**
     * Test saveSettings persists to MySQL and does not throw when cache invalidation fails.
     */
    public function test_save_settings_persists_to_db_and_does_not_throw_when_cache_fails(): void
    {
        Exceptions::fake();

        $mockStore = $this->createMock(Store::class);
        $mockStore->method('forever')->willThrowException(new \RuntimeException('Redis write failure'));

        $mockRepository = new Repository($mockStore);
        Cache::swap($mockRepository);

        $record = $this->repository->saveSettings('footer', ['copyright' => '2026 Bagisto Portal'], 1, 'portal');

        // Database write succeeded
        $this->assertNotNull($record);
        $this->assertSame('2026 Bagisto Portal', $record->settings['copyright'] ?? null);

        // Cache failure was reported and did not fail the save response
        Exceptions::assertReported(function (\RuntimeException $e) {
            return $e->getMessage() === 'Redis write failure';
        });
    }

    /**
     * Test fetchSettingsFromDatabase preserves locale merging hierarchy identically.
     */
    public function test_fetch_settings_from_database_preserves_locale_merging_identically(): void
    {
        // 1. Channel base settings (locale = null)
        $this->repository->create([
            'group' => 'header',
            'channel_id' => 1,
            'theme_code' => 'portal',
            'locale_code' => null,
            'settings' => [
                'bg_color' => '#ffffff',
                'nav' => ['home', 'shop'],
                'sticky' => true,
            ],
        ]);

        // 2. Locale override for Arabic (locale = 'ar')
        $this->repository->create([
            'group' => 'header',
            'channel_id' => 1,
            'theme_code' => 'portal',
            'locale_code' => 'ar',
            'settings' => [
                'bg_color' => '#1a1a1a', // overrides #ffffff
                'rtl' => true,           // added key
            ],
        ]);

        // Merged Arabic result
        $arSettings = $this->repository->fetchSettingsFromDatabase('header', 1, 'portal', 'ar');
        $this->assertSame('#1a1a1a', $arSettings['bg_color']);
        $this->assertSame(['home', 'shop'], $arSettings['nav']);
        $this->assertTrue($arSettings['sticky']);
        $this->assertTrue($arSettings['rtl']);

        // Default locale (null) without overrides
        $defaultSettings = $this->repository->fetchSettingsFromDatabase('header', 1, 'portal', null);
        $this->assertSame('#ffffff', $defaultSettings['bg_color']);
        $this->assertArrayNotHasKey('rtl', $defaultSettings);

        // Non-existent locale falls back to channel base settings
        $frSettings = $this->repository->fetchSettingsFromDatabase('header', 1, 'portal', 'fr');
        $this->assertSame('#ffffff', $frSettings['bg_color']);
        $this->assertArrayNotHasKey('rtl', $frSettings);
    }

    /**
     * Test missing generation token with surviving old data keys forces a cache miss.
     */
    public function test_missing_generation_token_with_surviving_old_data_keys_causes_cache_miss(): void
    {
        $this->repository->saveSettings('theme_tokens', ['--primary' => '#111111'], 1, 'portal');

        // Prime the cache
        $initial = $this->repository->getSettings('theme_tokens', 1, 'portal');
        $this->assertSame('#111111', $initial['--primary']);

        // Update database directly (simulating write during outage)
        PortalLayoutSetting::where([
            'group' => 'theme_tokens',
            'channel_id' => 1,
            'theme_code' => 'portal',
        ])->update(['settings' => json_encode(['--primary' => '#222222'])]);

        // Evict/lose the theme version token key alone (simulating eviction while old data key remains)
        Cache::forget('portal_layout_v_c1_tportal');

        // Next read will generate a new unique token, causing a cache miss on the old key
        $updated = $this->repository->getSettings('theme_tokens', 1, 'portal');
        $this->assertSame('#222222', $updated['--primary']);
    }

    /**
     * Test repository is stateless and multiple instances do not serve stale cached data.
     */
    public function test_repository_is_stateless_and_multiple_instances_do_not_serve_stale_data(): void
    {
        $instanceA = app(PortalLayoutSettingRepository::class);
        $instanceB = app(PortalLayoutSettingRepository::class);

        // Instance A saves initial data
        $instanceA->saveSettings('header', ['title' => 'Initial Title'], 1, 'portal');
        $this->assertSame('Initial Title', $instanceA->getSettings('header', 1, 'portal')['title']);

        // Instance B updates data
        $instanceB->saveSettings('header', ['title' => 'Updated Title'], 1, 'portal');

        // Instance A reads again: must receive updated data immediately (no stale in-memory runtimeCache)
        $this->assertSame('Updated Title', $instanceA->getSettings('header', 1, 'portal')['title']);
    }

    /**
     * Test saving settings for theme group invalidates channel generation.
     */
    public function test_save_settings_for_theme_group_invalidates_channel_generation(): void
    {
        // 1. Initialize tokens for channel 1, channel 2, and theme 'portal' on channel 1
        $c1Key = 'portal_layout_v_c1';
        $c2Key = 'portal_layout_v_c2';
        $t1PortalKey = 'portal_layout_v_c1_tportal';

        $c1Init = PortalLayoutSettingRepository::resolveVersionToken($c1Key);
        $c2Init = PortalLayoutSettingRepository::resolveVersionToken($c2Key);
        $t1PortalInit = PortalLayoutSettingRepository::resolveVersionToken($t1PortalKey);

        // 2. Prime the cache with existing data for channel 1 to obtain initial data cache key
        $this->repository->saveSettings('header', ['title' => 'Original Header'], 1, 'portal');
        $initialSettings = $this->repository->getSettings('header', 1, 'portal');
        $this->assertSame('Original Header', $initialSettings['title']);

        // Record tokens immediately prior to saving 'theme' group
        $c1Before = PortalLayoutSettingRepository::resolveVersionToken($c1Key);
        $t1PortalBefore = PortalLayoutSettingRepository::resolveVersionToken($t1PortalKey);

        // 3. Save a setting in 'theme' group (e.g. activating a new theme on channel 1)
        $this->repository->saveSettings('theme', ['active_theme' => 'midnight'], 1, 'portal');

        // 4. Assert channel 1 token changed
        $c1After = PortalLayoutSettingRepository::resolveVersionToken($c1Key);
        $this->assertNotSame($c1Before, $c1After);

        // 5. Assert independent theme token did NOT change
        $t1PortalAfter = PortalLayoutSettingRepository::resolveVersionToken($t1PortalKey);
        $this->assertSame($t1PortalBefore, $t1PortalAfter);

        // 6. Assert another channel's generation token was NOT invalidated
        $c2After = PortalLayoutSettingRepository::resolveVersionToken($c2Key);
        $this->assertSame($c2Init, $c2After);

        // 7. Assert that the new channel generation leads to a different data cache key (cache miss)
        PortalLayoutSetting::where([
            'group' => 'header',
            'channel_id' => 1,
            'theme_code' => 'portal',
        ])->update(['settings' => json_encode(['title' => 'Fresh Header from DB'])]);

        $freshSettings = $this->repository->getSettings('header', 1, 'portal');
        $this->assertSame('Fresh Header from DB', $freshSettings['title']);
    }

    /**
     * Test saving settings for non-theme group invalidates theme generation and not channel generation.
     */
    public function test_save_settings_for_non_theme_group_invalidates_theme_generation(): void
    {
        $c1Key = 'portal_layout_v_c1';
        $t1PortalKey = 'portal_layout_v_c1_tportal';

        $c1Init = PortalLayoutSettingRepository::resolveVersionToken($c1Key);
        $t1PortalInit = PortalLayoutSettingRepository::resolveVersionToken($t1PortalKey);

        // Save settings in 'theme_tokens' group
        $this->repository->saveSettings('theme_tokens', ['--primary' => '#abcdef'], 1, 'portal');

        // Theme generation must change
        $t1PortalAfter = PortalLayoutSettingRepository::resolveVersionToken($t1PortalKey);
        $this->assertNotSame($t1PortalInit, $t1PortalAfter);

        // Channel generation must NOT change
        $c1After = PortalLayoutSettingRepository::resolveVersionToken($c1Key);
        $this->assertSame($c1Init, $c1After);
    }
}
