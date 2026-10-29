<?php

namespace Platform\Portal\Sections;

class HeroSlider extends SectionType
{
    protected string $code = 'hero_slider';

    protected ?string $title = 'portal::app.sections.hero_slider.title';

    protected string $icon = 'icon-slider';

    protected ?string $description = 'portal::app.sections.hero_slider.description';

    public function getDefaultOptions(): array
    {
        return [
            'slides' => [
                [
                    'title' => 'مرحباً بك في بوابتنا الرقمية',
                    'subtitle' => 'حلول حديثة وشاملة تمكنك من تحقيق أهدافك بسرعة وسلاسة.',
                    'button_text' => 'استكشف الآن',
                    'button_url' => '#services',
                    'image' => '',
                ],
            ],
        ];
    }
}
