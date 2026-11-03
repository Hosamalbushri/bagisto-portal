<?php

use Illuminate\Support\Facades\Route;
use Platform\Portal\Contracts\PortalMenu;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Repositories\PortalMenuRepository;
use Platform\Portal\Repositories\PortalMenuSlotAssignmentRepository;

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
        $themeCode = $themeCode ?: (core()->getCurrentChannel()?->theme ?? config('themes.shop-default', 'default'));

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

        if (Route::has('shop.home.index')) {
            return route('shop.home.index');
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
     * Resolve a portal route with fallback if operating in override mode.
     */
    function portal_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        if (Route::has($name)) {
            return route($name, $parameters, $absolute);
        }

        $shopFallback = match ($name) {
            'portal.home' => 'shop.home.index',
            'portal.contact_us' => 'shop.home.contact_us',
            'portal.contact_us.send_mail' => 'shop.home.contact_us.send_mail',
            'portal.appearance.preview' => 'shop.appearance.preview',
            'portal.sitemap.index' => 'shop.sitemap.index',
            'portal.robots' => 'shop.robots',
            default => null,
        };

        if ($shopFallback && Route::has($shopFallback)) {
            return route($shopFallback, $parameters, $absolute);
        }

        return url('/');
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
        $themeCode = $themeCode ?? core()->getCurrentChannel()?->theme ?? config('themes.shop-default', 'default');
        $localeCode = $localeCode ?? app()->getLocale();

        return app(PortalLayoutSettingRepository::class)->getSettings(
            $group,
            $channelId,
            $themeCode,
            $localeCode
        );
    }
}
