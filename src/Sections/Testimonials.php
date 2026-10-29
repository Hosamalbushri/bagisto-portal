<?php

namespace Platform\Portal\Sections;

class Testimonials extends SectionType
{
    protected string $code = 'testimonials';

    protected ?string $title = 'portal::app.sections.testimonials.title';

    protected string $icon = 'icon-star';

    protected ?string $description = 'portal::app.sections.testimonials.description';

    public function getFields(): array
    {
        return [
            ['key' => 'heading', 'type' => 'text', 'label' => trans('portal::app.sections.testimonials.heading')],
            ['key' => 'subheading', 'type' => 'textarea', 'label' => trans('portal::app.sections.testimonials.subheading')],
            [
                'key' => 'items',
                'type' => 'repeater',
                'label' => trans('portal::app.sections.testimonials.items_label'),
                'add_label' => trans('portal::app.sections.testimonials.add_item_btn'),
                'fields' => [
                    ['key' => 'name', 'type' => 'text', 'label' => trans('portal::app.sections.testimonials.author_name')],
                    ['key' => 'role', 'type' => 'text', 'label' => trans('portal::app.sections.testimonials.author_role')],
                    ['key' => 'content', 'type' => 'textarea', 'label' => trans('portal::app.sections.testimonials.item_content')],
                ],
            ],
        ];
    }

    public function getDefaultOptions(): array
    {
        return [
            'heading' => trans('portal::app.sections.testimonials.default_heading'),
            'subheading' => trans('portal::app.sections.testimonials.default_subheading'),
            'items' => [
                [
                    'name' => 'أحمد العتيبي',
                    'role' => 'المدير التنفيذي، شركة أفق التقنية',
                    'content' => 'تجربة استثنائية ونقلة نوعية في كفاءة أعمالنا وتجربة مستخدمينا.',
                ],
                [
                    'name' => 'سارة الشمري',
                    'role' => 'مديرة العمليات، مؤسسة الريادة',
                    'content' => 'المنصة تتميز بسهولة بالغة في التخصيص وسرعة لا مثيل لها.',
                ],
            ],
        ];
    }
}
