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
        'route' => ['admin.portal.pages.create', 'admin.portal.pages.store'],
        'sort' => 2,
    ],
    [
        'key' => 'pages.edit',
        'name' => 'portal::app.admin.pages.acl.edit',
        'route' => ['admin.portal.pages.edit', 'admin.portal.pages.update'],
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
        'route' => [
            'admin.portal.pages.customize',
            'admin.portal.pages.sections.store',
            'admin.portal.pages.sections.reorder',
            'admin.portal.pages.publish',
            'admin.portal.pages.discard',
            'admin.portal.pages.sections.fields',
            'admin.portal.pages.sections.draft',
            'admin.portal.pages.sections.media',
            'admin.portal.pages.sections.status',
            'admin.portal.pages.sections.duplicate',
            'admin.portal.pages.sections.delete',
        ],
        'sort' => 5,
    ],
    [
        'key' => 'portal_menus',
        'name' => 'portal::app.admin.menus.acl.title',
        'route' => 'admin.portal.menus.index',
        'sort' => 3,
    ],
    [
        'key' => 'portal_menus.index',
        'name' => 'portal::app.admin.menus.acl.index',
        'route' => 'admin.portal.menus.index',
        'sort' => 1,
    ],
    [
        'key' => 'portal_menus.create',
        'name' => 'portal::app.admin.menus.acl.create',
        'route' => ['admin.portal.menus.create', 'admin.portal.menus.store'],
        'sort' => 2,
    ],
    [
        'key' => 'portal_menus.builder',
        'name' => 'portal::app.admin.menus.acl.builder',
        'route' => ['admin.portal.menus.builder', 'admin.portal.menus.items.save'],
        'sort' => 3,
    ],
    [
        'key' => 'portal_menus.edit',
        'name' => 'portal::app.admin.menus.acl.edit',
        'route' => ['admin.portal.menus.edit', 'admin.portal.menus.update'],
        'sort' => 4,
    ],
    [
        'key' => 'portal_menus.delete',
        'name' => 'portal::app.admin.menus.acl.delete',
        'route' => 'admin.portal.menus.delete',
        'sort' => 5,
    ],
    [
        'key' => 'portal_menus.navigation',
        'name' => 'portal::app.admin.navigation.acl.title',
        'route' => ['admin.portal.navigation.index', 'admin.portal.navigation.update'],
        'sort' => 6,
    ],
];
