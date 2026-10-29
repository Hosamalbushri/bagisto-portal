<?php

namespace Platform\Portal\Sections;

class CtaBanner extends SectionType
{
    protected string $code = 'cta_banner';

    protected ?string $title = 'portal::app.sections.cta_banner.title';

    protected string $icon = 'icon-bell';

    protected ?string $description = 'portal::app.sections.cta_banner.description';

    public function getFields(): array
    {
        return [
            ['key' => 'heading', 'type' => 'text', 'label' => trans('portal::app.sections.cta_banner.heading')],
            ['key' => 'description', 'type' => 'textarea', 'label' => trans('portal::app.sections.cta_banner.description_label')],
            ['key' => 'button_text', 'type' => 'text', 'label' => trans('portal::app.sections.cta_banner.button_text')],
            ['key' => 'button_url', 'type' => 'text', 'label' => trans('portal::app.sections.cta_banner.button_url')],
        ];
    }

    public function getDefaultOptions(): array
    {
        return [
            'heading' => trans('portal::app.sections.cta_banner.default_heading'),
            'description' => trans('portal::app.sections.cta_banner.default_desc'),
            'button_text' => trans('portal::app.sections.cta_banner.default_btn'),
            'button_url' => '/contact-us',
        ];
    }
}
