<?php

namespace Platform\Portal\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Themes\ThemeManifest;
use Webkul\Core\Models\Channel;

class ThemeManager
{
    /**
     * Cache version key for invalidating compiled system design tokens.
     */
    public const TOKEN_CACHE_VERSION_KEY = 'portal_system_tokens_version';
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
        $portalThemesFile = dirname(__DIR__).'/Config/themes.php';
        if (file_exists($portalThemesFile)) {
            $builtinConfig = require $portalThemesFile;
            if (is_array($builtinConfig)) {
                foreach ($builtinConfig as $code => $conf) {
                    if (is_array($conf)) {
                        $discovered[$code] = ThemeManifest::fromArray($code, $conf);
                    }
                }
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
     * Get all discovered theme manifests.
     *
     * @return array<string, ThemeManifest>
     */
    public function all(bool $forceRefresh = false): array
    {
        return $this->discoverThemes($forceRefresh);
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
    public function resolveThemeCss(?string $themeCode = null, ?int $channelId = null): ?string
    {
        $channel = ($channelId && method_exists(core(), 'getAllChannels'))
            ? core()->getAllChannels()->firstWhere('id', $channelId)
            : core()->getCurrentChannel();
        $channelId = $channel?->id ?? 1;
        $themeCode = $themeCode ?: (function_exists('portal_active_theme') ? portal_active_theme($channelId) : 'portal');

        $css = null;

        if (! in_array($themeCode, ['portal', 'default'], true)) {
            $manifest = $this->find($themeCode);
            if ($manifest) {
                $css = $manifest->getCssContents();
            } else {
                $directPath = resource_path("themes/{$themeCode}/assets/css/app.css");
                if (file_exists($directPath)) {
                    $css = file_get_contents($directPath);
                }
            }
        }

        // Compile system configuration design tokens (cached per channel & versioned on save)
        $systemTokensCss = $this->resolveSystemTokensCss($channel?->code ?? null, $themeCode);
        if (! empty($systemTokensCss)) {
            $css = trim(($css ? $css."\n" : '').$systemTokensCss);
        }

        // Retrieve custom CSS if stored in legacy layout settings
        if (class_exists(PortalLayoutSettingRepository::class)) {
            try {
                $customSettings = app(PortalLayoutSettingRepository::class)->getSettings('theme_tokens', $channelId, $themeCode);
                if (! empty($customSettings['custom_css'])) {
                    $css = $customSettings['custom_css']."\n".($css ?? '');
                }
            } catch (\Throwable) {
                // Graceful fallback
            }
        }

        return $css;
    }

    /**
     * Invalidate cached system design tokens CSS across channels.
     */
    public static function clearTokenCache(): void
    {
        try {
            Cache::forever(self::TOKEN_CACHE_VERSION_KEY, bin2hex(random_bytes(16)));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Read and compile portal.tokens.* configuration into CSS variables with versioned caching.
     */
    protected function resolveSystemTokensCss(?string $channelCode, string $themeCode): ?string
    {
        $compile = function () use ($channelCode, $themeCode): ?string {
            $tokenMap = [
                'portal.tokens.brand.brand_primary'          => '--brand-primary',
                'portal.tokens.brand.brand_accent'           => '--brand-accent',
                'portal.tokens.light.bg_app'                 => '--bg-app',
                'portal.tokens.light.bg_surface'             => '--bg-surface',
                'portal.tokens.light.bg_surface_elevated'    => '--bg-surface-elevated',
                'portal.tokens.light.text_primary'           => '--text-primary',
                'portal.tokens.light.text_secondary'         => '--text-secondary',
                'portal.tokens.light.header_bg'              => '--header-bg',
                'portal.tokens.light.footer_bg'              => '--footer-bg',
                'portal.tokens.light.footer_text'            => '--footer-text',
                'portal.tokens.dark.dark_bg_app'             => '--dark-bg-app',
                'portal.tokens.dark.dark_bg_surface'         => '--dark-bg-surface',
                'portal.tokens.dark.dark_bg_surface_elevated'=> '--dark-bg-surface-elevated',
                'portal.tokens.dark.dark_text_primary'       => '--dark-text-primary',
                'portal.tokens.dark.dark_text_secondary'     => '--dark-text-secondary',
                'portal.tokens.dark.dark_header_bg'          => '--dark-header-bg',
                'portal.tokens.dark.dark_footer_bg'          => '--dark-footer-bg',
                'portal.tokens.layout.btn_radius'            => '--btn-radius',
                'portal.tokens.layout.card_radius'           => '--card-radius',
                'portal.tokens.layout.container_max_width'   => '--container-max-width',
            ];

            $tokens = [];

            foreach ($tokenMap as $configKey => $cssVar) {
                $val = core()->getConfigData($configKey, $channelCode);

                if (is_string($val) && trim($val) !== '') {
                    $tokens[$cssVar] = trim($val);
                }
            }

            $customCss = core()->getConfigData('portal.tokens.layout.custom_css', $channelCode);
            $customCss = is_string($customCss) && trim($customCss) !== '' ? $customCss : null;

            if (empty($tokens) && $customCss === null) {
                return null;
            }

            $compiler = class_exists(TokenCompilerService::class)
                ? app(TokenCompilerService::class)
                : app(\Platform\PortalTokens\TokenCompilerService::class);

            $compiled = $compiler->compile($tokens, $themeCode, $customCss);

            return trim($compiled) !== '' ? $compiled : null;
        };

        $version = PortalLayoutSettingRepository::resolveVersionToken(self::TOKEN_CACHE_VERSION_KEY);
        if ($version === null) {
            return $compile();
        }

        $cacheKey = 'portal_system_tokens_css_'.$version.'_'.($channelCode ?: 'default').'_'.$themeCode;

        try {
            return Cache::remember($cacheKey, 1800, $compile);
        } catch (\Throwable) {
            return $compile();
        }
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

        // Persist in portal settings for independent theme management
        try {
            if (class_exists(PortalLayoutSettingRepository::class)) {
                app(PortalLayoutSettingRepository::class)->saveSetting(
                    'theme',
                    'active_theme',
                    $themeCode,
                    $channelId
                );
            }
        } catch (\Throwable) {
            // Graceful fallback
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
