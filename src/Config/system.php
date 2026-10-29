<?php

return [
    /**
     * Portal Root.
     */
    [
        'key' => 'portal',
        'name' => 'portal::app.admin.configuration.portal.title',
        'info' => 'portal::app.admin.configuration.portal.info',
        'sort' => 10,
    ],

    /**
     * Portal Authentication Settings Group.
     */
    [
        'key' => 'portal.auth',
        'name' => 'portal::app.admin.configuration.portal.auth.title',
        'info' => 'portal::app.admin.configuration.portal.auth.info',
        'icon' => 'settings/settings.svg',
        'sort' => 1,
    ],

    /**
     * Portal Authentication Section.
     */
    [
        'key' => 'portal.auth.settings',
        'name' => 'portal::app.admin.configuration.portal.auth.settings.title',
        'info' => 'portal::app.admin.configuration.portal.auth.settings.info',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'enabled',
                'title' => 'portal::app.admin.configuration.portal.auth.settings.enabled',
                'type' => 'boolean',
                'channel_based' => true,
                'default' => false,
            ],
            [
                'name' => 'guard',
                'title' => 'portal::app.admin.configuration.portal.auth.settings.guard',
                'info' => 'portal::app.admin.configuration.portal.auth.settings.guard-info',
                'type' => 'select',
                'channel_based' => true,
                'default' => 'customer',
                'options' => [
                    [
                        'title' => 'Customer (Webkul Customer)',
                        'value' => 'customer',
                    ],
                    [
                        'title' => 'Web (Standard Laravel User)',
                        'value' => 'web',
                    ],
                ],
            ],
        ],
    ],
];
