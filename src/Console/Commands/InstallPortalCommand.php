<?php

namespace Platform\Portal\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Repositories\PortalMenuItemRepository;
use Platform\Portal\Repositories\PortalMenuRepository;
use Platform\Portal\Repositories\PortalMenuSlotAssignmentRepository;
use Platform\Portal\Repositories\PortalPageRepository;
use Platform\Portal\Repositories\PortalPageSectionRepository;
use Platform\Portal\Services\ThemeManager;
use Webkul\Core\Models\Channel;

class InstallPortalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portal:install
                            {--force : Overwrite existing published files and configurations}
                            {--skip-seed : Skip seeding initial sample home page, sections, and menus}
                            {--stubs : Publish customizable scaffolding stubs to stubs/portal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install, publish, and initialize the Portal Engine out-of-the-box';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=blue;options=bold>┌────────────────────────────────────────────────────────┐</>');
        $this->line('<fg=blue;options=bold>│        Platform Portal Engine — Installation Wizard   │</>');
        $this->line('<fg=blue;options=bold>└────────────────────────────────────────────────────────┘</>');
        $this->newLine();

        $force = (bool) $this->option('force');
        $skipSeed = (bool) $this->option('skip-seed');
        $publishStubs = (bool) $this->option('stubs');

        // Step 1: Run Migrations
        $this->info('📦 1/5 Running database migrations...');
        $migrationsPath = realpath(__DIR__.'/../../Database/Migrations');

        if ($migrationsPath && File::isDirectory($migrationsPath)) {
            $this->call('migrate', [
                '--path' => str_replace(base_path().'/', '', $migrationsPath),
                '--force' => true,
            ]);
        } else {
            $this->call('migrate', ['--force' => true]);
        }

        // Step 2: Publish Configurations & Assets
        $this->newLine();
        $this->info('🚀 2/5 Publishing package configuration and assets...');
        $this->callSilent('vendor:publish', [
            '--tag' => 'portal-config',
            '--force' => $force,
        ]);

        if ($publishStubs) {
            $this->callSilent('vendor:publish', [
                '--tag' => 'portal-stubs',
                '--force' => $force,
            ]);
            $this->line('   <comment>✔ Customizable stubs published to stubs/portal/</comment>');
        }

        // Ensure portal default assets directory exists
        $publicPortalDir = public_path('themes/portal/default');
        if (! File::isDirectory($publicPortalDir)) {
            File::ensureDirectoryExists($publicPortalDir);
        }

        // Step 3: Seed Sample Out-of-the-Box Content
        if (! $skipSeed) {
            $this->newLine();
            $this->info('🌱 3/5 Seeding sample storefront homepage and navigation menus...');
            $this->seedSampleData();
        } else {
            $this->newLine();
            $this->comment('⏩ 3/5 Skipping sample data seeding (--skip-seed provided).');
        }

        // Step 4: Refresh Themes & Clear Caches
        $this->newLine();
        $this->info('⚡ 4/5 Refreshing Theme Registry and clearing compiled views...');
        app(ThemeManager::class)->clearCache();
        $this->callSilent('view:clear');

        // Step 5: Finished
        $this->newLine();
        $this->line('<fg=green;options=bold>🎉 Portal Engine successfully installed and ready for production!</>');
        $this->newLine();
        $this->comment('Quick links:');
        $this->line('   🌐 Storefront:          <info>'.url('/').'</info>');
        $this->line('   🎨 Theme Gallery:        <info>'.url('/admin/appearance/themes').'</info>');
        $this->line('   📑 Pages Management:     <info>'.url('/admin/portal/pages').'</info>');
        $this->line('   🗂️  Menu Navigation:     <info>'.url('/admin/portal/menus').'</info>');
        $this->newLine();
        $this->comment('Artisan commands available:');
        $this->line('   php artisan portal:make-theme <name>        (Scaffold an in-app theme)');
        $this->line('   php artisan portal:make-theme <name> --pkg  (Scaffold a reusable package)');
        $this->line('   php artisan portal:make-section <name>      (Scaffold a custom page section)');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Seed initial sample Home Page, Sections, and Navigation Menus.
     */
    protected function seedSampleData(): void
    {
        $channel = Channel::first() ?? (class_exists('Webkul\Core\Models\Channel') ? Channel::first() : null);
        $channelId = $channel?->id ?? 1;
        $locale = app()->getLocale() ?: 'ar';

        $pageRepo = app(PortalPageRepository::class);
        $sectionRepo = app(PortalPageSectionRepository::class);
        $menuRepo = app(PortalMenuRepository::class);
        $menuItemRepo = app(PortalMenuItemRepository::class);
        $assignmentRepo = app(PortalMenuSlotAssignmentRepository::class);
        $settingsRepo = app(PortalLayoutSettingRepository::class);

        // 1. Seed Sample Home Page if none exists
        $existingHome = $pageRepo->findOneWhere([
            'channel_id' => $channelId,
            'is_home_page' => 1,
        ]);

        if (! $existingHome) {
            $homePage = $pageRepo->create([
                'channel_id' => $channelId,
                'slug' => 'home',
                'is_home_page' => 1,
                'status' => 1,
                'sort_order' => 0,
                $locale => [
                    'title' => 'الصفحة الرئيسية',
                    'meta_title' => 'البوابة الرقمية الموحدة',
                    'meta_description' => 'بوابة رقمية متطورة مصممة بأحدث تقنيات الويب.',
                    'html_content' => '<p>مرحباً بك في البوابة المتطورة.</p>',
                ],
            ]);

            // Add Sections to Home Page
            $sampleSections = [
                [
                    'type' => 'bento_grid',
                    'status' => 1,
                    'sort_order' => 1,
                    'options' => json_encode([
                        'badge' => 'القدرات الرقمية',
                        'title' => 'منظومة حلول برمجية متكاملة',
                        'subtitle' => 'نوفر لك كافة الأدوات والخدمات التي تحتاجها لإدارة حضورك الرقمي بكفاءة.',
                        'features' => [
                            ['title' => 'سرعة وأداء فائق', 'description' => 'بنية تحتية محسنة تضمن سرعة تحميل استثنائية لكافة الصفحات.'],
                            ['title' => 'تصميم متجاوب ومتطور', 'description' => 'واجهات تدعم الوضع الليلي والنهاري وتتوافق مع مختلف الأجهزة.'],
                            ['title' => 'تخصيص كامل بدون كود', 'description' => 'نظام أقسام ديناميكي يسمح ببناء الصفحات والتعديل عليها بلحظات.'],
                        ],
                    ]),
                ],
                [
                    'type' => 'features_grid',
                    'status' => 1,
                    'sort_order' => 2,
                    'options' => json_encode([
                        'title' => 'أبرز مميزات المنصة',
                        'subtitle' => 'صُممت خصيصاً لتمنحك المرونة والتحكم الكامل في تصميم متجرك وموقعك.',
                    ]),
                ],
                [
                    'type' => 'cta_banner',
                    'status' => 1,
                    'sort_order' => 3,
                    'options' => json_encode([
                        'heading' => 'هل أنت مستعد لبدء مشروعك القادم؟',
                        'subheading' => 'انضم إلى آلاف العملاء واكتشف قوة وتطور بوابتنا الرقمية الآن.',
                        'button_text' => 'تواصل معنا الآن',
                        'button_url' => '/contact-us',
                    ]),
                ],
            ];

            foreach ($sampleSections as $secData) {
                $sectionRepo->create(array_merge($secData, [
                    'portal_page_id' => $homePage->id,
                    'channel_id' => $channelId,
                ]));
            }

            $this->line('   <comment>✔ Sample homepage created with Bento Grid, Features, and CTA Banner.</comment>');
        }

        // 2. Seed Default Navigation Menu if none exists
        $existingMenu = $menuRepo->findOneWhere(['slug' => 'main-nav']);
        if (! $existingMenu) {
            $menu = $menuRepo->create([
                'channel_id' => $channelId,
                'slug' => 'main-nav',
                'status' => 1,
                $locale => [
                    'name' => 'القائمة الرئيسية',
                ],
            ]);

            // Add Menu Items
            $items = [
                ['title' => 'الرئيسية', 'url' => '/', 'sort_order' => 1],
                ['title' => 'عن البوابة', 'url' => '/#about', 'sort_order' => 2],
                ['title' => 'خدماتنا', 'url' => '/#services', 'sort_order' => 3],
                ['title' => 'اتصل بنا', 'url' => '/contact-us', 'sort_order' => 4],
            ];

            foreach ($items as $item) {
                $menuItemRepo->create([
                    'portal_menu_id' => $menu->id,
                    'parent_id' => null,
                    'url' => $item['url'],
                    'target' => '_self',
                    'sort_order' => $item['sort_order'],
                    'status' => 1,
                    $locale => [
                        'title' => $item['title'],
                    ],
                ]);
            }

            // Assign to header_main slot
            $assignmentRepo->updateOrCreate(
                [
                    'channel_id' => $channelId,
                    'slot_id' => 'header_main',
                    'theme_code' => 'portal',
                ],
                [
                    'location' => 'header',
                    'portal_menu_id' => $menu->id,
                    'status' => 1,
                ]
            );

            // Also assign for corporate and default fallback
            $assignmentRepo->updateOrCreate(
                [
                    'channel_id' => $channelId,
                    'slot_id' => 'header_main',
                    'theme_code' => 'corporate',
                ],
                [
                    'location' => 'header',
                    'portal_menu_id' => $menu->id,
                    'status' => 1,
                ]
            );

            $this->line('   <comment>✔ Default Navigation Menu created and assigned to [header_main] slot.</comment>');
        }
    }
}
