<?php

namespace Platform\Portal\Sections;

class CtaBanner extends SectionType
{
    protected string $code = 'cta_banner';

    protected ?string $title = 'portal::app.sections.cta_banner.title';

    protected string $icon = 'icon-customer';

    protected ?string $description = 'portal::app.sections.cta_banner.description';

    public function getDefaultOptions(): array
    {
        return [
            'heading' => 'جاهز للانطلاق وتطوير أعمالك اليوم؟',
            'description' => 'انضم إلى مئات المؤسسات التي تعتمد على بوابتنا لتحقيق التميز الرقمي.',
            'button_text' => 'تواصل معنا الآن',
            'button_url' => '/contact-us',
            'background_color' => 'bg-navyBlue',
        ];
    }
}
