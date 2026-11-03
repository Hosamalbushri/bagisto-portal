<?php

namespace Platform\Portal\Services;

use Illuminate\Support\Facades\File;
use Platform\Portal\Themes\ThemeManifest;
use Webkul\Core\Models\Channel;

class ThemeManager
{
    /**
     * Statically memoized manifests to eliminate repeated filesystem I/O.
     *
     * @var array<string, ThemeManifest>|null
     */
    protected static ?array $manifests = null;

    /**
     * Discover all registered, in-app, and package themes.
     *
     * @return array<string, ThemeManifest>
     */
    public function discoverThemes(bool $forceRefresh = false): array
    {
        if (self::$manifests !== null && ! $forceRefresh) {
            return self::$manifests;
        }

        $discovered = [];

        // 1. Built-in themes from package config
        $builtinConfig = config('themes.shop', []);
        foreach ($builtinConfig as $code => $conf) {
            if (is_array($conf)) {
                $discovered[$code] = ThemeManifest::fromArray($code, $conf);
            }
        }

        // 2. Discover in-app themes inside resources/themes/*/theme.php
        $themesDir = resource_path('themes');
        if (File::isDirectory($themesDir)) {
            $themeFiles = glob($themesDir.'/*/theme.php');
            if (is_array($themeFiles)) {
                foreach ($themeFiles as $themeFile) {
                    $code = basename(dirname($themeFile));
                    try {
                        $data = require $themeFile;
                        if (is_array($data)) {
                            $themeData = $data[$code] ?? $data;
                            $discovered[$code] = ThemeManifest::fromArray($code, $themeData, false);
                        }
                    } catch (\Throwable) {
                        // Skip corrupted theme definitions gracefully
                    }
                }
            }
        }

        // 3. Discover standalone package themes inside packages/*/*/src/Config/themes.php
        $packageThemeFiles = glob(base_path('packages/*/*/src/Config/themes.php'));
        if (is_array($packageThemeFiles)) {
            foreach ($packageThemeFiles as $file) {
                try {
                    $pkgData = require $file;
                    if (is_array($pkgData)) {
                        foreach ($pkgData as $code => $conf) {
                            if (is_array($conf)) {
                                $discovered[$code] = ThemeManifest::fromArray($code, $conf, true);
                            }
                        }
                    }
                } catch (\Throwable) {
                    // Skip corrupted theme definitions gracefully
                }
            }
        }

        self::$manifests = $discovered;

        return self::$manifests;
    }

    /**
     * Find a theme manifest by its code.
     */
    public function find(?string $code): ?ThemeManifest
    {
        if (! $code) {
            return null;
        }

        $all = $this->discoverThemes();

        return $all[$code] ?? null;
    }

    /**
     * Retrieve all discovered themes as Bagisto-compatible config arrays.
     */
    public function toConfigArray(): array
    {
        $result = [];
        foreach ($this->discoverThemes() as $code => $manifest) {
            $result[$code] = $manifest->toArray();
        }

        return $result;
    }

    /**
     * Check if a theme is installed/discovered.
     */
    public function isInstalled(string $code): bool
    {
        return isset($this->discoverThemes()[$code]);
    }

    /**
     * Resolve the active theme's custom CSS design tokens or overrides.
     */
    public function resolveThemeCss(?string $themeCode = null): ?string
    {
        $themeCode = $themeCode ?: (core()->getCurrentChannel()?->theme ?? config('themes.shop-default', 'default'));

        if (in_array($themeCode, ['portal', 'default'], true)) {
            return null;
        }

        $manifest = $this->find($themeCode);
        if ($manifest) {
            return $manifest->getCssContents();
        }

        // Direct in-app fallback if theme.php manifest is not created yet
        $directPath = resource_path("themes/{$themeCode}/assets/css/app.css");
        if (file_exists($directPath)) {
            return file_get_contents($directPath);
        }

        return null;
    }

    /**
     * Activate a theme for a specific channel and dispatch domain events.
     */
    public function activateTheme(int $channelId, string $themeCode): void
    {
        event('portal.theme.activating', [$channelId, $themeCode]);

        $channelClass = class_exists(Channel::class)
            ? Channel::class
            : (class_exists(\Webkul\Core\Contracts\Channel::class) ? \Webkul\Core\Contracts\Channel::class : null);

        if ($channelClass) {
            $channel = $channelClass::find($channelId);
            if ($channel) {
                $channel->theme = $themeCode;
                $channel->save();
            }
        }

        $this->clearCache();

        event('portal.theme.activated', [$channelId, $themeCode]);
    }

    /**
     * Clear memoized cache for testing or runtime refreshes.
     */
    public function clearCache(): void
    {
        self::$manifests = null;
    }
}
