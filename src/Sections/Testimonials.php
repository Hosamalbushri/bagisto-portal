<?php

namespace Platform\Portal\Sections;

class Testimonials extends SectionType
{
    protected string $code = 'testimonials';

    protected ?string $title = 'portal::app.sections.testimonials.title';

    protected string $icon = 'icon-star';

    protected ?string $description = 'portal::app.sections.testimonials.description';

    public function getDefaultOptions(): array
    {
        return [
            'heading' => 'ماذا يقول عملاؤنا عنا',
            'subheading' => 'ثقة شركائنا هي دافعنا الدائم للتطوير والابتكار',
            'items' => [
                [
                    'name' => 'محمد السعيد',
                    'role' => 'المدير التنفيذي - شركة التقنية المتقدمة',
                    'content' => 'تجربة استثنائية وسهولة غير مسبوقة في إدارة وتخصيص البوابة بما يلبي تطلعات عملائنا بدقة.',
                    'avatar' => '',
                ],
                [
                    'name' => 'سارة الأحمد',
                    'role' => 'مديرة العمليات - منصة ريادة',
                    'content' => 'المرونة العالية وسرعة الاستجابة كانتا العامل الأهم في نجاح إطلاق مشروعنا الرقمي في وقت قياسي.',
                    'avatar' => '',
                ],
            ],
        ];
    }
}
