<?php

namespace Platform\Portal\Sections;

class BentoGrid extends SectionType
{
    protected string $code = 'bento_grid';

    protected ?string $title = 'portal::app.sections.bento_grid.title';

    protected string $icon = 'icon-product';

    protected ?string $description = 'portal::app.sections.bento_grid.description';

    public function getFields(): array
    {
        return [
            ['key' => 'badge', 'type' => 'text', 'label' => trans('portal::app.sections.bento_grid.badge')],
            ['key' => 'heading', 'type' => 'text', 'label' => trans('portal::app.sections.bento_grid.heading')],
            ['key' => 'subheading', 'type' => 'textarea', 'label' => trans('portal::app.sections.bento_grid.subheading')],
            ['key' => 'card_1_title', 'type' => 'text', 'label' => trans('portal::app.sections.bento_grid.card_1_title_label')],
            ['key' => 'card_1_desc', 'type' => 'textarea', 'label' => trans('portal::app.sections.bento_grid.card_1_desc_label')],
            ['key' => 'card_1_tag', 'type' => 'text', 'label' => trans('portal::app.sections.bento_grid.card_1_tag_label')],
            ['key' => 'card_2_title', 'type' => 'text', 'label' => trans('portal::app.sections.bento_grid.card_2_title_label')],
            ['key' => 'card_2_desc', 'type' => 'textarea', 'label' => trans('portal::app.sections.bento_grid.card_2_desc_label')],
            ['key' => 'card_2_tag', 'type' => 'text', 'label' => trans('portal::app.sections.bento_grid.card_2_tag_label')],
            ['key' => 'card_3_title', 'type' => 'text', 'label' => trans('portal::app.sections.bento_grid.card_3_title_label')],
            ['key' => 'card_3_desc', 'type' => 'textarea', 'label' => trans('portal::app.sections.bento_grid.card_3_desc_label')],
            ['key' => 'card_3_tag', 'type' => 'text', 'label' => trans('portal::app.sections.bento_grid.card_3_tag_label')],
            ['key' => 'card_4_title', 'type' => 'text', 'label' => trans('portal::app.sections.bento_grid.card_4_title_label')],
            ['key' => 'card_4_desc', 'type' => 'textarea', 'label' => trans('portal::app.sections.bento_grid.card_4_desc_label')],
            ['key' => 'card_4_tag', 'type' => 'text', 'label' => trans('portal::app.sections.bento_grid.card_4_tag_label')],
        ];
    }

    public function getDefaultOptions(): array
    {
        return [
            'badge' => trans('portal::app.sections.bento_grid.default_badge'),
            'heading' => trans('portal::app.sections.bento_grid.default_heading'),
            'subheading' => trans('portal::app.sections.bento_grid.default_subheading'),
            'card_1_title' => trans('portal::app.sections.bento_grid.card_1_title'),
            'card_1_desc' => trans('portal::app.sections.bento_grid.card_1_desc'),
            'card_1_tag' => trans('portal::app.sections.bento_grid.card_1_tag'),
            'card_2_title' => trans('portal::app.sections.bento_grid.card_2_title'),
            'card_2_desc' => trans('portal::app.sections.bento_grid.card_2_desc'),
            'card_2_tag' => trans('portal::app.sections.bento_grid.card_2_tag'),
            'card_3_title' => trans('portal::app.sections.bento_grid.card_3_title'),
            'card_3_desc' => trans('portal::app.sections.bento_grid.card_3_desc'),
            'card_3_tag' => trans('portal::app.sections.bento_grid.card_3_tag'),
            'card_4_title' => trans('portal::app.sections.bento_grid.card_4_title'),
            'card_4_desc' => trans('portal::app.sections.bento_grid.card_4_desc'),
            'card_4_tag' => trans('portal::app.sections.bento_grid.card_4_tag'),
        ];
    }
}
