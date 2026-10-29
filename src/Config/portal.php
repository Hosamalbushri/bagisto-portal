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
];
