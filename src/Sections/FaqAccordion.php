<?php

namespace Platform\Portal\Sections;

class FaqAccordion extends SectionType
{
    protected string $code = 'faq_accordion';

    protected ?string $title = 'portal::app.sections.faq_accordion.title';

    protected string $icon = 'icon-information';

    protected ?string $description = 'portal::app.sections.faq_accordion.description';

    public function getFields(): array
    {
        return [
            ['key' => 'heading', 'type' => 'text', 'label' => trans('portal::app.sections.faq_accordion.heading')],
            ['key' => 'subheading', 'type' => 'textarea', 'label' => trans('portal::app.sections.faq_accordion.subheading')],
            [
                'key' => 'items',
                'type' => 'repeater',
                'label' => trans('portal::app.sections.faq_accordion.items_label'),
                'add_label' => trans('portal::app.sections.faq_accordion.add_item_btn'),
                'fields' => [
                    ['key' => 'question', 'type' => 'text', 'label' => trans('portal::app.sections.faq_accordion.item_question')],
                    ['key' => 'answer', 'type' => 'textarea', 'label' => trans('portal::app.sections.faq_accordion.item_answer')],
                ],
            ],
        ];
    }

    public function getDefaultOptions(): array
    {
        return [
            'heading' => trans('portal::app.sections.faq_accordion.default_heading'),
            'subheading' => trans('portal::app.sections.faq_accordion.default_subheading'),
            'items' => [
                [
                    'question' => trans('portal::app.sections.faq_accordion.item_1_q'),
                    'answer' => trans('portal::app.sections.faq_accordion.item_1_a'),
                ],
                [
                    'question' => trans('portal::app.sections.faq_accordion.item_2_q'),
                    'answer' => trans('portal::app.sections.faq_accordion.item_2_a'),
                ],
            ],
        ];
    }
}
