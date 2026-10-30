<?php

return [
    [
        'key' => 'pages',
        'name' => 'portal::app.admin.pages.acl.pages',
        'route' => 'admin.portal.pages.index',
        'sort' => 2,
    ],
    [
        'key' => 'pages.index',
        'name' => 'portal::app.admin.pages.acl.index',
        'route' => 'admin.portal.pages.index',
        'sort' => 1,
    ],
    [
        'key' => 'pages.create',
        'name' => 'portal::app.admin.pages.acl.create',
        'route' => 'admin.portal.pages.create',
        'sort' => 2,
    ],
    [
        'key' => 'pages.edit',
        'name' => 'portal::app.admin.pages.acl.edit',
        'route' => 'admin.portal.pages.edit',
        'sort' => 3,
    ],
    [
        'key' => 'pages.delete',
        'name' => 'portal::app.admin.pages.acl.delete',
        'route' => 'admin.portal.pages.delete',
        'sort' => 4,
    ],
    [
        'key' => 'pages.customize',
        'name' => 'portal::app.admin.pages.acl.customize',
        'route' => 'admin.portal.pages.customize',
        'sort' => 5,
    ],
    [
        'key' => 'portal.menus',
        'name' => 'portal::app.admin.menus.acl.title',
        'route' => 'admin.portal.menus.index',
        'sort' => 3,
    ],
    [
        'key' => 'portal.menus.index',
        'name' => 'portal::app.admin.menus.acl.index',
        'route' => 'admin.portal.menus.index',
        'sort' => 1,
    ],
    [
        'key' => 'portal.menus.create',
        'name' => 'portal::app.admin.menus.acl.create',
        'route' => 'admin.portal.menus.store',
        'sort' => 2,
    ],
    [
        'key' => 'portal.menus.builder',
        'name' => 'portal::app.admin.menus.acl.builder',
        'route' => ['admin.portal.menus.builder', 'admin.portal.menus.items.save'],
        'sort' => 3,
    ],
    [
        'key' => 'portal.menus.edit',
        'name' => 'portal::app.admin.menus.acl.edit',
        'route' => 'admin.portal.menus.update',
        'sort' => 4,
    ],
    [
        'key' => 'portal.menus.delete',
        'name' => 'portal::app.admin.menus.acl.delete',
        'route' => 'admin.portal.menus.delete',
        'sort' => 5,
    ],
];
