<?php

return [
    /**
     * Root: Storefront Portal.
     */
    [
        'key' => 'portal',
        'name' => 'portal::app.admin.acl.portal',
        'route' => 'admin.portal.pages.index',
        'sort' => 3,
    ],

    /**
     * 1. Pages.
     */
    [
        'key' => 'portal.pages',
        'name' => 'portal::app.admin.acl.pages',
        'route' => 'admin.portal.pages.index',
        'sort' => 1,
    ],
    [
        'key' => 'portal.pages.index',
        'name' => 'portal::app.admin.acl.pages-index',
        'route' => 'admin.portal.pages.index',
        'sort' => 1,
    ],
    [
        'key' => 'portal.pages.create',
        'name' => 'portal::app.admin.acl.pages-create',
        'route' => ['admin.portal.pages.create', 'admin.portal.pages.store'],
        'sort' => 2,
    ],
    [
        'key' => 'portal.pages.edit',
        'name' => 'portal::app.admin.acl.pages-edit',
        'route' => ['admin.portal.pages.edit', 'admin.portal.pages.update', 'admin.portal.pages.mass_update'],
        'sort' => 3,
    ],
    [
        'key' => 'portal.pages.delete',
        'name' => 'portal::app.admin.acl.pages-delete',
        'route' => ['admin.portal.pages.delete', 'admin.portal.pages.mass_delete'],
        'sort' => 4,
    ],
    [
        'key' => 'portal.pages.customize',
        'name' => 'portal::app.admin.acl.pages-customize',
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

    /**
     * 2. Menus.
     */
    [
        'key' => 'portal.menus',
        'name' => 'portal::app.admin.acl.menus',
        'route' => 'admin.portal.menus.index',
        'sort' => 2,
    ],
    [
        'key' => 'portal.menus.index',
        'name' => 'portal::app.admin.acl.menus-index',
        'route' => 'admin.portal.menus.index',
        'sort' => 1,
    ],
    [
        'key' => 'portal.menus.create',
        'name' => 'portal::app.admin.acl.menus-create',
        'route' => ['admin.portal.menus.create', 'admin.portal.menus.store'],
        'sort' => 2,
    ],
    [
        'key' => 'portal.menus.builder',
        'name' => 'portal::app.admin.acl.menus-builder',
        'route' => ['admin.portal.menus.builder', 'admin.portal.menus.items.save'],
        'sort' => 3,
    ],
    [
        'key' => 'portal.menus.edit',
        'name' => 'portal::app.admin.acl.menus-edit',
        'route' => ['admin.portal.menus.edit', 'admin.portal.menus.update'],
        'sort' => 4,
    ],
    [
        'key' => 'portal.menus.delete',
        'name' => 'portal::app.admin.acl.menus-delete',
        'route' => 'admin.portal.menus.delete',
        'sort' => 5,
    ],

    /**
     * 3. Customization.
     */
    [
        'key' => 'portal.customize',
        'name' => 'portal::app.admin.acl.customize',
        'route' => 'admin.portal.navigation.index',
        'sort' => 3,
    ],
    [
        'key' => 'portal.customize.header',
        'name' => 'portal::app.admin.acl.customize-header',
        'route' => [
            'admin.portal.navigation.header',
            'admin.portal.navigation.header.update',
        ],
        'sort' => 1,
    ],
    [
        'key' => 'portal.customize.footer',
        'name' => 'portal::app.admin.acl.customize-footer',
        'route' => [
            'admin.portal.navigation.footer',
            'admin.portal.navigation.footer.update',
        ],
        'sort' => 2,
    ],
    [
        'key' => 'portal.customize.navigation',
        'name' => 'portal::app.admin.acl.customize-navigation',
        'route' => [
            'admin.portal.navigation.index',
            'admin.portal.navigation.header',
            'admin.portal.navigation.header.update',
            'admin.portal.navigation.footer',
            'admin.portal.navigation.footer.update',
            'admin.portal.navigation.update',
        ],
        'sort' => 3,
    ],
    [
        'key' => 'portal.customize.themes',
        'name' => 'portal::app.admin.acl.customize-themes',
        'route' => 'admin.appearance.themes.index',
        'sort' => 2,
    ],
];
