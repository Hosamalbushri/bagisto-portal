<?php

use Platform\Portal\Contracts\PortalMenu;
use Platform\Portal\Repositories\PortalMenuRepository;

if (! function_exists('portal_menu')) {
    /**
     * Retrieve a portal menu with its active item hierarchy by slug.
     */
    function portal_menu(string $slug, ?int $channelId = null): ?PortalMenu
    {
        return app(PortalMenuRepository::class)->getActiveTree($slug, $channelId);
    }
}
