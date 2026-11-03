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

        return app(PortalLayoutSettingRepository::class)->getSettings(
            $group,
            $channelId,
            $themeCode,
            $localeCode
        );
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
