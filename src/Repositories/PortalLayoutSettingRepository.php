<?php

namespace Platform\Portal\Repositories;

use Illuminate\Support\Facades\Cache;
use Platform\Portal\Contracts\PortalLayoutSetting as PortalLayoutSettingContract;
use Webkul\Core\Eloquent\Repository;

class PortalLayoutSettingRepository extends Repository
{
    /**
     * Cache version key for global atomic invalidation.
     */
    public const CACHE_VERSION_KEY = 'portal_layout_settings_version';

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PortalLayoutSettingContract::class;
    }

    /**
     * Resolve a confirmed stored generation token for a version key.
     *
     * Never returns an unconfirmed local candidate to prevent worker divergence.
     * Returns null if cache is unavailable or storage could not be confirmed.
     */
    public static function resolveVersionToken(string $key): ?string
    {
        try {
            $token = Cache::get($key);

            if (is_string($token) && $token !== '') {
                return $token;
            }

            $candidate = bin2hex(random_bytes(16));

            // Explicit positive TTL (31536000s / 1 year) triggers native store->add(); atomicity depends on driver (atomic in Redis/Database, driver-dependent in File/Array)
            Cache::add($key, $candidate, 31536000);

            $confirmed = Cache::get($key);

            if (is_string($confirmed) && $confirmed !== '') {
                return $confirmed;
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return null;
    }

    /**
     * Invalidate persistent layout cache across scopes.
     *
     * Hierarchy:
     * - (null, null) or no args: Global generation.
     * - (null, $themeCode): Global generation.
     * - ($channelId, null): Channel generation.
     * - ($channelId, $themeCode): Channel + theme generation.
     */
    public static function clearCache(?int $channelId = null, ?string $themeCode = null): void
    {
        try {
            $newToken = bin2hex(random_bytes(16));

            if ($channelId !== null && $themeCode !== null) {
                Cache::forever("portal_layout_v_c{$channelId}_t{$themeCode}", $newToken);
            } elseif ($channelId !== null) {
                Cache::forever("portal_layout_v_c{$channelId}", $newToken);
            } else {
                Cache::forever(self::CACHE_VERSION_KEY, $newToken);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Fetch settings directly from database preserving locale merging hierarchy.
     */
    public function fetchSettingsFromDatabase(
        string $group,
        int $channelId,
        string $themeCode = 'portal',
        ?string $localeCode = null
    ): array {
        // 1. Fetch channel-wide settings without specific locale (fallback)
        $defaultRecord = $this->findOneWhere([
            'group' => $group,
            'channel_id' => $channelId,
            'theme_code' => $themeCode,
            'locale_code' => null,
        ]);

        $defaultSettings = is_array($defaultRecord?->settings)
            ? $defaultRecord->settings
            : [];

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

        $localeSettings = is_array($localeRecord?->settings)
            ? $localeRecord->settings
            : [];

        // 3. Specific locale settings override default settings recursively
        return array_replace_recursive($defaultSettings, $localeSettings);
    }

    /**
     * Get layout settings for a group, channel, theme, and locale.
     * Cached with versioning for optimal response time.
     */
    public function getSettings(
        string $group,
        int $channelId,
        string $themeCode = 'portal',
        ?string $localeCode = null
    ): array {
        $gToken = self::resolveVersionToken(self::CACHE_VERSION_KEY);
        $cToken = self::resolveVersionToken("portal_layout_v_c{$channelId}");
        $tToken = self::resolveVersionToken("portal_layout_v_c{$channelId}_t{$themeCode}");

        if ($gToken === null || $cToken === null || $tToken === null) {
            return $this->fetchSettingsFromDatabase($group, $channelId, $themeCode, $localeCode);
        }

        $persistentKey = "portal_layout_g{$gToken}_c{$channelId}_{$cToken}_t{$themeCode}_{$tToken}_{$group}_".($localeCode ?? 'none');

        try {
            return Cache::remember($persistentKey, 1800, function () use ($group, $channelId, $themeCode, $localeCode) {
                return $this->fetchSettingsFromDatabase($group, $channelId, $themeCode, $localeCode);
            });
        } catch (\Throwable $e) {
            report($e);

            return $this->fetchSettingsFromDatabase($group, $channelId, $themeCode, $localeCode);
        }
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
        $themeCode = $themeCode ?? ($group === 'theme' ? 'portal' : (function_exists('portal_active_theme') ? portal_active_theme($channelId) : 'portal'));
        $localeCode = $localeCode ?? app()->getLocale();

        $settings = $this->getSettings($group, $channelId, $themeCode, $localeCode);

        return data_get($settings, $key, $default);
    }

    /**
     * Save a single layout setting value using dot notation.
     */
    public function saveSetting(
        string $group,
        string $key,
        mixed $value,
        int $channelId,
        string $themeCode = 'portal',
        ?string $localeCode = null
    ): mixed {
        $settings = $this->getSettings($group, $channelId, $themeCode, $localeCode);
        data_set($settings, $key, $value);

        return $this->saveSettings($group, $settings, $channelId, $themeCode, $localeCode);
    }

    /**
     * Save layout settings for a group, channel, theme, and locale.
     */
    public function saveSettings(
        string $group,
        array $settings,
        int $channelId,
        string $themeCode = 'portal',
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

        // Invalidate scoped persistent cache without failing successful DB write
        if ($group === 'theme') {
            self::clearCache($channelId);
        } else {
            self::clearCache($channelId, $themeCode);
        }

        return $record;
    }
}
