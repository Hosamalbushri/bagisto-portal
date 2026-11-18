# دليل التوثيق الشامل والمرجع المعماري لحزمة `bagisto-portal`
## (Platform Portal Enterprise Developer & Architecture Manual)

---

## 📑 فهرس المحتويات (Table of Contents)

1. [نظرة عامة وفلسفة التصميم (Overview & Architecture Philosophy)](#1-نظرة-عامة-وفلسفة-التصميم)
2. [متطلبات التشغيل والتثبيت السريع (Installation & Quick Start)](#2-متطلبات-التشغيل-والتثبيت-السريع)
3. [معمارية الحزمة وهيكل الملفات (System Architecture & Directory Structure)](#3-معمارية-الحزمة-وهيكل-الملفات)
4. [محرك الصفحات والأقسام الديناميكية (Modular Pages & Section Engine)](#4-محرك-الصفحات-والأقسام-الديناميكية)
5. [محرك القوائم ومواقع العرض الذكية (Dynamic Menus & AST Slot Engine)](#5-محرك-القوائم-ومواقع-العرض-الذكية)
6. [محرك القوالب واستوديو الهوية البصرية (Theme Engine & Live Design Tokens)](#6-محرك-القوالب-واستوديو-الهوية-البصرية)
7. [مكتبة المكونات والأقسام المدمجة (Built-in Section Types Library)](#7-مكتبة-المكونات-والأقسام-المدمجة)
8. [معمارية فك الارتباط وجسر المتاجر (E-Commerce Decoupled Bridge)](#8-معمارية-فك-الارتباط-وجسر-المتاجر)
9. [حزمة أدوات المطورين وأوامر الـ CLI (Developer CLI & Generator Suite)](#9-حزمة-أدوات-المطورين-وأوامر-الـ-cli)
10. [مرجع الأحداث البرمجية (Domain Events Reference)](#10-مرجع-الأحداث-البرمجية)
11. [توسيع الموديلات عبر Concord Proxies (Model Extensibility)](#11-توسيع-الموديلات-عبر-concord-proxies)
12. [مرجع الإعدادات ومتغيرات البيئة (Configuration & Environment Reference)](#12-مرجع-الإعدادات-ومتغيرات-البيئة)
13. [الأمان، الأداء، وأفضل الممارسات (Security, Performance & Best Practices)](#13-الأمان-والأداء-وأفضل-الممارسات)
14. [الواجهات البرمجية المنفصلة ومحرك الكاش الذري (Headless REST API & Version-Aware Cache)](#14-الواجهات-البرمجية-المنفصلة-ومحرك-الكاش-الذري)
15. [مكونات تجربة المستخدم وهياكل التحميل (Shimmer Loaders & Flash Notifications)](#15-مكونات-تجربة-المستخدم-وهياكل-التحميل)

---

## 1. نظرة عامة وفلسفة التصميم

حزمة **Bagisto Platform Portal** (`platform/portal`) هي محرك عرض وبناء مواقع رقمية عالي الأداء ومستقل تماماً، مصمم خصيصاً لبيئة **Bagisto 2.4.x / Laravel 11 & 12**.

### الفلسفة الأساسية (Core Philosophy):
- **التحرر التام من قيود المتاجر (Domain-Agnostic / No Commerce Lock-In):** على عكس قالب المتجر الافتراضي لـ Bagisto الذي يفترض دائماً وجود منتجات وسلة شراء وطلب، تم تصميم هذا المحرك كمنصة تجربة رقمية نقية (**Digital Experience Platform - DXP**). تتيح للمطورين والشركات بناء:
  - منصات برمجية وسحابية (SaaS & Enterprise Portals).
  - مواقع جهات حكومية وتعليمية وخدمية.
  - مواقع طبية ومراكز استشارية ومحافظ أعمال (Portfolios).
  - متاجر إلكترونية عند الرغبة من خلال جسر اختياري منفصل تماماً.
- **تخصيص كامل بدون الحاجة للبرمجة (Zero-Code Customization):** يستطيع مدير النظام التحكم بالصفحات، القوائم، الأقسام، الهوية البصرية، والألوان فورياً عبر لوحة الإدارة.
- **تغيير الهوية البصرية الحية بدون بناء (Zero-Build Tokens):** تعديل ألوان الواجهة والخطوط ونصف قطر الحواف فوراً عبر متغيرات الـ CSS المتوافقة مع معايير W3C دون الحاجة لتشغيل `npm run build` في خادم الإنتاج.
- **معمارية نظيفة 100% متوافقة مع Bagisto:** استخدام مكتبات ومكونات Bagisto القياسية، ونمط المستودعات (Repository Pattern)، ووكلاء Concord، بدون أي كود دخيل أو أطر عمل ثقيلة.

---

## 2. متطلبات التشغيل والتثبيت السريع

### المتطلبات التقنية:
- **PHP:** `^8.2` أو `^8.3` أو `^8.4`
- **Laravel Framework:** `^11.0` أو `^12.0`
- **Bagisto:** `v2.4.x` (متوافقة كلياً مع Bagisto Core)
- **Node.js & NPM:** Node `>= 18.0` لبناء الأصول الأولية.

### خطوات التثبيت:

#### 1. إضافة مسار الحزمة في ملف `composer.json` الأساسي للتطبيق:
```json
"repositories": [
    {
        "type": "path",
        "url": "packages/Platform/Portal",
        "options": {
            "symlink": true
        }
    }
]
```

#### 2. طلب الحزمة عبر Composer:
```bash
composer require platform/portal
```

#### 3. تشغيل معالج التثبيت المؤتمت (Portal Interactive Installer):
يوفر النظام أمراً مخصصاً يقوم بتهيئة قواعد البيانات، الأصول، والقوائم الافتراضية بنقرة واحدة:
```bash
php artisan portal:install
```

**خيارات أمر التثبيت:**
- `--force`: إعادة نشر ملفات الإعدادات وتجاوز الملفات الموجودة مسبقاً.
- `--skip-seed`: تخطي حقن البيانات التجريبية (موصى به عند التثبيت على بيئات إنتاجية قائمة).
- `--stubs`: نشر ملفات القوالب المرجعية (Stubs) في مجلد `stubs/portal/` لتخصيص مخرجات التوليد.

#### 4. بناء أصول الواجهة (Vite Asset Pipeline):
```bash
cd packages/Platform/Portal
npm install
npm run build
```

---

## 3. معمارية الحزمة وهيكل الملفات

تتبع الحزمة أحدث معايير هندسة البرمجيات المعيارية (Domain-Driven Modular Package):

```text
platform/portal/
├── src/
│   ├── Config/              # تعريفات لوحة التحكم، القوائم، الصلاحيات، وإعدادات المنصة
│   │   ├── acl.php          # أذونات لوحة التحكم (ACL Permissions)
│   │   ├── menu.php         # عناصر القائمة الجانبية في لوحة الإدارة
│   │   ├── portal.php       # الإعدادات العامة (Storefront, Auth, Shop Bridge, Slots)
│   │   └── system.php       # إعدادات النظام المدمجة في لوحة الإدارة
│   ├── Console/             # أوامر Artisan السريعة للمطورين
│   │   ├── Commands/
│   │   │   ├── InstallPortalCommand.php  # أمر التثبيت الشامل
│   │   │   ├── MakeSectionCommand.php    # مولد الأقسام المخصصة
│   │   │   └── MakeThemeCommand.php      # مولد القوالب
│   ├── Contracts/           # واجهات Concord للموديلات لتمكين التوريث
│   ├── Database/            # ملفات التهجير والفهارس
│   │   ├── Migrations/      # تهجيرات الجداول، الفهارس المركبة، وحقول Tokens
│   │   └── Seeders/         # بذور البيانات الافتراضية
│   ├── Facades/             # الواجهات الساكنة (PortalSection Facade)
│   ├── Helpers/             # دوال المساعدة العامة، المصادقة، والتحقق
│   │   ├── PortalAuth.php
│   │   ├── PortalStorefront.php
│   │   └── helpers.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/       # وحدات تحكم لوحة الإدارة (Pages, Menus, Navigation, Themes)
│   │   │   └── ...          # وحدات تحكم الواجهة (Home, Page, Search, Contact, Auth)
│   │   └── Middleware/      # طبقات التصفية (StorefrontStatus, Currency, Locale, Theme)
│   ├── Models/              # الموديلات البرمجية المدعومة بـ Traits الحماية و Tokens
│   ├── Providers/           # مزودات الخدمة (PortalServiceProvider, EventServiceProvider)
│   ├── Repositories/        # طبقة المستودعات لعزل قواعد البيانات
│   ├── Resources/
│   │   ├── assets/          # مصادر CSS, JS, Fonts, Icons
│   │   ├── lang/            # ملفات الترجمة الثنائية (ar, en)
│   │   └── views/           # قوالب Blade للوحة الإدارة والواجهة العامة
│   ├── Rules/               # قواعد التحقق المخصصة (ValidJsonArrayRule)
│   ├── Sections/            # سجل ومكتبة أقسام الواجهة المدمجة
│   │   ├── SectionType.php          # الصنف الأساسي لأي قسم مخصص
│   │   ├── SectionTypeRegistry.php  # السجل المركزي لإدارة وتفعيل الأقسام
│   │   └── ...                      # أصناف الأقسام (Hero, Bento, Features, etc.)
│   └── Services/            # طبقة الخدمات (ThemeManager, MenuSlotScanner, Tokens)
```

---

## 4. محرك الصفحات والأقسام الديناميكية (Modular Pages)

يتيح محرك الصفحات إنشاء عدد لا نهائي من الصفحات المعيارية المستقلة، وتجميع محتواها بصرياً عبر السحب والإفلات.

### دورة حياة الصفحة (Page Lifecycle):
1. **الإنشاء (Creation):** إنشاء صفحة بعنوان، رابط دائم فريد (Slug)، وبيانات تحسين محركات البحث (SEO: Meta Title, Description, Keywords).
2. **الصفحة الرئيسية (Home Page Override):** تفعيل خيار `is_home_page` يجعل الصفحة فورياً هي الصفحة الرئيسية للموقع في النطاق المحدد، وتستبدل الصفحة الافتراضية بدون أي تعديل في المسارات (Routes).
3. **تخصيص المكونات (Visual Customizer):**
   - إضافة أقسام من المكتبة المدمجة أو الأقسام المخصصة.
   - إعادة ترتيب الأقسام بالسحب والإفلات (`Sort Order`).
   - تفعيل وتعطيل أي قسم بلحظة دون حذفه.
4. **نظام المسودة والنشر الفوري (Draft vs Published):**
   - كافة التعديلات تُحفظ كمسودة (`draft_options`) لتمكين المطور أو المسؤول من معاينة التغييرات بأمان.
   - النقر على "نشر التغييرات" (`Publish`) ينقل خيارات المسودة إلى خيارات الإنتاج الحية، ويطلق الحدث `portal.page.published`.
   - النقر على "تراجع" (`Discard`) يعيد المسودة إلى آخر نسخة منشورة.

### دعم تخصيص متغيرات التصميم على مستوى الصفحة والقسم (Tokens Support):
تحتوي جداول الصفحات والأقسام على حقل `tokens` بصيغة JSON، مما يتيح تطبيق ألوان وهوية مخصصة لصفحة بعينها أو لقسم محدد (مثلاً: خلفية داكنة لقسم معين أو أزرار بألوان مغايرة) دون التأثير على بقية الموقع.

---

## 5. محرك القوائم ومواقع العرض الذكية (Dynamic Menus)

تم ابتكار نظام القوائم ليمنح المطور **حرية معمارية مطلقة** دون أي شفرة برمجية معقدة.

### 1. الماسح الثابت لمواقع القوائم (`MenuSlotScanner`):
يقوم محرك المسح بتحليل قوالب Blade في مجلدات القالب النشط باستخدام التعبيرات النمطية (Regex AST Scanner) لاكتشاف أي موقع قائمة تم تعريفه، ويعرضه تلقائياً في لوحة الإدارة!

### 2. كيفية إضافة موقع قائمة جديد في أي ملف Blade:
ببساطة، ضع المكون التالي في أي مكان داخل موقعك (في الهيدر، الفوتر، القائمة الجانبية، أو داخل صفحة مخصصة):

```blade
{{-- استخدام مكون Blade المباشر --}}
<x-portal::menu-slot
    id="sidebar_navigation"
    location="other"
    direction="column"
    class="space-y-2 p-4"
/>

{{-- أو استخدام التوجيه المختصر --}}
@portalMenuSlot('header_top_bar')
```

### 3. تعيين القوائم من لوحة الإدارة:
بمجرد حفظ ملف Blade، يذهب المسؤول إلى **لوحة الإدارة > البوابة الرقمية > تخصيص المظهر والقوائم**، ليجد الموقع الجديد ظاهراً باسمه، ويمكنه:
- ربط أي قائمة هرمية بالموقع.
- تعيين عنوان مخصص للعمود أو الموقع لكل لغة على حدة.
- إخفاء أو إظهار الموقع دون لمس الكود.

### 4. منشئ القوائم الشجري متعدد المستويات (Tree Builder):
- يدعم تفريع القوائم إلى مستويات غير محدودة (Sub-items).
- يدعم الروابط المخصصة والروابط الديناميكية لصفحات البوابة.
- يدعم فتح الروابط في نافذة جديدة (`_blank`) وإعادة الترتيب بنقرة زر.

---

## 6. محرك القوالب واستوديو الهوية البصرية (Theme Engine & Live Tokens)

### 1. اكتشاف القوالب والتوريث (Theme Inheritance):
يدعم المحرك نظامين لبناء القوالب:
- **القوالب الداخلية (`resources/themes/{theme-name}`):** للمشاريع الخاصة التي تعيش داخل نفس التطبيق.
- **حزم القوالب المستقلة (`packages/{Vendor}/{ThemePackage}`):** حزم Composer مستقلة قابلة للبيع والنشر.
- يدعم القالب خاصية التوريث `parent => 'portal'`؛ إذا كان القالب يفتقر لملف عرض معين، يلجأ النظام تلقائياً للملف من القالب الأب.

### 2. استوديو الهوية البصرية اللحظي (Design Token Studio):
يتيح التحكم الكامل بـ 28 متغيراً من متغيرات التصميم تشمل:
- **ألوان الهوية:** الأساسي (`--brand-primary`)، التحويم (`--brand-primary-hover`)، اللون الثانوي (`--brand-accent`).
- **الأسطح والخلفيات:** خلفية التطبيق (`--bg-app`)، البطاقات (`--bg-surface`)، القوائم المنسدلة (`--bg-surface-elevated`).
- **النصوص والحدود:** النصوص الأساسية والفرعية، حدود الإدخال، حلقات التركيز (Focus Rings).
- **الهندسة والخطوط:** نصف قطر الأزرار والبطاقات (`--btn-radius` و `--card-radius`)، خطوط الخط العربي (Cairo)، اللاتيني (Inter)، والبرمجي (Monospace).
- **النمط الداكن (Dark Mode):** متزامن تلقائياً مع الكوكيز عبر مكون `<v-dark>`.

### 3. دعم معايير W3C DTCG:
يتيح النظام تصدير واستيراد متغيرات الهوية البصرية بنقرة واحدة عبر ملفات JSON المتوافقة مع معايير W3C Design Tokens Community Group، مما يتيح لفرق التصميم مزامنة التصاميم من Figma و Style Dictionary مباشرة إلى Bagisto.

---

## 7. مكتبة المكونات والأقسام المدمجة (Built-in Section Types)

تأتي الحزمة محملة بـ **9 مكونات قياسية عامة** صالحة لأي موقع في العالم، ومبرمجة بأعلى معايير الوصول (WCAG AAA):

| كود القسم (Code) | اسم القسم | الوصف والوظيفة |
| :--- | :--- | :--- |
| `hero_scaffold` | **واجهة رئيسية تفاعلية** | واجهة ترحيبية بتأثيرات بصرية راقية، عناوين بارزة، أزرار توجيه، وشريط مؤشرات أداء (Metrics Strip). |
| `bento_grid` | **شبكة المزايا والقدرات** | شبكة 4 ركائز مؤسسية تعرض التخطيط، كفاءة العمليات، التحليلات، وسهولة الوصول وتناغم الخطين العربي والإنجليزي. |
| `hero_slider` | **بانر الشرائح المتحركة** | عارض شرائح متجاوب للصور والعناوين وأزرار الإجراءات مع دعم التحميل السريع (SSR Placeholder). |
| `features_grid` | **شبكة الخدمات والمزايا** | عرض الخدمات في شبكة بطاقات متعددة الأعمدة (2 أو 3 أو 4 أعمدة) مع أيقونات تفاعلية. |
| `faq-accordion` | **الأسئلة الشائعة القابلة للطي** | قائمة أسئلة وأجوبة منسدلة أنيقة ومتوافقة مع المعايير الدلالية ومحركات البحث. |
| `cta_banner` | **شريط الحث على الإجراء (CTA)** | بانر بارز بتدرج لوني يجذب انتباه الزوار نحو اتخاذ إجراء محدد (تسجيل، اتصال، طلب استشارة). |
| `testimonials` | **آراء العملاء والشركاء** | شبكة آراء وتوصيات تستعرض تقييمات المستفيدين بأسلوب عصري موثوق. |
| `rich_content` | **محتوى نصي وتنسيقات حرة** | مساحة حرة للمطورين لكتابة نصوص أو كود HTML مخصص مع دعم إدراج CSS محلي معزول. |
| `contact_section` | **قسم الاتصال والمراسلة** | تفاصيل الاتصال (العنوان، الهاتف، البريد) مقترنة بنموذج اتصال تفاعلي يرسل تنبيهات بريدية عبر AJAX. |

---

## 8. معمارية فك الارتباط وجسر المتاجر (E-Commerce Decoupled Bridge)

لضمان صلاحية الحزمة كمحرك عام لكل المطورين، تم عزل وظائف التجارة الإلكترونية بالكامل:

```mermaid
flowchart TD
    Config["PORTAL_SHOP_ENABLED=true/false"] --> Check{"هل وضع المتجر مفعل؟"}
    Check -->|False (الوضع الافتراضي)| PureMode["Pure Portal Mode<br/>منصة عامة 100%<br/>- لا سلة شراء في الهيدر<br/>- تقتصر المكتبة على الأقسام العامة<br/>- مسارات نقية لأي نشاط"]
    Check -->|True (وضع المتجر)| ShopMode["E-Commerce Bridge Mode<br/>ربط كتالوج وسلة Bagisto<br/>- تفعيل Product Carousel<br/>- تفعيل Category Carousel<br/>- ظهور مصغّر السلة في الهيدر<br/>- مسارات طلبات وحسابات العملاء"]
```

### كيف يعمل الجسر؟
1. **في الوضع النقي (Pure Portal - الافتراضي):**
   - الدالة `portal_shop_enabled()` تُرجع `false`.
   - لا تظهر أي سلة تسوق في الهيدر، ولا تظهر أي منتجات في خيارات بناء الصفحات.
2. **عند تفعيل وضع التجارة (`PORTAL_SHOP_ENABLED=true`):**
   - يقوم `SectionTypeRegistry` تلقائياً بتسجيل قسمين إضافيين:
     - `ProductCarouselSection` (`product_carousel`): شريط ديناميكي يعرض منتجات المتجر مع فلاتر المنتجات المميزة، الأحدث، أو حسب التصنيف.
     - `CategoryCarouselSection` (`category_carousel`): شريط تفاعلي لاستعراض تصنيفات المتجر.
   - تظهر أيقونة السلة التفاعلية ومصغّر السلة في الهيدر تلقائياً.

---

## 9. حزمة أدوات المطورين وأوامر الـ CLI

تحتوي الحزمة على مولدات آلية توفر ساعات من العمل اليدوي:

### 1. توليد قسم مخصص جديد (`portal:make-section`):
```bash
php artisan portal:make-section PricingTable
```
**المخرجات:**
- يُنشئ صنف المعالجة: `app/Portal/Sections/PricingTableSection.php` (مجهز بمخطط الحقول والخيارات الافتراضية).
- يُنشئ قالب Blade للعرض: `resources/views/portal/sections/pricing-table.blade.php`.

**تسجيل القسم في تطبيقك (`AppServiceProvider::boot`):**
```php
use App\Portal\Sections\PricingTableSection;
use Platform\Portal\Facades\PortalSection;

public function boot(): void
{
    PortalSection::register(PricingTableSection::class);
}
```

### 2. توليد قالب داخلي (`portal:make-theme`):
```bash
php artisan portal:make-theme corporate --title="Corporate Portal"
```
ينشئ قالباً داخلياً كاملاً في `resources/themes/corporate` متضمناً `theme.php` ومسارات الأصول ومكونات الهيدر والفوتر الخاصة به.

### 3. توليد حزمة قالب خارجية مستقلة (Standalone Package):
```bash
php artisan portal:make-theme Acme/HospitalTheme --package
```
ينشئ حزمة Composer مستقلة بالكامل داخل `packages/Acme/HospitalTheme` مجهزة بـ `composer.json` ومزود خدمة خاص بها، ويحقنها في التطبيق الرئيسي مباشرة!

---

## 10. مرجع الأحداث البرمجية (Domain Events Reference)

تطلق الحزمة أحداثاً معيارية (Domain Events) عند كل مفترق طرق، مما يسمح لأي إضافة أخرى بالربط والتكامل بسلاسة:

| اسم الحدث | المعطيات (Payload) | التوقيت والوصف |
| :--- | :--- | :--- |
| `portal.page.creating` | `[PortalPage $page]` | يُطلق قبل حفظ الصفحة الجديدة في قاعدة البيانات. |
| `portal.page.created` | `[PortalPage $page]` | يُطلق مباشرة بعد حفظ الصفحة بنجاح. |
| `portal.page.updating` | `[PortalPage $page, array $data]` | يُطلق قبل تحديث بيانات الصفحة. |
| `portal.page.updated` | `[PortalPage $page]` | يُطلق بعد إتمام تحديث بيانات الصفحة. |
| `portal.page.published` | `[int $pageId]` | يُطلق عند اعتماد مسودة الصفحة ونشرها للعامة. |
| `portal.page.deleting` | `[int $pageId]` | يُطلق قبل تنفيذ حذف الصفحة. |
| `portal.page.deleted` | `[int $pageId]` | يُطلق بعد حذف الصفحة من النظام. |
| `portal.theme.activating` | `[int $channelId, string $themeCode]` | يُطلق قبل اعتماد وتفعيل قالب جديد على قناة معينة. |
| `portal.theme.activated` | `[int $channelId, string $themeCode]` | يُطلق فور نجاح تفعيل القالب الجديد. |
| `portal.section.registered` | `[SectionType $section]` | يُطلق عند تسجيل قسم جديد في السجل العام. |
| `portal.section.rendering` | `[PortalPageSection $sec, array &$data]` | يُطلق قبل تصيير القسم في واجهة العرض مباشرة لتعديل المعطيات. |

---

## 11. توسيع الموديلات عبر Concord Proxies

تعتمد الحزمة على معمارية Bagisto Concord. هذا يعني أنه يمكنك استبدال أو توسيع أي موديل من موديلات البوابة في تطبيقك دون تعديل سطر واحد داخل مجلد الباقة:

### مثال: إضافة علاقة أو منطق مخصص لموديل `PortalPage`:

1. أنشئ الموديل الخاص بك:
```php
namespace App\Models;

use Platform\Portal\Models\PortalPage as BasePortalPage;

class CustomPortalPage extends BasePortalPage
{
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function getReadingTimeAttribute(): int
    {
        return ceil(str_word_count(strip_tags($this->html_content)) / 200);
    }
}
```

2. سجل الموديل الجديد في `AppServiceProvider::boot()`:
```php
public function boot(): void
{
    $this->app->concord->registerModel(
        \Platform\Portal\Contracts\PortalPage::class,
        \App\Models\CustomPortalPage::class
    );
}
```
الآن، أي استدعاء لموديل الصفحة في النظام بالكامل سيستخدم صنفك المخصص تلقائياً!

---

## 12. مرجع الإعدادات ومتغيرات البيئة

يتم التحكم بسلوك الحزمة عبر ملف `config/portal.php` ومتغيرات ملف `.env`:

| المتغير في `.env` | القيمة الافتراضية | الوصف |
| :--- | :--- | :--- |
| `PORTAL_STOREFRONT_ENABLED` | `true` | تفعيل الواجهة الأمامية العامة. عند إيقافها، يتم تحويل كافة الزوار لصفحة تسجيل الدخول. |
| `PORTAL_AUTH_ENABLED` | `false` | تفعيل تسجيل الدخول الاختياري في واجهة البوابة (اختياري وغير مقيد). |
| `PORTAL_AUTH_GUARD` | `'web'` | الـ Guard المستخدم للتحقق من الزوار (`web` للمستخدمين، أو `customer` لعملاء Bagisto). |
| `PORTAL_SHOP_ENABLED` | `false` | تفعيل وضع المتجر وربط سلة ومنتجات Bagisto. عند `false` تعمل كبوابة عامة نقية. |
| `PORTAL_ROUTING_MODE` | `'root'` | طريقة تقديم مسار البوابة: `root` (تخدم المسار الرئيسي `/`) أو `prefix` تحت بادئة. |
| `PORTAL_ROUTING_PREFIX` | `'portal'` | اسم البادئة عند ضبط وضع المسارات على `prefix` (مثل: `/portal/about`). |
| `PORTAL_CACHE_TTL` | `300` | مدة كاش استجابات الصفحات بالثواني لضمان سرعة تحميل فائقة. |

---

## 13. الأمان، الأداء، وأفضل الممارسات

1. **تطهير الروابط ومنع ثغرات Open Redirection و XSS:**
   - كافة روابط الأزرار والأقسام تمر عبر دالة `portal_sanitize_url($url)` التي تمنع تنفيذ بروتوكولات الخطر مثل `javascript:` أو المسارات الخارجية غير الموثوقة.
2. **فهارس الأداء المركبة (Database Composite Indexes):**
   - تم تزويد جداول البوابة بفهارس أداء على أعمدة `['channel_id', 'is_home_page', 'status']` و `['slug', 'status']` لضمان استرجاع الصفحات في أقل من **5ms** حتى مع ملايين الزيارات.
3. **تطبيق المعاملات المالية وقواعد التحقق الصارمة:**
   - كافة عمليات الحفظ المعقدة للمسودات محمية بـ `DB::transaction` لمنع أي تلف في حالة انقطاع الاتصال.
   - التحقق من سلامة نصوص JSON عبر صنف التحقق المخصص `ValidJsonArrayRule`.
4. **التوافق التام مع الشاشات والوصول الشامل (A11y & Responsiveness):**
   - تدعم كافة مكونات الواجهة تباين ألوان يتجاوز معايير **WCAG 2.2 AAA**.
   - دعم أصيل ومرن للقراءة من اليمين لليسار ومن اليسار لليمين (**RTL / LTR Symmetry**).

---

## 14. الواجهات البرمجية المنفصلة ومحرك الكاش الذري

توفر حزمة **Bagisto Platform Portal** واجهات برمجية متطورة تتيح استخدامها كـ **Headless CMS**، لبناء تطبيقات الجوال (Flutter / React Native) أو واجهات الويب الحديثة (Next.js / Nuxt / SvelteKit):

### نقاط النهاية المتاحة (Endpoints Reference):

1. **قائمة الصفحات المنشورة:**
   - **الطلب:** `GET /api/portal/pages`
   - **الاستجابة:** قائمة كائنات `PortalPageListResource` تحوي المعرّف، العنوان، المسار (slug)، القالب، والحالة.

2. **تفاصيل الصفحة الكاملة والأقسام:**
   - **الطلب:** `GET /api/portal/pages/{slug}`
   - **الاستجابة:** كائن `PortalPageResource` يحوي كافة بيانات الصفحة، ومعلومات الـ SEO، والـ Design Tokens، وشجرة الأقسام المنشورة مع خياراتها وترجماتها كاملة.

3. **شجرة القوائم الهيكلية (AST Menu):**
   - **الطلب:** `GET /api/portal/menus/{slug}`
   - **الاستجابة:** كائن `PortalMenuResource` يقدّم شجرة العناصر المتداخلة وروابطها وأيقوناتها وأهداف الفتح (`_blank` / `_self`).

4. **أنواع الأقسام المسجلة ومخططاتها (Section Schemas):**
   - **الطلب:** `GET /api/portal/sections`
   - **الاستجابة:** قائمة بجميع الأقسام المسجلة في `SectionTypeRegistry` متضمنة الأيقونة، والوصف، ومخطط الحقول (`fields`)، والخيارات الافتراضية.

### محرك الكاش الذري القائم على الإصدارات (`PortalCache`):
- يضمن استجابة فورية للواجهات البرمجية دون استعلامات قاعدة بيانات مكررة.
- يعتمد على مفتاح ذري `portal_api_version` يتزايد تلقائياً عند أي تعديل أو نشر للصفحات أو القوائم (`Cache::increment`).
- يعزل الكاش تلقائياً حسب القناة (`channel`)، القالب (`theme`)، واللغة (`locale`).

---

## 15. مكونات تجربة المستخدم وهياكل التحميل

### 1. هياكل التحميل التفاعلية (`Shimmer Loaders`):
تمنع مكونات Shimmer ظاهرة انزياح التنسيق التراكمي (CLS) وتقدم تجربة تحميل سلسة وأنيقة:
- `<x-portal::shimmer.hero />`: هيكل تحميل متجاوب لقسم الواجهة الرئيسي.
- `<x-portal::shimmer.bento />`: هيكل تحميل شبكة البطاقات غير المتناظرة (Bento Grid).
- `<x-portal::shimmer.features />`: هيكل تحميل شبكة الميزات ثلاثية الأعمدة.
- `<x-portal::shimmer.page />`: هيكل تحميل كامل يدمج الأقسام الرئيسية.

### 2. نظام التنبيهات والوميض التفاعلي (`Flash Group`):
مكوّن `<x-portal::flash-group />` مدمج داخل تخطيط الصفحة ويوفر:
- التقاط تلقائي لرسائل الجلسة (`session('success')`, `session('error')`, `session('warning')`, `session('info')`).
- استماع لحظي لأحداث واجهة Vue الأمامية عبر ناقل الأحداث:
  ```javascript
  this.$emitter.emit('add-flash', {
      type: 'success',
      message: 'تم إرسال رسالتك بنجاح!'
  });
  ```
- حركات ظهور واختفاء ناعمة (Transitions) مع دعم محاذاة RTL / LTR تلقائياً.

---
*تم إعداد هذا التوثيق ليمثل المرجع الهندسي المتكامل لحزمة `bagisto-portal` كمنصة عامة احترافية تضمن للمطور أقصى درجات المرونة والجودة.*
