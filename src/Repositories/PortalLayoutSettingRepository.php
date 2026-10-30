<?php

namespace Platform\Portal\Repositories;

use Platform\Portal\Contracts\PortalLayoutSetting;
use Webkul\Core\Eloquent\Repository;

class PortalLayoutSettingRepository extends Repository
{
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
        return PortalLayoutSetting::class;
    }

    /**
     * Get layout settings for a group, channel, theme, and locale.
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

        // 1. Fetch channel-wide settings without specific locale (fallback)
        $defaultRecord = $this->findOneWhere([
            'group' => $group,
            'channel_id' => $channelId,
            'theme_code' => $themeCode,
            'locale_code' => null,
        ]);

        $defaultSettings = $defaultRecord?->settings ?? [];

        if (! $localeCode) {
            return $this->runtimeCache[$cacheKey] = $defaultSettings;
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
        $merged = array_replace_recursive($defaultSettings, $localeSettings);

        return $this->runtimeCache[$cacheKey] = $merged;
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

        // Invalidate runtime cache
        $this->runtimeCache = [];

        return $record;
    }
}
