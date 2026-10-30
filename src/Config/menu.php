<?php

return [
    [
        'key' => 'pages',
        'name' => 'portal::app.admin.pages.menu.pages',
        'route' => 'admin.portal.pages.index',
        'sort' => 2,
        'icon' => 'icon-cms',
        'icon-class' => 'cms-icon',
    ],
    [
        'key' => 'pages.index',
        'name' => 'portal::app.admin.pages.menu.all-pages',
        'route' => 'admin.portal.pages.index',
        'sort' => 1,
        'icon' => '',
    ],
    [
        'key' => 'pages.create',
        'name' => 'portal::app.admin.pages.menu.create',
        'route' => 'admin.portal.pages.create',
        'sort' => 2,
        'icon' => '',
    ],
    [
        'key' => 'portal_menus',
        'name' => 'portal::app.admin.menus.menu.title',
        'route' => 'admin.portal.menus.index',
        'sort' => 3,
        'icon' => 'icon-menu',
        'icon-class' => 'menu-icon',
    ],
    [
        'key' => 'portal_menus.index',
        'name' => 'portal::app.admin.menus.menu.all-menus',
        'route' => 'admin.portal.menus.index',
        'sort' => 1,
        'icon' => '',
    ],
    [
        'key' => 'portal_menus.navigation',
        'name' => 'portal::app.admin.navigation.menu.title',
        'route' => 'admin.portal.navigation.index',
        'sort' => 2,
        'icon' => '',
    ],
];
