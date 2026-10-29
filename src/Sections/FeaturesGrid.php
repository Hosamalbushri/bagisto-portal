<?php

namespace Platform\Portal\Sections;

class FeaturesGrid extends SectionType
{
    protected string $code = 'features_grid';

    protected ?string $title = 'portal::app.sections.features_grid.title';

    protected string $icon = 'icon-product';

    protected ?string $description = 'portal::app.sections.features_grid.description';

    public function getDefaultOptions(): array
    {
        return [
            'heading' => 'مميزاتنا وخدماتنا',
            'subheading' => 'نقدم أفضل الحلول المصممة خصيصاً لتلبية متطلباتك',
            'columns' => 3,
            'items' => [
                [
                    'icon' => 'icon-settings',
                    'title' => 'أداء فائق واستجابة سريعة',
                    'description' => 'بنية تحتية متطورة تضمن سرعة التحميل وتجربة مستخدم لا تضاهى.',
                ],
                [
                    'icon' => 'icon-security',
                    'title' => 'أمان وحماية موثوقة',
                    'description' => 'أعلى معايير التشفير والحماية لبياناتك وعملياتك اليومية.',
                ],
                [
                    'icon' => 'icon-support',
                    'title' => 'دعم فني متواصل 24/7',
                    'description' => 'فريق متخصص جاهز لمساعدتك وحل جميع استفساراتك على مدار الساعة.',
                ],
            ],
        ];
    }
}
