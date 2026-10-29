<?php

namespace Platform\Portal\Sections;

class FaqAccordion extends SectionType
{
    protected string $code = 'faq_accordion';

    protected ?string $title = 'portal::app.sections.faq_accordion.title';

    protected string $icon = 'icon-attribute';

    protected ?string $description = 'portal::app.sections.faq_accordion.description';

    public function getDefaultOptions(): array
    {
        return [
            'heading' => 'الأسئلة الشائعة',
            'subheading' => 'إجابات على أهم الاستفسارات التي قد تخطر ببالك',
            'items' => [
                [
                    'question' => 'كيف يمكنني البدء في استخدام البوابة؟',
                    'answer' => 'يمكنك البدء مباشرة بإنشاء حساب أو التواصل مع فريقنا لاختيار الخطة الأنسب لاحتياجاتك.',
                ],
                [
                    'question' => 'هل تدعم البوابة التخصيص الكامل؟',
                    'answer' => 'نعم، المنصة مبنية بهندسة مرنة تتيح لك تخصيص كل صفحة ومكون بسهولة عبر لوحة التحكم.',
                ],
            ],
        ];
    }
}
