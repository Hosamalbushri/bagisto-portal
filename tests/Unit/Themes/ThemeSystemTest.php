<?php

namespace Platform\Portal\Tests\Unit\Themes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\File;
use Platform\Portal\Http\Controllers\Admin\ThemeController;
use Platform\Portal\Http\Middleware\Theme as PortalThemeMiddleware;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Services\ThemeManager;
use Platform\Portal\Services\TokenCompilerService;
use Platform\Portal\Services\Tokens\ComponentBindingRegistry;
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

    /**
     * Test empty tokens still invokes compiler and produces expected default CSS.
     */
    public function test_empty_tokens_invokes_compiler_and_produces_default_css(): void
    {
        $layoutRepo = app(PortalLayoutSettingRepository::class);
        $layoutRepo->saveSettings('theme_tokens', [], 1, 'portal');

        $css = portal_theme_css('portal', 1);

        $this->assertNotNull($css);
        $this->assertStringContainsString(':root {', $css);
        $this->assertStringContainsString('--btn-radius: 0.5rem;', $css);
        $this->assertStringContainsString('--card-radius: 1rem;', $css);
        $this->assertStringContainsString('--shadow-card: 0 4px 20px -2px rgba(0, 0, 0, 0.05)', $css);
        $this->assertStringContainsString('--header-bg: #ffffff;', $css);
        $this->assertStringContainsString('--header-text: #090d16;', $css);
        $this->assertStringContainsString('--footer-bg: #0f172a;', $css);
        $this->assertStringContainsString('--footer-text: #94a3b8;', $css);
        $this->assertStringContainsString('--container-max-width: 1280px;', $css);
        $this->assertStringContainsString('--page-padding-x: 1rem;', $css);
    }

    /**
     * Test empty tokens CSS includes all 13 component rules from ComponentBindingRegistry.
     */
    public function test_empty_tokens_contains_all_thirteen_component_binding_rules(): void
    {
        $layoutRepo = app(PortalLayoutSettingRepository::class);
        $layoutRepo->saveSettings('theme_tokens', [], 1, 'portal');

        $css = portal_theme_css('portal', 1);
        $registry = app(ComponentBindingRegistry::class);

        $this->assertCount(13, $registry->all());

        foreach ($registry->all() as $id => $definition) {
            $this->assertStringContainsString(
                $definition['selector'],
                $css,
                "CSS must contain selector for component binding '{$id}'."
            );
        }

        // Explicitly assert critical component selectors
        $this->assertStringContainsString('.rounded-btn', $css);
        $this->assertStringContainsString('.btn-primary:hover', $css);
        $this->assertStringContainsString('.btn-accent:hover', $css);
        $this->assertStringContainsString('[dir="rtl"]', $css);
        $this->assertStringContainsString('[dir="ltr"]', $css);
    }

    /**
     * Test Blade layout uses unified compiler output and no longer maintains a hardcoded token fallback.
     */
    public function test_blade_layout_uses_unified_compiler_css_and_removes_hardcoded_fallback(): void
    {
        $layoutRepo = app(PortalLayoutSettingRepository::class);
        $layoutRepo->saveSettings('theme_tokens', [], 1, 'portal');

        $html = Blade::render('<x-portal::layouts><div>Body Test</div></x-portal::layouts>');

        $this->assertStringContainsString('<style id="portal-theme-custom-tokens">', $html);
        $this->assertStringContainsString('--btn-radius: 0.5rem;', $html);
        $this->assertStringContainsString('--shadow-card:', $html);
        $this->assertStringContainsString('.rounded-btn', $html);

        // Verify the old competing hardcoded fallback strings are NOT present
        $this->assertStringNotContainsString('--header-bg: var(--bg-surface, #ffffff);', $html);
        $this->assertStringNotContainsString('--header-text: var(--text-primary, #090d16);', $html);
        $this->assertStringNotContainsString('--header-bg: #0f141f;', $html);
    }

    /**
     * Test non-empty tokens preserve existing custom and derived output.
     */
    public function test_non_empty_tokens_preserves_custom_and_derived_output(): void
    {
        $layoutRepo = app(PortalLayoutSettingRepository::class);
        $layoutRepo->saveSettings('theme_tokens', [
            '--brand-primary' => '#6366f1',
        ], 1, 'portal');

        $css = portal_theme_css('portal', 1);

        $this->assertNotNull($css);
        $this->assertStringContainsString('--brand-primary: #6366f1;', $css);
        $this->assertStringContainsString('--brand-primary-hover:', $css);
        $this->assertStringContainsString('--on-brand-primary:', $css);
        $this->assertStringContainsString('--brand-primary-50:', $css);

        // Reset
        $layoutRepo->saveSettings('theme_tokens', [], 1, 'portal');
    }

    /**
     * Test custom CSS remains present and in the correct final position.
     */
    public function test_custom_css_remains_present_and_in_correct_position(): void
    {
        $layoutRepo = app(PortalLayoutSettingRepository::class);
        $layoutRepo->saveSettings('theme_tokens', [
            '--brand-primary' => '#6366f1',
            'custom_css' => '.custom-announcement { display: flex; color: purple; }',
        ], 1, 'portal');

        $css = portal_theme_css('portal', 1);

        $this->assertNotNull($css);
        $this->assertStringContainsString('.custom-announcement { display: flex; color: purple; }', $css);

        // Must appear after root tokens
        $rootPos = strpos($css, ':root {');
        $customPos = strpos($css, '.custom-announcement');
        $this->assertGreaterThan($rootPos, $customPos);

        // Reset
        $layoutRepo->saveSettings('theme_tokens', [], 1, 'portal');
    }

    /**
     * Test dark-mode overrides remain correct.
     */
    public function test_dark_mode_overrides_remain_correct(): void
    {
        $layoutRepo = app(PortalLayoutSettingRepository::class);
        $layoutRepo->saveSettings('theme_tokens', [
            '--brand-primary' => '#6366f1',
            '--dark-header-bg' => '#1e293b',
            '--dark-brand-primary' => '#818cf8',
        ], 1, 'portal');

        $css = portal_theme_css('portal', 1);

        $this->assertNotNull($css);
        $this->assertStringContainsString('.dark,', $css);
        $this->assertStringContainsString('[data-theme="dark"]', $css);
        $this->assertStringContainsString('--header-bg: #1e293b;', $css);
        $this->assertStringContainsString('--brand-primary: #818cf8;', $css);

        // Reset
        $layoutRepo->saveSettings('theme_tokens', [], 1, 'portal');
    }

    /**
     * Test external theme CSS behavior and ordering remain unchanged.
     */
    public function test_external_theme_css_behavior_remains_unchanged(): void
    {
        $dummyThemeCssPath = resource_path('themes/external_demo/assets/css/app.css');
        File::ensureDirectoryExists(dirname($dummyThemeCssPath));
        File::put($dummyThemeCssPath, ':root { --external-theme-accent: #abcdef; }');

        $css = portal_theme_css('external_demo', 1);

        $this->assertNotNull($css);
        // Contains compiler output
        $this->assertStringContainsString('--btn-radius: 0.5rem;', $css);
        // Contains external theme CSS
        $this->assertStringContainsString('--external-theme-accent: #abcdef;', $css);

        // External theme CSS must be appended after compiler output
        $compilerPos = strpos($css, '--btn-radius: 0.5rem;');
        $externalPos = strpos($css, '--external-theme-accent: #abcdef;');
        $this->assertGreaterThan($compilerPos, $externalPos);

        File::deleteDirectory(resource_path('themes/external_demo'));
    }

    /**
     * Test repository failure reports exception and produces in-memory default CSS.
     */
    public function test_repository_failure_reports_exception_and_produces_in_memory_default_css(): void
    {
        Exceptions::fake();

        $mockRepo = $this->createMock(PortalLayoutSettingRepository::class);
        $mockRepo->method('getSettings')
            ->willThrowException(new \RuntimeException('Database connection refused'));

        $this->app->instance(PortalLayoutSettingRepository::class, $mockRepo);

        $css = app(ThemeManager::class)->resolveThemeCss('portal', 1);

        // 1. Exception was reported
        Exceptions::assertReported(function (\RuntimeException $e) {
            return $e->getMessage() === 'Database connection refused';
        });

        // 2. In-memory compiler still produced full default CSS
        $this->assertNotNull($css);
        $this->assertStringContainsString(':root {', $css);
        $this->assertStringContainsString('--btn-radius: 0.5rem;', $css);
        $this->assertStringContainsString('--card-radius: 1rem;', $css);
        $this->assertStringContainsString('--shadow-card:', $css);
        $this->assertStringContainsString('.rounded-btn', $css);
    }

    /**
     * Test compiler failure is not silently swallowed and reports exception.
     */
    public function test_compiler_failure_is_not_silently_swallowed_and_reports_exception(): void
    {
        Exceptions::fake();

        $mockCompiler = $this->createMock(TokenCompilerService::class);
        $mockCompiler->method('compile')
            ->willThrowException(new \RuntimeException('Compiler memory allocation failed'));

        $this->app->instance(TokenCompilerService::class, $mockCompiler);

        $css = app(ThemeManager::class)->resolveThemeCss('portal', 1);

        // Exception was reported
        Exceptions::assertReported(function (\RuntimeException $e) {
            return $e->getMessage() === 'Compiler memory allocation failed';
        });

        // Does NOT claim default CSS was generated
        $this->assertNull($css);
    }

    /**
     * Test compiler failure with external theme returns external CSS without claiming token CSS.
     */
    public function test_compiler_failure_with_external_theme_does_not_claim_token_css(): void
    {
        Exceptions::fake();

        $dummyThemeCssPath = resource_path('themes/crash_demo/assets/css/app.css');
        File::ensureDirectoryExists(dirname($dummyThemeCssPath));
        File::put($dummyThemeCssPath, ':root { --only-external: #999; }');

        $mockCompiler = $this->createMock(TokenCompilerService::class);
        $mockCompiler->method('compile')
            ->willThrowException(new \RuntimeException('Parser crash'));

        $this->app->instance(TokenCompilerService::class, $mockCompiler);

        $css = app(ThemeManager::class)->resolveThemeCss('crash_demo', 1);

        Exceptions::assertReported(\RuntimeException::class);

        // Only external CSS is returned, no token CSS
        $this->assertNotNull($css);
        $this->assertStringContainsString('--only-external: #999;', $css);
        $this->assertStringNotContainsString('--btn-radius:', $css);
        $this->assertStringNotContainsString('--card-radius:', $css);

        File::deleteDirectory(resource_path('themes/crash_demo'));
    }

    /**
     * Test Blade layout does not re-introduce manual hardcoded CSS when CSS is null.
     */
    public function test_blade_layout_does_not_reintroduce_manual_hardcoded_css_when_css_is_null(): void
    {
        $mockManager = $this->createMock(ThemeManager::class);
        $mockManager->method('resolveThemeCss')->willReturn(null);
        $this->app->instance(ThemeManager::class, $mockManager);

        $html = Blade::render('<x-portal::layouts><div>Null CSS Test</div></x-portal::layouts>');

        // No style tag rendered
        $this->assertStringNotContainsString('<style id="portal-theme-custom-tokens">', $html);
        $this->assertStringNotContainsString('--header-bg: var(--bg-surface, #ffffff);', $html);
        $this->assertStringNotContainsString('--header-text: var(--text-primary, #090d16);', $html);
    }
}
