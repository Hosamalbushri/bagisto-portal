<?php

namespace Platform\Portal\Sections;

class ContactSection extends SectionType
{
    protected string $code = 'contact_section';

    protected ?string $title = 'portal::app.sections.contact_section.title';

    protected string $icon = 'icon-mail';

    protected ?string $description = 'portal::app.sections.contact_section.description';

    public function getFields(): array
    {
        return [
            ['key' => 'heading', 'type' => 'text', 'label' => trans('portal::app.sections.contact_section.heading')],
            ['key' => 'subheading', 'type' => 'textarea', 'label' => trans('portal::app.sections.contact_section.subheading')],
            ['key' => 'email', 'type' => 'text', 'label' => trans('portal::app.sections.contact_section.email')],
            ['key' => 'phone', 'type' => 'text', 'label' => trans('portal::app.sections.contact_section.phone')],
            ['key' => 'address', 'type' => 'textarea', 'label' => trans('portal::app.sections.contact_section.address')],
        ];
    }

    public function getDefaultOptions(): array
    {
        return [
            'heading' => trans('portal::app.sections.contact_section.default_heading'),
            'subheading' => trans('portal::app.sections.contact_section.default_subheading'),
            'email' => 'contact@example.com',
            'phone' => '+966 50 000 0000',
            'address' => 'الرياض، المملكة العربية السعودية',
        ];
    }
}
