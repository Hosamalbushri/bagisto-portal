<?php

return [
    /**
     * Root Level Item: Storefront Portal.
     */
    [
        'key' => 'portal',
        'name' => 'portal::app.admin.menu.portal',
        'route' => 'admin.portal.pages.index',
        'sort' => 3,
        'icon' => 'icon-store',
        'icon-class' => 'portal-icon',
    ],

    /**
     * 1. Pages.
     */
    [
        'key' => 'portal.pages',
        'name' => 'portal::app.admin.menu.pages',
        'route' => 'admin.portal.pages.index',
        'sort' => 1,
        'icon' => '',
    ],

    /**
     * 2. Menus.
     */
    [
        'key' => 'portal.menus',
        'name' => 'portal::app.admin.menu.menus',
        'route' => 'admin.portal.menus.index',
        'sort' => 2,
        'icon' => '',
    ],

    /**
     * 3. Customization.
     */
    [
        'key' => 'portal.customize',
        'name' => 'portal::app.admin.menu.customize',
        'route' => 'admin.portal.navigation.index',
        'sort' => 3,
        'icon' => '',
    ],

    /**
     * 3.1 Header Customization.
     */
    [
        'key' => 'portal.customize.header',
        'name' => 'portal::app.admin.menu.customize-header',
        'route' => 'admin.portal.navigation.header',
        'sort' => 1,
        'icon' => '',
    ],

    /**
     * 3.2 Footer Customization.
     */
    [
        'key' => 'portal.customize.footer',
        'name' => 'portal::app.admin.menu.customize-footer',
        'route' => 'admin.portal.navigation.footer',
        'sort' => 2,
        'icon' => '',
    ],

    /**
     * 3.3 Themes Selection.
     */
    [
        'key' => 'portal.customize.themes',
        'name' => 'portal::app.admin.menu.customize-themes',
        'route' => 'admin.portal.themes.index',
        'sort' => 3,
        'icon' => '',
    ],
];
