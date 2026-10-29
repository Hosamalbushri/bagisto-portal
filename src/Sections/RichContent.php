<?php

namespace Platform\Portal\Sections;

class RichContent extends SectionType
{
    protected string $code = 'rich_content';

    protected ?string $title = 'portal::app.sections.rich_content.title';

    protected string $icon = 'icon-cms';

    protected ?string $description = 'portal::app.sections.rich_content.description';

    public function getFields(): array
    {
        return [
            ['key' => 'title', 'type' => 'text', 'label' => trans('portal::app.sections.rich_content.title_label')],
            ['key' => 'html', 'type' => 'code', 'language' => 'html', 'label' => trans('portal::app.sections.rich_content.html_label')],
        ];
    }

    public function getDefaultOptions(): array
    {
        return [
            'title' => trans('portal::app.sections.rich_content.default_title'),
            'html' => trans('portal::app.sections.rich_content.default_html'),
        ];
    }
}
