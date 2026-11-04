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

    /**
     * Sanitize rich content HTML to prevent XSS.
     */
    public function sanitize(array $options): array
    {
        if (isset($options['html']) && is_string($options['html'])) {
            $html = $options['html'];

            // Remove script, iframe, object, embed tags
            $html = preg_replace('/<(script|iframe|object|embed)\b[^>]*>(.*?)<\/\1>/is', '', $html);
            $html = preg_replace('/<(script|iframe|object|embed)\b[^>]*\/?>/is', '', $html);

            // Strip inline event attributes (e.g. onclick=, onerror=, onload=)
            $html = preg_replace('/\s*on[a-zA-Z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

            // Strip javascript: pseudo-protocols
            $html = preg_replace('/(href|src)\s*=\s*["\']\s*javascript:[^"\']*["\']/i', '$1="#"', $html);

            $options['html'] = $html;
        }

        if (isset($options['title']) && is_string($options['title'])) {
            $title = preg_replace('/<(script|iframe|object|embed)\b[^>]*>(.*?)<\/\1>/is', '', $options['title']);
            $options['title'] = strip_tags(trim($title));
        }

        return $options;
    }
}
