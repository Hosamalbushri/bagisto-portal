<?php

namespace Platform\Portal\Repositories;

use Illuminate\Support\Facades\Cache;
use Platform\Portal\Contracts\PortalLayoutSetting as PortalLayoutSettingContract;
use Webkul\Core\Eloquent\Repository;

class PortalLayoutSettingRepository extends Repository
{
    /**
     * Cache version key for atomic invalidation.
     */
    public const CACHE_VERSION_KEY = 'portal_layout_settings_version';

    /**
     * Cache store for runtime lookups.
     *
     * @var array<string, array>
     */
    protected array $runtimeCache = [];

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PortalLayoutSettingContract::class;
    }

    /**
     * Invalidate persistent layout cache.
     */
    public static function clearCache(): void
    {
        try {
            if (! Cache::has(self::CACHE_VERSION_KEY)) {
                Cache::forever(self::CACHE_VERSION_KEY, 1);
            }

            Cache::increment(self::CACHE_VERSION_KEY);
        } catch (\Throwable) {
            Cache::put(self::CACHE_VERSION_KEY, time(), 86400 * 30);
        }
    }

    /**
     * Get layout settings for a group, channel, theme, and locale.
     * Cached with versioning for optimal response time.
     */
    public function getSettings(
        string $group,
        int $channelId,
        string $themeCode = 'default',
        ?string $localeCode = null
    ): array {
        $cacheKey = "{$group}_{$channelId}_{$themeCode}_{$localeCode}";

        if (isset($this->runtimeCache[$cacheKey])) {
            return $this->runtimeCache[$cacheKey];
        }

        $version = Cache::get(self::CACHE_VERSION_KEY, 1);
        $persistentKey = "portal_layout_v{$version}_{$group}_{$channelId}_{$themeCode}_".($localeCode ?? 'none');

        $result = Cache::remember($persistentKey, 86400, function () use ($group, $channelId, $themeCode, $localeCode) {
            // 1. Fetch channel-wide settings without specific locale (fallback)
            $defaultRecord = $this->findOneWhere([
                'group' => $group,
                'channel_id' => $channelId,
                'theme_code' => $themeCode,
                'locale_code' => null,
            ]);

            $defaultSettings = $defaultRecord?->settings ?? [];

            if (! $localeCode) {
                return $defaultSettings;
            }

            // 2. Fetch specific locale settings
            $localeRecord = $this->findOneWhere([
                'group' => $group,
                'channel_id' => $channelId,
                'theme_code' => $themeCode,
                'locale_code' => $localeCode,
            ]);

            $localeSettings = $localeRecord?->settings ?? [];

            // 3. Specific locale settings override default settings recursively
            return array_replace_recursive($defaultSettings, $localeSettings);
        });

        return $this->runtimeCache[$cacheKey] = $result;
    }

    /**
     * Get a specific setting value using dot notation.
     */
    public function getSetting(
        string $group,
        string $key,
        mixed $default = null,
        ?int $channelId = null,
        ?string $themeCode = null,
        ?string $localeCode = null
    ): mixed {
        $channelId = $channelId ?? core()->getCurrentChannel()?->id ?? 0;
        $themeCode = $themeCode ?? core()->getCurrentChannel()?->theme ?? config('themes.shop-default', 'default');
        $localeCode = $localeCode ?? app()->getLocale();

        $settings = $this->getSettings($group, $channelId, $themeCode, $localeCode);

        return data_get($settings, $key, $default);
    }

    /**
     * Save layout settings for a group, channel, theme, and locale.
     */
    public function saveSettings(
        string $group,
        array $settings,
        int $channelId,
        string $themeCode = 'default',
        ?string $localeCode = null
    ): mixed {
        $record = $this->model->updateOrCreate([
            'group' => $group,
            'channel_id' => $channelId,
            'theme_code' => $themeCode,
            'locale_code' => $localeCode,
        ], [
            'settings' => $settings,
        ]);

        // Invalidate both persistent cache and runtime cache
        self::clearCache();
        $this->runtimeCache = [];

        return $record;
    }
}
