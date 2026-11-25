<?php

use Illuminate\Support\Facades\Route;
use Platform\Portal\Contracts\PortalMenu;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Repositories\PortalMenuRepository;
use Platform\Portal\Repositories\PortalMenuSlotAssignmentRepository;
use Platform\Portal\Services\ThemeManager;

if (! function_exists('portal_menu')) {
    /**
     * Retrieve a portal menu with its active item hierarchy by slug.
     */
    function portal_menu(string $slug, ?int $channelId = null): ?PortalMenu
    {
        return app(PortalMenuRepository::class)->getActiveTree($slug, $channelId);
    }
}

if (! function_exists('portal_menu_slot')) {
    /**
     * Retrieve the portal menu assigned to a specific slot identifier.
     */
    function portal_menu_slot(string $slotId, ?int $channelId = null, ?string $themeCode = null): ?PortalMenu
    {
        $channelId = $channelId ?: core()->getCurrentChannel()?->id;
        $themeCode = $themeCode ?: portal_active_theme($channelId);

        $assignment = app(PortalMenuSlotAssignmentRepository::class)->findForSlot(
            $slotId,
            $channelId ?? 0,
            $themeCode
        );

        if ($assignment && $assignment->status && $assignment->menu && $assignment->menu->status) {
            return app(PortalMenuRepository::class)->getActiveTree($assignment->menu->slug, $channelId);
        }

        // Header slot fallback to common convention slugs or first active menu
        if (in_array($slotId, ['header_main', 'header_mobile', 'header'], true)) {
            $menuRepo = app(PortalMenuRepository::class);

            foreach (['main-nav', 'header-nav', 'header', 'primary'] as $slug) {
                $tree = $menuRepo->getActiveTree($slug, $channelId);
                if ($tree && $tree->status && $tree->rootItems && $tree->rootItems->isNotEmpty()) {
                    return $tree;
                }
            }

            $firstMenu = \Platform\Portal\Models\PortalMenu::where('status', 1)
                ->where(function ($q) use ($channelId) {
                    if ($channelId) {
                        $q->where('channel_id', $channelId)->orWhereNull('channel_id');
                    } else {
                        $q->whereNull('channel_id');
                    }
                })
                ->whereHas('items')
                ->latest('id')
                ->first();

            if ($firstMenu) {
                return $menuRepo->getActiveTree($firstMenu->slug, $channelId);
            }
        }

        return null;
    }
}

if (! function_exists('portal_home_url')) {
    /**
     * Retrieve the active portal storefront home URL.
     */
    function portal_home_url(): string
    {
        if (Route::has('portal.home')) {
            return route('portal.home');
        }

        return url('/');
    }
}

if (! function_exists('portal_sanitize_url')) {
    /**
     * Sanitize a URL to prevent XSS (allowing only http, https, relative paths, anchor hashes, mailto, and tel).
     */
    function portal_sanitize_url(?string $url): string
    {
        if ($url === null) {
            return '#';
        }

        $trimmed = trim($url);

        if ($trimmed === '' || $trimmed === '#') {
            return '#';
        }

        // Remove control characters, null bytes, and invisible whitespace
        $cleaned = preg_replace('/[\x00-\x20]+/', '', $trimmed);

        // Strict allowlist: exclusively allow URLs starting with /, #, http://, https://, mailto:, or tel:
        if (! preg_match('/^(\/|#|https?:\/\/|mailto:|tel:)/i', $cleaned)) {
            return '#';
        }

        return $trimmed;
    }
}

if (! function_exists('portal_route')) {
    /**
     * Resolve a portal route safely.
     */
    function portal_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        if (Route::has($name)) {
            return route($name, $parameters, $absolute);
        }

        return url($name);
    }
}

if (! function_exists('portal_layout_setting')) {
    /**
     * Retrieve a specific portal layout setting value using dot notation.
     */
    function portal_layout_setting(
        string $group,
        string $key,
        mixed $default = null,
        ?int $channelId = null,
        ?string $themeCode = null,
        ?string $localeCode = null
    ): mixed {
        return app(PortalLayoutSettingRepository::class)->getSetting(
            $group,
            $key,
            $default,
            $channelId,
            $themeCode,
            $localeCode
        );
    }
}

if (! function_exists('portal_layout_settings')) {
    /**
     * Retrieve all portal layout settings for a group.
     *
     * For 'header' and 'footer', merges Bagisto System Configuration (`portal.header.*`
     * / `portal.footer.*`) with any legacy `portal_layout_settings` overrides.
     */
    function portal_layout_settings(
        string $group,
        ?int $channelId = null,
        ?string $themeCode = null,
        ?string $localeCode = null
    ): array {
        $channelId = $channelId ?? core()->getCurrentChannel()?->id ?? 0;
        $themeCode = $themeCode ?? portal_active_theme($channelId);
        $localeCode = $localeCode ?? app()->getLocale();

        $settings = app(PortalLayoutSettingRepository::class)->getSettings(
            $group,
            $channelId,
            $themeCode,
            $localeCode
        );

        if (in_array($group, ['header', 'footer'], true)) {
            $settings = portal_merge_system_settings($group, $settings, $channelId, $localeCode);
        }

        return $settings;
    }
}

if (! function_exists('portal_merge_system_settings')) {
    /**
     * Populate header/footer layout settings from Bagisto System Configuration (`system.php`).
     */
    function portal_merge_system_settings(
        string $group,
        array $settings,
        ?int $channelId = null,
        ?string $localeCode = null
    ): array {
        $channel = ($channelId && method_exists(core(), 'getAllChannels'))
            ? core()->getAllChannels()->firstWhere('id', $channelId)
            : core()->getCurrentChannel();

        $channelCode = $channel?->code ?? null;
        $defaultLocaleCode = $channel?->default_locale?->code ?? null;

        $readConfig = static function (string $key, bool $localeBased) use ($channelCode, $localeCode, $defaultLocaleCode): mixed {
            $value = $localeBased
                ? core()->getConfigData($key, $channelCode, $localeCode)
                : core()->getConfigData($key, $channelCode);

            if ($localeBased && ($value === null || $value === '') && $defaultLocaleCode && $defaultLocaleCode !== $localeCode) {
                $value = core()->getConfigData($key, $channelCode, $defaultLocaleCode);
            }

            return $value;
        };

        $map = $group === 'header'
            ? [
                'controls.sticky'          => ['portal.header.controls.sticky', false],
                'controls.show_dark_mode'  => ['portal.header.controls.show_dark_mode', false],
                'controls.show_locales'    => ['portal.header.controls.show_locales', false],
                'controls.show_search'     => ['portal.header.controls.show_search', false],
                'controls.show_auth'       => ['portal.header.controls.show_auth', false],
                'announcement.status'      => ['portal.header.announcement.status', false],
                'announcement.text'        => ['portal.header.announcement.text', true],
                'announcement.link'        => ['portal.header.announcement.link', false],
                'announcement.bg_color'    => ['portal.header.announcement.bg_color', false],
                'announcement.text_color'  => ['portal.header.announcement.text_color', false],
                'cta.enabled'              => ['portal.header.cta.enabled', false],
                'cta.text'                 => ['portal.header.cta.text', true],
                'cta.url'                  => ['portal.header.cta.url', false],
                'cta.target'               => ['portal.header.cta.target', false],
            ]
            : [
                'contact.bio'                => ['portal.footer.contact.bio', true],
                'contact.phone'              => ['portal.footer.contact.phone', false],
                'contact.email'              => ['portal.footer.contact.email', false],
                'contact.address'            => ['portal.footer.contact.address', true],
                'social.status'              => ['portal.footer.social.status', false],
                'social.platforms.twitter'   => ['portal.footer.social.twitter', false],
                'social.platforms.instagram' => ['portal.footer.social.instagram', false],
                'social.platforms.facebook'  => ['portal.footer.social.facebook', false],
                'social.platforms.youtube'   => ['portal.footer.social.youtube', false],
                'social.platforms.linkedin'  => ['portal.footer.social.linkedin', false],
                'social.platforms.whatsapp'  => ['portal.footer.social.whatsapp', false],
                'social.platforms.telegram'  => ['portal.footer.social.telegram', false],
                'social.platforms.tiktok'    => ['portal.footer.social.tiktok', false],
                'copyright.text'             => ['portal.footer.copyright.text', true],
            ];

        foreach ($map as $dotPath => [$configKey, $localeBased]) {
            if (data_get($settings, $dotPath) === null) {
                $configValue = $readConfig($configKey, $localeBased);

                if ($configValue !== null) {
                    data_set($settings, $dotPath, $configValue);
                }
            }
        }

        return $settings;
    }
}

if (! function_exists('portal_theme_css')) {
    /**
     * Retrieve the active theme's custom CSS design tokens or overrides.
     * Injects live CSS into <head> for instant zero-build style and color updates.
     */
    function portal_theme_css(?string $themeCode = null, ?int $channelId = null): ?string
    {
        return app(ThemeManager::class)->resolveThemeCss($themeCode, $channelId);
    }
}

if (! function_exists('portal_active_theme')) {
    /**
     * Retrieve the active portal theme code for a channel.
     */
    function portal_active_theme(?int $channelId = null): string
    {
        $channel = ($channelId && method_exists(core(), 'getAllChannels'))
            ? core()->getAllChannels()->firstWhere('id', $channelId)
            : core()->getCurrentChannel();
        $channelId = $channel?->id ?? 1;

        $customPortalTheme = app(PortalLayoutSettingRepository::class)->getSetting(
            'theme',
            'active_theme',
            null,
            $channelId,
            'portal'
        );

        if ($customPortalTheme && app(ThemeManager::class)->find($customPortalTheme)) {
            return $customPortalTheme;
        }

        return 'portal';
    }
}

if (! function_exists('portal_asset')) {
    /**
     * Resolve a theme asset safely without triggering a 404 abort if missing in current theme manifest.
     */
    function portal_asset(string $url, ?string $fallback = null): ?string
    {
        try {
            return bagisto_asset($url);
        } catch (Throwable) {
            return $fallback;
        }
    }
}

if (! function_exists('portal_logo')) {
    /**
     * Resolve the portal site logo URL for the given theme mode ('light' or 'dark') and locale.
     *
     * Falls back cleanly from locale/channel-scoped configuration (`portal.general.design.logo_light`
     * / `logo_dark`) to the channel default locale's configuration, then to the channel logo
     * (`core()->getCurrentChannel()->logo_url`), and finally to the package SVG defaults.
     */
    function portal_logo(string $mode = 'light', ?string $locale = null): ?string
    {
        $resolveUrl = static function (mixed $value): ?string {
            if (! is_string($value) || trim($value) === '') {
                return null;
            }

            if (preg_match('/^(https?:\/\/|\/)/i', $value)) {
                return $value;
            }

            return \Illuminate\Support\Facades\Storage::url($value);
        };

        $channel = core()->getCurrentChannel();
        $defaultLocaleCode = $channel?->default_locale?->code ?? null;

        $resolveLocaleConfig = static function (string $key) use ($locale, $defaultLocaleCode): mixed {
            $value = $locale !== null
                ? core()->getConfigData($key, null, $locale)
                : core()->getConfigData($key);

            if ((! is_string($value) || trim($value) === '') && $defaultLocaleCode) {
                $activeLocale = $locale ?? (app()->getLocale() ?: null);

                if ($activeLocale !== $defaultLocaleCode) {
                    $value = core()->getConfigData($key, null, $defaultLocaleCode);
                }
            }

            return $value;
        };

        $lightConfig = $resolveLocaleConfig('portal.general.design.logo_light');
        $darkConfig = $resolveLocaleConfig('portal.general.design.logo_dark');
        $channelLogo = $channel?->logo_url;

        $lightUrl = $resolveUrl($lightConfig) ?: ($channelLogo ?: null);
        $darkUrl = $resolveUrl($darkConfig);

        $defaultLight = portal_asset('images/logo.svg');
        $defaultDark = portal_asset('images/dark-logo.svg') ?: $defaultLight;

        if ($mode === 'dark') {
            return $darkUrl ?: ($lightUrl ?: $defaultDark);
        }

        return $lightUrl ?: $defaultLight;
    }
}

if (! function_exists('portal_shop_enabled')) {
    /**
     * Check if the portal operates in e-commerce / shop mode.
     */
    function portal_shop_enabled(): bool
    {
        return (bool) config('portal.shop.enabled', false)
            && (class_exists(\Webkul\Shop\Providers\ShopServiceProvider::class) || class_exists(\Webkul\Product\Providers\ProductServiceProvider::class));
    }
}

if (! function_exists('view_render_event')) {
    /**
     * Fallback for view_render_event when testing standalone without Bagisto Core.
     */
    function view_render_event(string $eventName, mixed $params = null): string
    {
        if (class_exists(\Webkul\Theme\ViewRenderEventManager::class) && app()->bound(\Webkul\Theme\ViewRenderEventManager::class)) {
            return (string) app(\Webkul\Theme\ViewRenderEventManager::class)
                ->handleRenderEvent($eventName, $params)
                ->render();
        }

        if (function_exists('event')) {
            $responses = event($eventName, $params);

            if (is_array($responses)) {
                return implode('', array_filter($responses, fn ($item) => is_string($item) || $item instanceof \Illuminate\Contracts\Support\Htmlable));
            }
        }

        return '';
    }
}
