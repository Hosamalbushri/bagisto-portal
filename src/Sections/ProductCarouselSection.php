<?php

namespace Platform\Portal\Sections;

use Webkul\Theme\Enums\SectionTypeEnum;

class ProductCarouselSection extends SectionType
{
    protected string $code = 'product_carousel';

    protected ?string $title = 'portal::app.sections.product_carousel.title';

    protected string $icon = 'icon-product';

    protected ?string $description = 'portal::app.sections.product_carousel.description';

    public function getFields(): array
    {
        return [
            ['key' => 'title', 'type' => 'text', 'label' => trans('portal::app.sections.product_carousel.title_label')],
            ['key' => 'link', 'type' => 'text', 'label' => trans('portal::app.sections.product_carousel.view_all_link')],
            [
                'key' => 'filters',
                'type' => 'filters',
                'label' => trans('portal::app.sections.product_carousel.filters_label'),
                'add_label' => trans('portal::app.sections.product_carousel.add_filter_btn'),
                'keys' => [
                    ['value' => 'limit', 'label' => 'Limit', 'options' => [
                        ['value' => '4', 'label' => '4'],
                        ['value' => '8', 'label' => '8'],
                        ['value' => '12', 'label' => '12'],
                    ]],
                    ['value' => 'featured', 'label' => 'Featured', 'options' => [
                        ['value' => '1', 'label' => 'Yes'],
                        ['value' => '0', 'label' => 'No'],
                    ]],
                    ['value' => 'new', 'label' => 'New', 'options' => [
                        ['value' => '1', 'label' => 'Yes'],
                        ['value' => '0', 'label' => 'No'],
                    ]],
                ],
            ],
        ];
    }

    public function getDefaultOptions(): array
    {
        return [
            'title' => trans('portal::app.sections.product_carousel.default_title'),
            'link' => '',
            'filters' => [
                'limit' => '8',
                'featured' => '1',
            ],
        ];
    }
}
