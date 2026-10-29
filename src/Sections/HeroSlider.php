<?php

namespace Platform\Portal\Sections;

class HeroSlider extends SectionType
{
    protected string $code = 'hero_slider';

    protected ?string $title = 'portal::app.sections.hero_slider.title';

    protected string $icon = 'icon-slider';

    protected ?string $description = 'portal::app.sections.hero_slider.description';

    public function getFields(): array
    {
        return [
            [
                'key' => 'slides',
                'type' => 'repeater',
                'label' => trans('portal::app.sections.hero_slider.slides_label'),
                'add_label' => trans('portal::app.sections.hero_slider.add_slide_btn'),
                'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => trans('portal::app.sections.hero_slider.slide_title')],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => trans('portal::app.sections.hero_slider.slide_subtitle')],
                    ['key' => 'button_text', 'type' => 'text', 'label' => trans('portal::app.sections.hero_slider.button_text')],
                    ['key' => 'button_url', 'type' => 'text', 'label' => trans('portal::app.sections.hero_slider.button_url')],
                    ['key' => 'image', 'type' => 'image', 'label' => trans('portal::app.sections.hero_slider.slide_image')],
                ],
            ],
        ];
    }

    public function getDefaultOptions(): array
    {
        return [
            'slides' => [
                [
                    'title' => trans('portal::app.sections.hero_slider.default_title'),
                    'subtitle' => trans('portal::app.sections.hero_slider.default_subtitle'),
                    'button_text' => trans('portal::app.sections.hero_slider.default_btn'),
                    'button_url' => '#services',
                    'image' => '',
                ],
            ],
        ];
    }
}
