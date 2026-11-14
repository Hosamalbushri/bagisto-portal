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
        'guard' => env('PORTAL_AUTH_GUARD', 'web'),
    ],

    /*
     * Portal Shop / E-Commerce Integration.
     *
     * When enabled, the portal operates in e-commerce mode, integrating with Bagisto Shop:
     * - Adds mini-cart and cart counter to header.
     * - Adds customer account navigation and session links.
     * - Enables Product & Category carousel sections in page customizer.
     * - Coexists seamlessly with Bagisto catalog and checkout routes.
     */
    'shop' => [
        'enabled' => env('PORTAL_SHOP_ENABLED', false),
    ],

    /*
     * Dynamic Menu Slots Configuration.
     *
     * Define custom menu locations without modifying core package layout files.
     * These slots appear automatically in the admin menu slot assignment manager.
     *
     * Example:
     * 'slots' => [
     *     'sidebar_navigation' => [
     *         'label' => 'Main Sidebar Navigation',
     *         'location' => 'other', // 'header', 'footer', or 'other'
     *     ],
     *     'top_announcement' => [
     *         'label' => 'Top Bar Links',
     *         'location' => 'header',
     *     ],
     * ],
     */
    'slots' => [
        // Custom slot definitions registered by the host application
    ],
];
