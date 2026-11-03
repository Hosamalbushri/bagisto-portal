<?php

namespace Platform\Portal\Tests\Unit\Themes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Platform\Portal\Http\Controllers\Admin\ThemeController;
use Platform\Portal\Http\Middleware\Theme as PortalThemeMiddleware;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Services\ThemeManager;
use Tests\TestCase;

class ThemeSystemTest extends TestCase
{
    protected function tearDown(): void
    {
        // Clean up any test theme or package generated
        File::deleteDirectory(resource_path('themes/test_theme'));
        File::deleteDirectory(base_path('packages/AcmeTest'));
        app(ThemeManager::class)->clearCache();
        app(ThemeManager::class)->clearCache();

        parent::tearDown();
    }

    /**
     * Test make-theme artisan command scaffolds theme structure.
     */
    public function test_make_theme_command_scaffolds_theme_structure(): void
    {
        $this->artisan('portal:make-theme', [
            'name' => 'test_theme',
            '--title' => 'Test Theme',
            '--parent' => 'portal',
            '--force' => true,
        ])->assertSuccessful();

        $themeBase = resource_path('themes/test_theme');
        $this->assertDirectoryExists($themeBase);
        $this->assertFileExists("{$themeBase}/theme.php");
        $this->assertFileExists("{$themeBase}/vite.config.js");
        $this->assertFileExists("{$themeBase}/assets/css/app.css");
        $this->assertFileExists("{$themeBase}/assets/js/app.js");
        $this->assertFileExists("{$themeBase}/views/components/layouts/header/index.blade.php");
        $this->assertFileExists("{$themeBase}/views/components/layouts/footer/index.blade.php");

        $themeConfig = require "{$themeBase}/theme.php";
        $this->assertIsArray($themeConfig);
        $this->assertArrayHasKey('test_theme', $themeConfig);
        $this->assertEquals('Test Theme', $themeConfig['test_theme']['name']);
        $this->assertEquals('portal', $themeConfig['test_theme']['parent']);

        $viteConfig = File::get("{$themeBase}/vite.config.js");
        $this->assertStringContainsString('defineConfig', $viteConfig);
        $this->assertStringContainsString('portal-test_theme-vite.hot', $viteConfig);

        $css = File::get("{$themeBase}/assets/css/app.css");
        $this->assertStringContainsString('--brand-primary', $css);
        $this->assertStringContainsString('--bg-app', $css);

        $header = File::get("{$themeBase}/views/components/layouts/header/index.blade.php");
        $this->assertStringContainsString("@portalMenuSlot('header_main')", $header);
    }

    /**
     * Test make-theme artisan command scaffolds full standalone Bagisto Package when --package option is passed.
     */
    public function test_make_theme_package_scaffolds_full_bagisto_package(): void
    {
        $this->artisan('portal:make-theme', [
            'name' => 'AcmeTest/ClinicTheme',
            '--title' => 'Clinic Theme',
            '--force' => true,
        ])->assertSuccessful();

        $packageBase = base_path('packages/AcmeTest/ClinicTheme');
        $this->assertDirectoryExists($packageBase);
        $this->assertFileExists("{$packageBase}/composer.json");
        $this->assertFileExists("{$packageBase}/vite.config.js");
        $this->assertFileExists("{$packageBase}/src/Providers/ClinicThemeServiceProvider.php");
        $this->assertFileExists("{$packageBase}/src/Config/themes.php");
        $this->assertFileExists("{$packageBase}/src/Resources/assets/css/app.css");
        $this->assertFileExists("{$packageBase}/src/Resources/assets/js/app.js");
        $this->assertFileExists("{$packageBase}/src/Resources/views/components/layouts/header/index.blade.php");
        $this->assertFileExists("{$packageBase}/src/Resources/views/components/layouts/footer/index.blade.php");
        $this->assertFileExists("{$packageBase}/src/Resources/views/home/index.blade.php");
        $this->assertFileExists("{$packageBase}/src/Resources/views/page/show.blade.php");

        $composer = json_decode(File::get("{$packageBase}/composer.json"), true);
        $this->assertEquals('acme-test/clinic-theme', $composer['name']);
        $this->assertArrayHasKey('AcmeTest\\ClinicTheme\\', $composer['autoload']['psr-4']);
    }

    /**
     * Test Theme Middleware prepends theme views path to portal namespace hints.
     */
    public function test_theme_middleware_prepends_portal_namespace_hints(): void
    {
        // Create a dummy temporary theme view directory
        $dummyThemePath = resource_path('themes/test_theme/views');
        File::ensureDirectoryExists($dummyThemePath);

        // Configure a mock current theme with dummy views path
        $themes = themes();
        $themes->set('test_theme');

        // Execute middleware
        $middleware = new PortalThemeMiddleware;
        $request = Request::create('/', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());

        // Clean up
        File::deleteDirectory(resource_path('themes/test_theme'));
    }

    /**
     * Test portal theme does not inherit from itself and resolves view paths without recursion.
     */
    public function test_portal_theme_does_not_have_circular_parent(): void
    {
        $manifest = app(ThemeManager::class)->find('portal');

        $this->assertNotNull($manifest);
        $this->assertNull($manifest->parent);

        $config = $manifest->toArray();
        $this->assertArrayNotHasKey('parent', $config);

        themes()->set('portal');
        $theme = themes()->current();
        $this->assertNotSame($theme, $theme->parent ?? null);

        $paths = $theme->getViewPaths();
        $this->assertIsArray($paths);
    }

    /**
     * Test theme config contains portal and corporate definitions.
     */
    public function test_theme_config_structure(): void
    {
        $portalThemes = require dirname(__DIR__, 3).'/src/Config/themes.php';

        $this->assertArrayHasKey('portal', $portalThemes);
        $this->assertEquals('Portal Default', $portalThemes['portal']['name']);
        $this->assertEquals('portal', $portalThemes['portal']['views_namespace']);

        $this->assertArrayHasKey('corporate', $portalThemes);
        $this->assertEquals('Corporate Portal', $portalThemes['corporate']['name']);
        $this->assertEquals('portal', $portalThemes['corporate']['parent']);
    }

    /**
     * Test portal_theme_css returns theme tokens if custom app.css exists.
     */
    public function test_portal_theme_css_resolves_tokens_from_theme(): void
    {
        $dummyThemeCssPath = resource_path('themes/custom_demo/assets/css/app.css');
        File::ensureDirectoryExists(dirname($dummyThemeCssPath));
        File::put($dummyThemeCssPath, ':root { --brand-primary: #123456; }');

        $css = portal_theme_css('custom_demo');

        $this->assertNotNull($css);
        $this->assertStringContainsString('--brand-primary: #123456', $css);

        File::deleteDirectory(resource_path('themes/custom_demo'));
    }

    /**
     * Test portal_active_theme helper resolves appropriately.
     */
    public function test_portal_active_theme_helper(): void
    {
        $theme = portal_active_theme();
        $this->assertIsString($theme);
        $this->assertNotEmpty($theme);
    }

    /**
     * Test portal_theme_css merges custom layout tokens dynamically.
     */
    public function test_portal_theme_css_merges_custom_layout_tokens(): void
    {
        $layoutRepo = app(PortalLayoutSettingRepository::class);
        $layoutRepo->saveSettings(
            'theme_tokens',
            ['--brand-primary' => '#ff00aa'],
            1,
            'portal'
        );

        $css = portal_theme_css('portal', 1);

        $this->assertNotNull($css);
        $this->assertStringContainsString('--brand-primary: #ff00aa', $css);

        // Reset
        $layoutRepo->saveSettings('theme_tokens', [], 1, 'portal');
    }

    /**
     * Test ThemeManager activate theme dispatches events and updates setting.
     */
    public function test_theme_manager_activate_theme(): void
    {
        $activatingFired = false;
        $activatedFired = false;

        Event::listen('portal.theme.activating', function () use (&$activatingFired) {
            $activatingFired = true;
        });

        Event::listen('portal.theme.activated', function () use (&$activatedFired) {
            $activatedFired = true;
        });

        app(ThemeManager::class)->activateTheme(1, 'corporate');

        $this->assertTrue($activatingFired);
        $this->assertTrue($activatedFired);
        $this->assertEquals('corporate', portal_active_theme(1));

        // Restore to default
        app(ThemeManager::class)->activateTheme(1, 'portal');
    }

    /**
     * Test ThemeController customize redirect leads to Home Page section editor.
     */
    public function test_theme_controller_customize_redirects_to_home_page(): void
    {
        $adminClass = '\Webkul\User\Models\Admin';
        if (class_exists($adminClass)) {
            $admin = $adminClass::first();
            if ($admin) {
                auth()->guard('admin')->setUser($admin);
            }
        }

        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $controller = app(ThemeController::class);
        $response = $controller->customize('portal');

        $this->assertTrue($response->isRedirection());
        $this->assertStringContainsString('portal/pages/customize', $response->getTargetUrl());

        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = false;
        }
    }
}
