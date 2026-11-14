<?php

namespace Platform\Portal\Sections;

use Webkul\Theme\Enums\SectionTypeEnum;

class CategoryCarouselSection extends SectionType
{
    protected string $code = 'category_carousel';

    protected ?string $title = 'portal::app.sections.category_carousel.title';

    protected string $icon = 'icon-folder';

    protected ?string $description = 'portal::app.sections.category_carousel.description';

    public function getFields(): array
    {
        return [
            ['key' => 'title', 'type' => 'text', 'label' => trans('portal::app.sections.category_carousel.title_label')],
            [
                'key' => 'filters',
                'type' => 'filters',
                'label' => trans('portal::app.sections.category_carousel.filters_label'),
                'add_label' => trans('portal::app.sections.category_carousel.add_filter_btn'),
                'keys' => [
                    ['value' => 'limit', 'label' => 'Limit', 'options' => [
                        ['value' => '4', 'label' => '4'],
                        ['value' => '6', 'label' => '6'],
                        ['value' => '8', 'label' => '8'],
                    ]],
                    ['value' => 'sort', 'label' => 'Sort', 'options' => [
                        ['value' => 'asc', 'label' => 'Ascending'],
                        ['value' => 'desc', 'label' => 'Descending'],
                    ]],
                ],
            ],
        ];
    }

    public function getDefaultOptions(): array
    {
        return [
            'title' => trans('portal::app.sections.category_carousel.default_title'),
            'filters' => [
                'limit' => '6',
                'sort' => 'asc',
            ],
        ];
    }
}
