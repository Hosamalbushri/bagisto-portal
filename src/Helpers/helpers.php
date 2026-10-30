<?php

use Platform\Portal\Contracts\PortalMenu;
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
