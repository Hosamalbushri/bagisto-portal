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
     * Portal General Settings Group.
     */
    [
        'key' => 'portal.general',
        'name' => 'portal::app.admin.configuration.portal.general.title',
        'info' => 'portal::app.admin.configuration.portal.general.info',
        'icon' => 'settings/store.svg',
        'sort' => 1,
    ],

    /**
     * Portal Storefront Section.
     */
    [
        'key' => 'portal.general.storefront',
        'name' => 'portal::app.admin.configuration.portal.general.storefront.title',
        'info' => 'portal::app.admin.configuration.portal.general.storefront.info',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'enabled',
                'title' => 'portal::app.admin.configuration.portal.general.storefront.enabled',
                'info' => 'portal::app.admin.configuration.portal.general.storefront.enabled-info',
                'type' => 'boolean',
                'channel_based' => true,
                'default' => true,
            ],
        ],
    ],

    /**
     * Portal Authentication Settings Group.
     */
    [
        'key' => 'portal.auth',
        'name' => 'portal::app.admin.configuration.portal.auth.title',
        'info' => 'portal::app.admin.configuration.portal.auth.info',
        'icon' => 'settings/settings.svg',
        'sort' => 2,
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
                'default' => 'web',
                'options' => [
                    [
                        'title' => 'Standard User (web)',
                        'value' => 'web',
                    ],
                    [
                        'title' => 'Portal Member (customer)',
                        'value' => 'customer',
                    ],
                ],
            ],
        ],
    ],
];
