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
];
