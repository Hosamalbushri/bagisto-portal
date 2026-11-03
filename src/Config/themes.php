<?php

use Webkul\Theme\Enums\SectionTypeEnum;

return [
    'portal' => [
        'name' => 'Portal Default',
        'author' => 'Platform Portal',
        'version' => '1.0.0',
        'description' => 'Modern, high-performance content engine and portal theme.',
        'assets_path' => 'public/themes/portal/default',
        'views_path' => 'resources/themes/portal/views',
        'views_namespace' => 'portal',

        'vite' => [
            'hot_file' => 'portal-default-vite.hot',
            'build_directory' => 'themes/portal/default/build',
            'package_assets_directory' => 'src/Resources/assets',
        ],

        'customize' => [
            'sections' => [
                SectionTypeEnum::IMAGE_CAROUSEL,
                SectionTypeEnum::SERVICES_CONTENT,
                SectionTypeEnum::STATIC_CONTENT,
                SectionTypeEnum::FOOTER_LINKS,
            ],

            'image_cache' => [
                'templates' => [],
                'placeholders' => [],
            ],
        ],
    ],

    'corporate' => [
        'name' => 'Corporate Portal',
        'author' => 'Platform Portal',
        'version' => '1.0.0',
        'description' => 'High-end technical corporate enterprise theme for portal.',
        'assets_path' => 'public/themes/portal/default',
        'views_path' => 'resources/themes/corporate/views',
        'views_namespace' => 'portal',
        'parent' => 'portal',

        'vite' => [
            'hot_file' => 'portal-default-vite.hot',
            'build_directory' => 'themes/portal/default/build',
            'package_assets_directory' => 'src/Resources/assets',
        ],

        'customize' => [
            'sections' => [
                SectionTypeEnum::IMAGE_CAROUSEL,
                SectionTypeEnum::SERVICES_CONTENT,
                SectionTypeEnum::STATIC_CONTENT,
                SectionTypeEnum::FOOTER_LINKS,
            ],

            'image_cache' => [
                'templates' => [],
                'placeholders' => [],
            ],
        ],
    ],
];
