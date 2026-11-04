<?php

namespace Platform\Portal\Tests\Unit\Tokens;

use Illuminate\Http\Request;
use Platform\Portal\Http\Controllers\Admin\ThemeController;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Services\ThemeManager;
use Platform\Portal\Services\TokenCompilerService;
use Tests\TestCase;

class TokenCompilerServiceTest extends TestCase
{
    protected TokenCompilerService $compiler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->compiler = app(TokenCompilerService::class);
    }

    /**
     * Test compilation of tokens into :root and .dark rules.
     */
    public function test_compiles_tokens_into_root_and_dark_css(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--brand-accent' => '#fe8312',
            '--bg-app' => '#f8fafc',
            '--dark-bg-app' => '#080b11',
            '--radius-base' => '12px',
        ];

        $css = $this->compiler->compile($tokens);

        $this->assertStringContainsString(':root {', $css);
        $this->assertStringContainsString('--brand-primary: #1c66f6;', $css);
        $this->assertStringContainsString('--brand-accent: #fe8312;', $css);
        $this->assertStringContainsString('--bg-app: #f8fafc;', $css);
        $this->assertStringContainsString('--radius-base: 12px;', $css);
        $this->assertStringContainsString('--radius-sm: calc(12px * 0.5);', $css);
        $this->assertStringContainsString('.dark,', $css);
        $this->assertStringContainsString('[data-theme="dark"] {', $css);
        $this->assertStringContainsString('--bg-app: #080b11;', $css);
    }

    /**
     * Test tonal palette generator creates 10 distinct shades from 50 to 950.
     */
    public function test_generates_full_tonal_palette(): void
    {
        $palette = $this->compiler->generateTonalPalette('#1c66f6');

        $this->assertIsArray($palette);
        $this->assertCount(11, $palette);
        $this->assertArrayHasKey(50, $palette);
        $this->assertArrayHasKey(500, $palette);
        $this->assertArrayHasKey(950, $palette);
        $this->assertEquals('#1c66f6', $palette[500]);
        $this->assertNotEquals('#1c66f6', $palette[50]);
        $this->assertNotEquals('#1c66f6', $palette[950]);
    }

    /**
     * Test mathematical derivation of hover, light tints, glow shadows, and WCAG contrast text.
     */
    public function test_derives_hover_light_and_contrast_tokens(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--brand-accent' => '#ffffff', // Very bright accent -> should yield dark text
        ];

        $derived = $this->compiler->deriveTokens($tokens);

        // Hover should be derived
        $this->assertArrayHasKey('--brand-primary-hover', $derived);
        $this->assertNotEquals('#1c66f6', $derived['--brand-primary-hover']);

        // Light tint should be derived
        $this->assertArrayHasKey('--brand-primary-light', $derived);
        $this->assertStringStartsWith('rgba(', $derived['--brand-primary-light']);

        // Glow shadow should be derived
        $this->assertArrayHasKey('--shadow-glow', $derived);
        $this->assertStringContainsString('0 0 25px', $derived['--shadow-glow']);

        // Contrast text for #1c66f6 (darker blue) should be white
        $this->assertArrayHasKey('--on-brand-primary', $derived);
        $this->assertEquals('#ffffff', $derived['--on-brand-primary']);

        // Contrast text for #ffffff (pure white) should be dark #090d16
        $this->assertArrayHasKey('--on-brand-accent', $derived);
        $this->assertEquals('#090d16', $derived['--on-brand-accent']);

        // Tonal ramp tokens should be present
        $this->assertArrayHasKey('--brand-primary-50', $derived);
        $this->assertArrayHasKey('--brand-primary-500', $derived);
        $this->assertArrayHasKey('--brand-primary-900', $derived);
    }

    /**
     * Test sanitization strips harmful CSS and invalid keys.
     */
    public function test_sanitizes_tokens_strictly(): void
    {
        $raw = [
            'invalid_no_dashes' => '#fff',
            '--brand-primary' => '#1c66f6; background: red;',
            '--brand-accent' => '<script>alert(1)</script>#fe8312',
            '--bg-surface' => '   #ffffff   ',
        ];

        $clean = $this->compiler->sanitizeTokens($raw);

        $this->assertArrayNotHasKey('invalid_no_dashes', $clean);
        $this->assertArrayHasKey('--brand-primary', $clean);
        $this->assertStringNotContainsString(';', $clean['--brand-primary']);
        $this->assertStringNotContainsString('<script>', $clean['--brand-accent']);
        $this->assertEquals('#ffffff', $clean['--bg-surface']);
    }

    /**
     * Test retrieval of presets and specific preset.
     */
    public function test_retrieves_curated_presets(): void
    {
        $presets = $this->compiler->getPresets();

        $this->assertIsArray($presets);
        $this->assertArrayHasKey('default', $presets);
        $this->assertArrayHasKey('emerald_tech', $presets);
        $this->assertArrayHasKey('minimal_slate', $presets);
        $this->assertArrayHasKey('cyber_violet', $presets);
        $this->assertArrayHasKey('royal_gold', $presets);
        $this->assertArrayHasKey('crimson_passion', $presets);
        $this->assertArrayHasKey('nordic_ocean', $presets);

        $emerald = $this->compiler->getPreset('emerald_tech');
        $this->assertNotNull($emerald);
        $this->assertEquals('#059669', $emerald['tokens']['--brand-primary']);
    }

    /**
     * Test bidirectional W3C DTCG export and import format.
     */
    public function test_exports_and_imports_w3c_dtcg_format(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--brand-accent' => '#fe8312',
            '--radius-base' => '16px',
            '--font-arabic' => "'Cairo', sans-serif",
        ];

        // 1. Export
        $dtcg = $this->compiler->exportDtcg($tokens);

        $this->assertEquals('Bagisto Portal Design Tokens', $dtcg['$name']);
        $this->assertEquals('2.0.0', $dtcg['$version']);
        $this->assertArrayHasKey('color', $dtcg);
        $this->assertEquals('#1c66f6', $dtcg['color']['brand-primary']['$value']);
        $this->assertEquals('16px', $dtcg['dimension']['radius-base']['$value']);
        $this->assertEquals("'Cairo', sans-serif", $dtcg['typography']['font-arabic']['$value']);

        // 2. Import back
        $imported = $this->compiler->importDtcg($dtcg);
        $this->assertEquals('#1c66f6', $imported['--brand-primary']);
        $this->assertEquals('16px', $imported['--radius-base']);
        $this->assertEquals("'Cairo', sans-serif", $imported['--font-arabic']);
    }

    /**
     * Test ThemeController preset, export, and import endpoints.
     */
    public function test_theme_controller_preset_and_export_endpoints(): void
    {
        $controller = app(ThemeController::class);

        // 1. Get Presets
        $presetsResponse = $controller->getPresets();
        $this->assertEquals(200, $presetsResponse->getStatusCode());
        $presetsData = json_decode($presetsResponse->getContent(), true);
        $this->assertArrayHasKey('presets', $presetsData);
        $this->assertArrayHasKey('emerald_tech', $presetsData['presets']);

        // 2. Apply Preset
        $applyRequest = Request::create('/admin/portal/themes/tokens/apply-preset', 'POST', [
            'channel_id' => 1,
            'theme_code' => 'portal',
            'preset' => 'emerald_tech',
        ]);
        $applyResponse = $controller->applyPreset($applyRequest);
        $this->assertEquals(200, $applyResponse->getStatusCode());
        $applyData = json_decode($applyResponse->getContent(), true);
        $this->assertEquals('#059669', $applyData['tokens']['--brand-primary']);

        // Verify stored in repository
        $stored = app(PortalLayoutSettingRepository::class)->getSettings('theme_tokens', 1, 'portal');
        $this->assertEquals('#059669', $stored['--brand-primary']);

        // 3. Export DTCG JSON
        $exportRequest = Request::create('/admin/portal/themes/tokens/export', 'GET', [
            'channel_id' => 1,
            'theme_code' => 'portal',
        ]);
        $exportResponse = $controller->exportTokens($exportRequest);
        $this->assertEquals(200, $exportResponse->getStatusCode());
        $exportData = json_decode($exportResponse->getContent(), true);
        $this->assertEquals('#059669', $exportData['color']['brand-primary']['$value']);

        // 4. Import JSON payload
        $importPayload = json_encode([
            'color' => [
                'brand-primary' => ['$value' => '#0284c7'],
            ],
            'dimension' => [
                'radius-base' => ['$value' => '20px'],
            ],
        ]);
        $importRequest = Request::create('/admin/portal/themes/tokens/import', 'POST', [
            'channel_id' => 1,
            'theme_code' => 'portal',
            'json' => $importPayload,
        ]);
        $importResponse = $controller->importTokens($importRequest);
        $this->assertEquals(200, $importResponse->getStatusCode());
        $importData = json_decode($importResponse->getContent(), true);
        $this->assertEquals('#0284c7', $importData['tokens']['--brand-primary']);
        $this->assertEquals('20px', $importData['tokens']['--radius-base']);
    }

    /**
     * Test ThemeManager integrates TokenCompilerService in resolveThemeCss.
     */
    public function test_theme_manager_uses_token_compiler(): void
    {
        app(PortalLayoutSettingRepository::class)->saveSettings('theme_tokens', [
            '--brand-primary' => '#10b981',
        ], 1, 'portal');

        $css = app(ThemeManager::class)->resolveThemeCss('portal', 1);

        $this->assertNotNull($css);
        $this->assertStringContainsString(':root {', $css);
        $this->assertStringContainsString('--brand-primary: #10b981;', $css);
        $this->assertStringContainsString('--brand-primary-hover:', $css);
        $this->assertStringContainsString('--on-brand-primary:', $css);
        $this->assertStringContainsString('--brand-primary-50:', $css);
    }
}
