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
     * Portal Site Logo & Branding Section.
     */
    [
        'key' => 'portal.general.design',
        'name' => 'portal::app.admin.configuration.portal.general.design.title',
        'info' => 'portal::app.admin.configuration.portal.general.design.info',
        'sort' => 2,
        'fields' => [
            [
                'name' => 'logo_light',
                'title' => 'portal::app.admin.configuration.portal.general.design.logo-light',
                'info' => 'portal::app.admin.configuration.portal.general.design.logo-light-info',
                'type' => 'image',
                'channel_based' => true,
                'locale_based' => true,
                'validation' => 'mimes:bmp,jpeg,jpg,png,webp,svg',
            ],
            [
                'name' => 'logo_dark',
                'title' => 'portal::app.admin.configuration.portal.general.design.logo-dark',
                'info' => 'portal::app.admin.configuration.portal.general.design.logo-dark-info',
                'type' => 'image',
                'channel_based' => true,
                'locale_based' => true,
                'validation' => 'mimes:bmp,jpeg,jpg,png,webp,svg',
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

    /**
     * Portal Header Settings Group.
     */
    [
        'key' => 'portal.header',
        'name' => 'portal::app.admin.configuration.portal.header.title',
        'info' => 'portal::app.admin.configuration.portal.header.info',
        'icon' => 'settings/theme.svg',
        'sort' => 3,
    ],

    /**
     * Portal Header Controls Section.
     */
    [
        'key' => 'portal.header.controls',
        'name' => 'portal::app.admin.configuration.portal.header.controls.title',
        'info' => 'portal::app.admin.configuration.portal.header.controls.info',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'sticky',
                'title' => 'portal::app.admin.configuration.portal.header.controls.sticky',
                'info' => 'portal::app.admin.configuration.portal.header.controls.sticky-info',
                'type' => 'boolean',
                'channel_based' => true,
                'locale_based' => false,
                'default' => true,
            ],
            [
                'name' => 'show_dark_mode',
                'title' => 'portal::app.admin.configuration.portal.header.controls.show-dark-mode',
                'info' => 'portal::app.admin.configuration.portal.header.controls.show-dark-mode-info',
                'type' => 'boolean',
                'channel_based' => true,
                'locale_based' => false,
                'default' => true,
            ],
            [
                'name' => 'show_locales',
                'title' => 'portal::app.admin.configuration.portal.header.controls.show-locales',
                'info' => 'portal::app.admin.configuration.portal.header.controls.show-locales-info',
                'type' => 'boolean',
                'channel_based' => true,
                'locale_based' => false,
                'default' => true,
            ],
            [
                'name' => 'show_search',
                'title' => 'portal::app.admin.configuration.portal.header.controls.show-search',
                'info' => 'portal::app.admin.configuration.portal.header.controls.show-search-info',
                'type' => 'boolean',
                'channel_based' => true,
                'locale_based' => false,
                'default' => true,
            ],
            [
                'name' => 'show_auth',
                'title' => 'portal::app.admin.configuration.portal.header.controls.show-auth',
                'info' => 'portal::app.admin.configuration.portal.header.controls.show-auth-info',
                'type' => 'boolean',
                'channel_based' => true,
                'locale_based' => false,
                'default' => true,
            ],
        ],
    ],

    /**
     * Portal Header Announcement Bar Section.
     */
    [
        'key' => 'portal.header.announcement',
        'name' => 'portal::app.admin.configuration.portal.header.announcement.title',
        'info' => 'portal::app.admin.configuration.portal.header.announcement.info',
        'sort' => 2,
        'fields' => [
            [
                'name' => 'status',
                'title' => 'portal::app.admin.configuration.portal.header.announcement.status',
                'type' => 'boolean',
                'channel_based' => true,
                'locale_based' => false,
                'default' => false,
            ],
            [
                'name' => 'text',
                'title' => 'portal::app.admin.configuration.portal.header.announcement.text',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => true,
            ],
            [
                'name' => 'link',
                'title' => 'portal::app.admin.configuration.portal.header.announcement.link',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => false,
            ],
            [
                'name' => 'bg_color',
                'title' => 'portal::app.admin.configuration.portal.header.announcement.bg-color',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#1e293b',
            ],
            [
                'name' => 'text_color',
                'title' => 'portal::app.admin.configuration.portal.header.announcement.text-color',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#ffffff',
            ],
        ],
    ],

    /**
     * Portal Header Primary Action Button (CTA) Section.
     */
    [
        'key' => 'portal.header.cta',
        'name' => 'portal::app.admin.configuration.portal.header.cta.title',
        'info' => 'portal::app.admin.configuration.portal.header.cta.info',
        'sort' => 3,
        'fields' => [
            [
                'name' => 'enabled',
                'title' => 'portal::app.admin.configuration.portal.header.cta.enabled',
                'info' => 'portal::app.admin.configuration.portal.header.cta.enabled-info',
                'type' => 'boolean',
                'channel_based' => true,
                'locale_based' => false,
                'default' => false,
            ],
            [
                'name' => 'text',
                'title' => 'portal::app.admin.configuration.portal.header.cta.text',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => true,
            ],
            [
                'name' => 'url',
                'title' => 'portal::app.admin.configuration.portal.header.cta.url',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => false,
            ],
            [
                'name' => 'target',
                'title' => 'portal::app.admin.configuration.portal.header.cta.target',
                'type' => 'select',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '_self',
                'options' => [
                    [
                        'title' => 'portal::app.admin.navigation.cta.target-self',
                        'value' => '_self',
                    ],
                    [
                        'title' => 'portal::app.admin.navigation.cta.target-blank',
                        'value' => '_blank',
                    ],
                ],
            ],
        ],
    ],

    /**
     * Portal Footer Settings Group.
     */
    [
        'key' => 'portal.footer',
        'name' => 'portal::app.admin.configuration.portal.footer.title',
        'info' => 'portal::app.admin.configuration.portal.footer.info',
        'icon' => 'settings/store-information.svg',
        'sort' => 4,
    ],

    /**
     * Portal Footer Contact & Brand Section.
     */
    [
        'key' => 'portal.footer.contact',
        'name' => 'portal::app.admin.configuration.portal.footer.contact.title',
        'info' => 'portal::app.admin.configuration.portal.footer.contact.info',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'bio',
                'title' => 'portal::app.admin.configuration.portal.footer.contact.bio',
                'type' => 'textarea',
                'channel_based' => true,
                'locale_based' => true,
            ],
            [
                'name' => 'phone',
                'title' => 'portal::app.admin.configuration.portal.footer.contact.phone',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => false,
            ],
            [
                'name' => 'email',
                'title' => 'portal::app.admin.configuration.portal.footer.contact.email',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => false,
            ],
            [
                'name' => 'address',
                'title' => 'portal::app.admin.configuration.portal.footer.contact.address',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => true,
            ],
        ],
    ],

    /**
     * Portal Footer Social Links Section.
     */
    [
        'key' => 'portal.footer.social',
        'name' => 'portal::app.admin.configuration.portal.footer.social.title',
        'info' => 'portal::app.admin.configuration.portal.footer.social.info',
        'sort' => 2,
        'fields' => [
            [
                'name' => 'status',
                'title' => 'portal::app.admin.configuration.portal.footer.social.status',
                'type' => 'boolean',
                'channel_based' => true,
                'locale_based' => false,
                'default' => true,
            ],
            [
                'name' => 'twitter',
                'title' => 'portal::app.admin.navigation.social-links.twitter',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => false,
            ],
            [
                'name' => 'instagram',
                'title' => 'portal::app.admin.navigation.social-links.instagram',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => false,
            ],
            [
                'name' => 'facebook',
                'title' => 'portal::app.admin.navigation.social-links.facebook',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => false,
            ],
            [
                'name' => 'youtube',
                'title' => 'portal::app.admin.navigation.social-links.youtube',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => false,
            ],
            [
                'name' => 'linkedin',
                'title' => 'portal::app.admin.navigation.social-links.linkedin',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => false,
            ],
            [
                'name' => 'whatsapp',
                'title' => 'portal::app.admin.navigation.social-links.whatsapp',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => false,
            ],
            [
                'name' => 'telegram',
                'title' => 'portal::app.admin.navigation.social-links.telegram',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => false,
            ],
            [
                'name' => 'tiktok',
                'title' => 'portal::app.admin.navigation.social-links.tiktok',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => false,
            ],
        ],
    ],

    /**
     * Portal Footer Copyright Section.
     */
    [
        'key' => 'portal.footer.copyright',
        'name' => 'portal::app.admin.configuration.portal.footer.copyright.title',
        'info' => 'portal::app.admin.configuration.portal.footer.copyright.info',
        'sort' => 3,
        'fields' => [
            [
                'name' => 'text',
                'title' => 'portal::app.admin.configuration.portal.footer.copyright.text',
                'info' => 'portal::app.admin.configuration.portal.footer.copyright.text-info',
                'type' => 'text',
                'channel_based' => true,
                'locale_based' => true,
            ],
        ],
    ],
];
