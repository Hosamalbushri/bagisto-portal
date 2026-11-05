<?php

namespace Platform\Portal\Tests\Unit\Themes;

use Illuminate\Http\Request;
use Mockery;
use Platform\Portal\Http\Controllers\Admin\ThemeController;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Repositories\PortalPageRepository;
use Platform\Portal\Services\ThemeManager;
use Platform\Portal\Services\ThemeTokenService;
use Platform\Portal\Services\TokenCompilerService;
use Tests\TestCase;

class ThemeControllerTokenMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }
    }

    protected function tearDown(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = false;
        }

        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test getTokens delegates to ThemeTokenService.
     */
    public function test_get_tokens_delegates_to_theme_token_service(): void
    {
        $mockService = Mockery::mock(ThemeTokenService::class);
        $mockService->shouldReceive('getTokens')
            ->once()
            ->with(1, 'portal')
            ->andReturn(['--brand-primary' => '#123456']);

        $controller = new ThemeController(
            app(ThemeManager::class),
            app(PortalLayoutSettingRepository::class),
            app(PortalPageRepository::class),
            app(TokenCompilerService::class),
            $mockService
        );

        $request = Request::create('/admin/portal/themes/tokens', 'GET', [
            'channel_id' => 1,
            'theme_code' => 'portal',
        ]);

        $response = $controller->getTokens($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(['--brand-primary' => '#123456'], $data['tokens']);
    }

    /**
     * Test exportTokens delegates to ThemeTokenService for reading tokens.
     */
    public function test_export_tokens_delegates_to_theme_token_service(): void
    {
        $mockService = Mockery::mock(ThemeTokenService::class);
        $mockService->shouldReceive('getTokens')
            ->once()
            ->with(1, 'portal')
            ->andReturn(['--brand-primary' => '#123456']);

        $controller = new ThemeController(
            app(ThemeManager::class),
            app(PortalLayoutSettingRepository::class),
            app(PortalPageRepository::class),
            app(TokenCompilerService::class),
            $mockService
        );

        $request = Request::create('/admin/portal/themes/tokens/export', 'GET', [
            'channel_id' => 1,
            'theme_code' => 'portal',
        ]);

        $response = $controller->exportTokens($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('color', $data);
        $this->assertEquals('#123456', $data['color']['brand-primary']['$value']);
    }

    /**
     * Test applyPreset delegates to ThemeTokenService and handles success and not found.
     */
    public function test_apply_preset_delegates_to_theme_token_service(): void
    {
        $mockService = Mockery::mock(ThemeTokenService::class);
        $mockService->shouldReceive('applyPreset')
            ->once()
            ->with(1, 'portal', 'emerald_tech')
            ->andReturn(['--brand-primary' => '#059669']);

        $mockService->shouldReceive('applyPreset')
            ->once()
            ->with(1, 'portal', 'invalid_preset')
            ->andReturn(null);

        $controller = new ThemeController(
            app(ThemeManager::class),
            app(PortalLayoutSettingRepository::class),
            app(PortalPageRepository::class),
            app(TokenCompilerService::class),
            $mockService
        );

        // Success case
        $request = Request::create('/admin/portal/themes/tokens/apply-preset', 'POST', [
            'channel_id' => 1,
            'theme_code' => 'portal',
            'preset' => 'emerald_tech',
        ]);
        $response = $controller->applyPreset($request);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('#059669', $data['tokens']['--brand-primary']);

        // Not found case (404)
        $request404 = Request::create('/admin/portal/themes/tokens/apply-preset', 'POST', [
            'channel_id' => 1,
            'theme_code' => 'portal',
            'preset' => 'invalid_preset',
        ], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $response404 = $controller->applyPreset($request404);
        $this->assertEquals(404, $response404->getStatusCode());
    }

    /**
     * Test importTokens delegates to ThemeTokenService and preserves HTTP status codes.
     */
    public function test_import_tokens_delegates_to_theme_token_service(): void
    {
        $mockService = Mockery::mock(ThemeTokenService::class);
        $mockService->shouldReceive('importDtcg')
            ->once()
            ->with(1, 'portal', ['color' => ['brand-primary' => ['$value' => '#0284c7']]])
            ->andReturn(['--brand-primary' => '#0284c7']);

        $mockService->shouldReceive('importDtcg')
            ->once()
            ->with(1, 'portal', ['empty' => 'data'])
            ->andReturn([]);

        $controller = new ThemeController(
            app(ThemeManager::class),
            app(PortalLayoutSettingRepository::class),
            app(PortalPageRepository::class),
            app(TokenCompilerService::class),
            $mockService
        );

        // Success case
        $request = Request::create('/admin/portal/themes/tokens/import', 'POST', [
            'channel_id' => 1,
            'theme_code' => 'portal',
            'json' => json_encode(['color' => ['brand-primary' => ['$value' => '#0284c7']]]),
        ]);
        $response = $controller->importTokens($request);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('#0284c7', $data['tokens']['--brand-primary']);

        // Empty tokens case (422)
        $requestEmpty = Request::create('/admin/portal/themes/tokens/import', 'POST', [
            'channel_id' => 1,
            'theme_code' => 'portal',
            'json' => json_encode(['empty' => 'data']),
        ]);
        $responseEmpty = $controller->importTokens($requestEmpty);
        $this->assertEquals(422, $responseEmpty->getStatusCode());

        // Invalid JSON case (422)
        $requestInvalidJson = Request::create('/admin/portal/themes/tokens/import', 'POST', [
            'channel_id' => 1,
            'theme_code' => 'portal',
            'json' => '{not a valid json}',
        ]);
        $responseInvalidJson = $controller->importTokens($requestInvalidJson);
        $this->assertEquals(422, $responseInvalidJson->getStatusCode());
    }

    /**
     * Test customizeTokens delegates to ThemeTokenService with custom_css argument.
     */
    public function test_customize_tokens_delegates_to_theme_token_service(): void
    {
        $mockService = Mockery::mock(ThemeTokenService::class);
        $mockService->shouldReceive('customizeTokens')
            ->once()
            ->with(
                1,
                'portal',
                ['--brand-primary' => '#0284c7'],
                '.header-glow { color: red; }'
            )
            ->andReturn([
                '--brand-primary' => '#0284c7',
                'custom_css' => '.header-glow { color: red; }',
            ]);

        $controller = new ThemeController(
            app(ThemeManager::class),
            app(PortalLayoutSettingRepository::class),
            app(PortalPageRepository::class),
            app(TokenCompilerService::class),
            $mockService
        );

        $request = Request::create('/admin/portal/themes/tokens', 'POST', [
            'channel_id' => 1,
            'theme_code' => 'portal',
            'tokens' => [
                '--brand-primary' => '#0284c7',
            ],
            'custom_css' => '.header-glow { color: red; }',
        ], [], [], ['HTTP_ACCEPT' => 'application/json']);

        $response = $controller->customizeTokens($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        // Note: customizeTokens JSON response does not return tokens array
        $this->assertArrayNotHasKey('tokens', $data);
    }

    /**
     * Test resetTokens delegates to ThemeTokenService.
     */
    public function test_reset_tokens_delegates_to_theme_token_service(): void
    {
        $mockService = Mockery::mock(ThemeTokenService::class);
        $mockService->shouldReceive('resetTokens')
            ->once()
            ->with(1, 'portal')
            ->andReturn([]);

        $controller = new ThemeController(
            app(ThemeManager::class),
            app(PortalLayoutSettingRepository::class),
            app(PortalPageRepository::class),
            app(TokenCompilerService::class),
            $mockService
        );

        $request = Request::create('/admin/portal/themes/tokens/reset', 'POST', [
            'channel_id' => 1,
            'theme_code' => 'portal',
        ], [], [], ['HTTP_ACCEPT' => 'application/json']);

        $response = $controller->resetTokens($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayNotHasKey('tokens', $data);
    }
}
