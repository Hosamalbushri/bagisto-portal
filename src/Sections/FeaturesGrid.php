<?php

namespace Platform\Portal\Sections;

class FeaturesGrid extends SectionType
{
    protected string $code = 'features_grid';

    protected ?string $title = 'portal::app.sections.features_grid.title';

    protected string $icon = 'icon-store';

    protected ?string $description = 'portal::app.sections.features_grid.description';

    public function getFields(): array
    {
        return [
            ['key' => 'heading', 'type' => 'text', 'label' => trans('portal::app.sections.features_grid.heading')],
            ['key' => 'subheading', 'type' => 'textarea', 'label' => trans('portal::app.sections.features_grid.subheading')],
            ['key' => 'columns', 'type' => 'number', 'label' => trans('portal::app.sections.features_grid.columns')],
            [
                'key' => 'items',
                'type' => 'repeater',
                'label' => trans('portal::app.sections.features_grid.items_label'),
                'add_label' => trans('portal::app.sections.features_grid.add_item_btn'),
                'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => trans('portal::app.sections.features_grid.feature_title')],
                    ['key' => 'description', 'type' => 'textarea', 'label' => trans('portal::app.sections.features_grid.feature_desc')],
                    ['key' => 'icon', 'type' => 'text', 'label' => trans('portal::app.sections.features_grid.feature_icon')],
                ],
            ],
        ];
    }

    public function getDefaultOptions(): array
    {
        return [
            'heading' => trans('portal::app.sections.features_grid.default_heading'),
            'subheading' => trans('portal::app.sections.features_grid.default_subheading'),
            'columns' => 3,
            'items' => [
                [
                    'title' => trans('portal::app.sections.features_grid.item_1_title'),
                    'description' => trans('portal::app.sections.features_grid.item_1_desc'),
                    'icon' => 'icon-flash',
                ],
                [
                    'title' => trans('portal::app.sections.features_grid.item_2_title'),
                    'description' => trans('portal::app.sections.features_grid.item_2_desc'),
                    'icon' => 'icon-shield',
                ],
                [
                    'title' => trans('portal::app.sections.features_grid.item_3_title'),
                    'description' => trans('portal::app.sections.features_grid.item_3_desc'),
                    'icon' => 'icon-support',
                ],
            ],
        ];
    }
}
