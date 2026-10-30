<?php

namespace Platform\Portal\Sections;

class HeroScaffold extends SectionType
{
    protected string $code = 'hero_scaffold';

    protected ?string $title = 'portal::app.sections.hero_scaffold.title';

    protected string $icon = 'icon-slider';

    protected ?string $description = 'portal::app.sections.hero_scaffold.description';

    public function getFields(): array
    {
        return [
            ['key' => 'badge_text', 'type' => 'text', 'label' => trans('portal::app.sections.hero_scaffold.badge_text')],
            ['key' => 'badge_version', 'type' => 'text', 'label' => trans('portal::app.sections.hero_scaffold.badge_version')],
            ['key' => 'headline_prefix', 'type' => 'text', 'label' => trans('portal::app.sections.hero_scaffold.headline_prefix')],
            ['key' => 'headline_highlight', 'type' => 'text', 'label' => trans('portal::app.sections.hero_scaffold.headline_highlight')],
            ['key' => 'headline_suffix', 'type' => 'text', 'label' => trans('portal::app.sections.hero_scaffold.headline_suffix')],
            ['key' => 'description', 'type' => 'textarea', 'label' => trans('portal::app.sections.hero_scaffold.description_label')],
            ['key' => 'primary_cta_text', 'type' => 'text', 'label' => trans('portal::app.sections.hero_scaffold.primary_cta_text')],
            ['key' => 'primary_cta_url', 'type' => 'text', 'label' => trans('portal::app.sections.hero_scaffold.primary_cta_url')],
            ['key' => 'secondary_cta_text', 'type' => 'text', 'label' => trans('portal::app.sections.hero_scaffold.secondary_cta_text')],
            ['key' => 'secondary_cta_url', 'type' => 'text', 'label' => trans('portal::app.sections.hero_scaffold.secondary_cta_url')],
            [
                'key' => 'metrics',
                'type' => 'repeater',
                'label' => trans('portal::app.sections.hero_scaffold.metrics_label'),
                'add_label' => trans('portal::app.sections.features_grid.add_item_btn'),
                'fields' => [
                    ['key' => 'code', 'type' => 'text', 'label' => 'Code / Tag (e.g. // ARCH TARGET.01)'],
                    ['key' => 'value', 'type' => 'text', 'label' => 'Value (e.g. 99.95%)'],
                    ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                    ['key' => 'status_color', 'type' => 'text', 'label' => 'Indicator (e.g. bg-[var(--brand-primary)], bg-[var(--brand-accent)], bg-emerald-500)'],
                ],
            ],
        ];
    }

    public function getDefaultOptions(): array
    {
        return [
            'badge_text' => trans('portal::app.sections.hero_scaffold.default_badge'),
            'badge_version' => trans('portal::app.sections.hero_scaffold.default_version'),
            'headline_prefix' => trans('portal::app.sections.hero_scaffold.default_prefix'),
            'headline_highlight' => trans('portal::app.sections.hero_scaffold.default_highlight'),
            'headline_suffix' => trans('portal::app.sections.hero_scaffold.default_suffix'),
            'description' => trans('portal::app.sections.hero_scaffold.default_description'),
            'primary_cta_text' => trans('portal::app.sections.hero_scaffold.default_primary_cta'),
            'primary_cta_url' => '#contact',
            'secondary_cta_text' => trans('portal::app.sections.hero_scaffold.default_secondary_cta'),
            'secondary_cta_url' => '#services',
            'metrics' => [
                [
                    'code' => trans('portal::app.sections.hero_scaffold.metric_1_code'),
                    'value' => trans('portal::app.sections.hero_scaffold.metric_1_value'),
                    'label' => trans('portal::app.sections.hero_scaffold.metric_1_label'),
                    'status_color' => 'bg-[var(--brand-primary)]',
                ],
                [
                    'code' => trans('portal::app.sections.hero_scaffold.metric_2_code'),
                    'value' => trans('portal::app.sections.hero_scaffold.metric_2_value'),
                    'label' => trans('portal::app.sections.hero_scaffold.metric_2_label'),
                    'status_color' => 'bg-[var(--brand-accent)]',
                ],
                [
                    'code' => trans('portal::app.sections.hero_scaffold.metric_3_code'),
                    'value' => trans('portal::app.sections.hero_scaffold.metric_3_value'),
                    'label' => trans('portal::app.sections.hero_scaffold.metric_3_label'),
                    'status_color' => 'bg-emerald-500',
                ],
            ],
        ];
    }
}
