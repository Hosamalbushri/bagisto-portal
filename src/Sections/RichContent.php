<?php

namespace Platform\Portal\Sections;

class RichContent extends SectionType
{
    protected string $code = 'rich_content';

    protected ?string $title = 'portal::app.sections.rich_content.title';

    protected string $icon = 'icon-cms';

    protected ?string $description = 'portal::app.sections.rich_content.description';

    public function getDefaultOptions(): array
    {
        return [
            'title' => 'عنوان المحتوى المخصص',
            'html' => '<p class="text-gray-600 leading-relaxed">أدخل هنا المحتوى النصي المنسق، التوضيحات، أو الشروحات التي ترغب بعرضها للمستخدمين بأسلوب احترافي وجذاب.</p>',
        ];
    }
}
