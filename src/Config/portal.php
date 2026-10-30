<?php

return [
    /*
     * Portal Storefront Configuration.
     *
     * Allows enabling or disabling the public storefront interfaces.
     * When disabled, storefront requests are automatically redirected to admin login.
     */
    'storefront' => [
        'enabled' => env('PORTAL_STOREFRONT_ENABLED', true),
    ],

    /*
     * Portal Authentication Configuration.
     *
     * Authentication in the portal is completely optional and non-blocking.
     * By default it is disabled so the platform functions purely as a CMS/content engine.
     */
    'auth' => [
        'enabled' => env('PORTAL_AUTH_ENABLED', false),
        'guard' => env('PORTAL_AUTH_GUARD', 'customer'),
    ],

    /*
     * Portal Routing Mode Configuration.
     *
     * - 'override_shop': Portal acts as the primary storefront for the application.
     * - 'prefix': Portal runs alongside the default shop on a dedicated URL prefix (e.g., /portal).
     */
    'routing' => [
        'mode' => env('PORTAL_ROUTING_MODE', 'override_shop'),
        'prefix' => env('PORTAL_PREFIX', 'portal'),
    ],
];
