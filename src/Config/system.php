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

    /**
     * Portal Design Tokens Group.
     */
    [
        'key' => 'portal.tokens',
        'name' => 'portal::app.admin.configuration.portal.tokens.title',
        'info' => 'portal::app.admin.configuration.portal.tokens.info',
        'icon' => 'settings/theme.svg',
        'sort' => 5,
    ],

    /**
     * Portal Brand Colors Section.
     */
    [
        'key' => 'portal.tokens.brand',
        'name' => 'portal::app.admin.configuration.portal.tokens.brand.title',
        'info' => 'portal::app.admin.configuration.portal.tokens.brand.info',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'brand_primary',
                'title' => 'portal::app.admin.configuration.portal.tokens.brand.primary',
                'info' => 'portal::app.admin.configuration.portal.tokens.brand.primary-info',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#1c66f6',
            ],
            [
                'name' => 'brand_accent',
                'title' => 'portal::app.admin.configuration.portal.tokens.brand.accent',
                'info' => 'portal::app.admin.configuration.portal.tokens.brand.accent-info',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#fe8312',
            ],
        ],
    ],

    /**
     * Portal Light Mode Surface & Text Tokens Section.
     */
    [
        'key' => 'portal.tokens.light',
        'name' => 'portal::app.admin.configuration.portal.tokens.light.title',
        'info' => 'portal::app.admin.configuration.portal.tokens.light.info',
        'sort' => 2,
        'fields' => [
            [
                'name' => 'bg_app',
                'title' => 'portal::app.admin.configuration.portal.tokens.light.bg-app',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#f8fafc',
            ],
            [
                'name' => 'bg_surface',
                'title' => 'portal::app.admin.configuration.portal.tokens.light.bg-surface',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#ffffff',
            ],
            [
                'name' => 'bg_surface_elevated',
                'title' => 'portal::app.admin.configuration.portal.tokens.light.bg-surface-elevated',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#f1f5f9',
            ],
            [
                'name' => 'text_primary',
                'title' => 'portal::app.admin.configuration.portal.tokens.light.text-primary',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#090d16',
            ],
            [
                'name' => 'text_secondary',
                'title' => 'portal::app.admin.configuration.portal.tokens.light.text-secondary',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#475569',
            ],
            [
                'name' => 'header_bg',
                'title' => 'portal::app.admin.configuration.portal.tokens.light.header-bg',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#ffffff',
            ],
            [
                'name' => 'footer_bg',
                'title' => 'portal::app.admin.configuration.portal.tokens.light.footer-bg',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#0f172a',
            ],
            [
                'name' => 'footer_text',
                'title' => 'portal::app.admin.configuration.portal.tokens.light.footer-text',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#94a3b8',
            ],
        ],
    ],

    /**
     * Portal Dark Mode Surface & Text Tokens Section.
     */
    [
        'key' => 'portal.tokens.dark',
        'name' => 'portal::app.admin.configuration.portal.tokens.dark.title',
        'info' => 'portal::app.admin.configuration.portal.tokens.dark.info',
        'sort' => 3,
        'fields' => [
            [
                'name' => 'dark_bg_app',
                'title' => 'portal::app.admin.configuration.portal.tokens.dark.bg-app',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#080b11',
            ],
            [
                'name' => 'dark_bg_surface',
                'title' => 'portal::app.admin.configuration.portal.tokens.dark.bg-surface',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#0f141f',
            ],
            [
                'name' => 'dark_bg_surface_elevated',
                'title' => 'portal::app.admin.configuration.portal.tokens.dark.bg-surface-elevated',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#161e2e',
            ],
            [
                'name' => 'dark_text_primary',
                'title' => 'portal::app.admin.configuration.portal.tokens.dark.text-primary',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#f8fafc',
            ],
            [
                'name' => 'dark_text_secondary',
                'title' => 'portal::app.admin.configuration.portal.tokens.dark.text-secondary',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#94a3b8',
            ],
            [
                'name' => 'dark_header_bg',
                'title' => 'portal::app.admin.configuration.portal.tokens.dark.header-bg',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#0b0f19',
            ],
            [
                'name' => 'dark_footer_bg',
                'title' => 'portal::app.admin.configuration.portal.tokens.dark.footer-bg',
                'type' => 'color',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '#080b11',
            ],
        ],
    ],

    /**
     * Portal Layout Dimensions, Radius & Custom CSS Section.
     */
    [
        'key' => 'portal.tokens.layout',
        'name' => 'portal::app.admin.configuration.portal.tokens.layout.title',
        'info' => 'portal::app.admin.configuration.portal.tokens.layout.info',
        'sort' => 4,
        'fields' => [
            [
                'name' => 'btn_radius',
                'title' => 'portal::app.admin.configuration.portal.tokens.layout.btn-radius',
                'type' => 'select',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '0.5rem',
                'options' => [
                    ['title' => 'portal::app.admin.configuration.portal.tokens.layout.radius-none', 'value' => '0rem'],
                    ['title' => 'portal::app.admin.configuration.portal.tokens.layout.radius-sm', 'value' => '0.375rem'],
                    ['title' => 'portal::app.admin.configuration.portal.tokens.layout.radius-md', 'value' => '0.5rem'],
                    ['title' => 'portal::app.admin.configuration.portal.tokens.layout.radius-lg', 'value' => '0.75rem'],
                    ['title' => 'portal::app.admin.configuration.portal.tokens.layout.radius-pill', 'value' => '9999px'],
                ],
            ],
            [
                'name' => 'card_radius',
                'title' => 'portal::app.admin.configuration.portal.tokens.layout.card-radius',
                'type' => 'select',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '1rem',
                'options' => [
                    ['title' => 'portal::app.admin.configuration.portal.tokens.layout.radius-none', 'value' => '0rem'],
                    ['title' => 'portal::app.admin.configuration.portal.tokens.layout.radius-md', 'value' => '0.5rem'],
                    ['title' => 'portal::app.admin.configuration.portal.tokens.layout.radius-xl', 'value' => '1rem'],
                    ['title' => 'portal::app.admin.configuration.portal.tokens.layout.radius-2xl', 'value' => '1.5rem'],
                ],
            ],
            [
                'name' => 'container_max_width',
                'title' => 'portal::app.admin.configuration.portal.tokens.layout.container-max-width',
                'type' => 'select',
                'channel_based' => true,
                'locale_based' => false,
                'default' => '1280px',
                'options' => [
                    ['title' => '1140px', 'value' => '1140px'],
                    ['title' => '1280px', 'value' => '1280px'],
                    ['title' => '1440px', 'value' => '1440px'],
                ],
            ],
            [
                'name' => 'custom_css',
                'title' => 'portal::app.admin.configuration.portal.tokens.layout.custom-css',
                'info' => 'portal::app.admin.configuration.portal.tokens.layout.custom-css-info',
                'type' => 'textarea',
                'channel_based' => true,
                'locale_based' => false,
            ],
        ],
    ],
];
