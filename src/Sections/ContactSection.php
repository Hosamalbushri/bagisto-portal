<?php

namespace Platform\Portal\Sections;

class ContactSection extends SectionType
{
    protected string $code = 'contact_section';

    protected ?string $title = 'portal::app.sections.contact_section.title';

    protected string $icon = 'icon-mail';

    protected ?string $description = 'portal::app.sections.contact_section.description';

    public function getDefaultOptions(): array
    {
        return [
            'heading' => 'تواصل معنا مباشرة',
            'subheading' => 'نسعد دائماً بالإجابة على استفساراتكم ومناقشة مقترحاتكم',
            'email' => 'contact@example.com',
            'phone' => '+966 50 000 0000',
            'address' => 'الرياض، المملكة العربية السعودية',
        ];
    }
}
