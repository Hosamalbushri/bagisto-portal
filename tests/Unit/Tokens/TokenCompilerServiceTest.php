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
    /**
     * Test token sanitization rejects malicious CSS injection and XSS attempts.
     */
    public function test_sanitizes_tokens_strictly(): void
    {
        $raw = [
            'invalid_no_dashes' => '#fff',
            '--invalid;key' => '#111',
            '--brand-primary' => '#1c66f6; background: red;',
            '--brand-accent' => '<script>alert(1)</script>#fe8312',
            '--bg-surface' => '   #ffffff   ',
            '--malicious-url' => 'url("https://attacker.com/leak.png")',
            '--malicious-expression' => 'expression(alert(1))',
            '--malicious-import' => '@import "evil.css"',
            '--malicious-comment' => '/* comment */ #000',
            '--malicious-escape' => '\\00002a',
        ];

        $clean = $this->compiler->sanitizeTokens($raw);

        $this->assertArrayNotHasKey('invalid_no_dashes', $clean);
        $this->assertArrayNotHasKey('--invalid;key', $clean);
        $this->assertArrayHasKey('--brand-primary', $clean);
        $this->assertStringNotContainsString(';', $clean['--brand-primary']);
        $this->assertStringNotContainsString('<script>', $clean['--brand-accent']);
        $this->assertEquals('#ffffff', $clean['--bg-surface']);

        // Assert dangerous patterns are completely rejected
        $this->assertArrayNotHasKey('--malicious-url', $clean);
        $this->assertArrayNotHasKey('--malicious-expression', $clean);
        $this->assertArrayNotHasKey('--malicious-import', $clean);
        $this->assertArrayNotHasKey('--malicious-comment', $clean);
        $this->assertArrayNotHasKey('--malicious-escape', $clean);
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
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        try {
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
        } finally {
            if (property_exists(bouncer(), 'allowsAll')) {
                bouncer()::$allowsAll = false;
            }
        }
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

    /**
     * Test Google Fonts URL generation from font tokens.
     */
    public function test_generates_google_fonts_url_from_tokens(): void
    {
        $tokens = [
            '--font-arabic' => "'Cairo', system-ui, sans-serif",
            '--font-latin' => "'Plus Jakarta Sans', system-ui, sans-serif",
        ];

        $url = $this->compiler->getGoogleFontsUrl($tokens);

        $this->assertNotNull($url);
        $this->assertStringContainsString('family=Cairo', $url);
        $this->assertStringContainsString('family=Plus+Jakarta+Sans', $url);
        $this->assertStringContainsString('display=swap', $url);
    }

    /**
     * Test compilation injects @import for Google fonts if non-system fonts are present.
     */
    public function test_compile_prepends_google_fonts_import(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--font-arabic' => "'Tajawal', sans-serif",
        ];

        $css = $this->compiler->compile($tokens);

        $this->assertStringContainsString('@import url(\'https://fonts.googleapis.com/css2?family=Tajawal', $css);
        $this->assertStringContainsString(':root {', $css);
    }

    /**
     * Test WCAG luminance and contrast ratio calculations.
     */
    public function test_wcag_luminance_and_contrast_ratio_calculations(): void
    {
        // Pure black and pure white
        $ratioBlackWhite = $this->compiler->calculateContrastRatio('#000000', '#ffffff');
        $this->assertEquals(21.0, round($ratioBlackWhite, 1));

        // #1c66f6 (primary blue) vs #ffffff
        $ratioBlueWhite = $this->compiler->calculateContrastRatio('#1c66f6', '#ffffff');
        $this->assertGreaterThan(4.5, $ratioBlueWhite);

        // Identical colors should have contrast ratio of 1.0
        $ratioSame = $this->compiler->calculateContrastRatio('#fe8312', '#fe8312');
        $this->assertEquals(1.0, round($ratioSame, 1));
    }

    /**
     * Test derivation of component tokens (header, footer, button radius, card radius).
     */
    public function test_derives_component_tokens(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--radius-base' => '8px',
        ];

        $derived = $this->compiler->deriveTokens($tokens);

        $this->assertArrayHasKey('--btn-radius', $derived);
        $this->assertArrayHasKey('--card-radius', $derived);
        $this->assertArrayHasKey('--header-bg', $derived);
        $this->assertArrayHasKey('--header-text', $derived);
        $this->assertArrayHasKey('--footer-bg', $derived);
        $this->assertArrayHasKey('--footer-text', $derived);

        $this->assertEquals('calc(8px * 0.75)', $derived['--btn-radius']);
        $this->assertEquals('calc(8px * 1.33)', $derived['--card-radius']);
    }

    /**
     * Test Custom CSS sanitization protects against XSS while allowing valid styles.
     */
    public function test_sanitizes_custom_css_safely(): void
    {
        $malicious = "</style><script>alert('xss')</script>\n.btn { color: red; background: url('javascript:alert(1)'); }\n@media (min-width: 1024px) { .container { width: 100%; } }";

        $clean = $this->compiler->sanitizeCustomCss($malicious);

        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringNotContainsString('</script>', $clean);
        $this->assertStringNotContainsString('</style>', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('.btn { color: red;', $clean);
        $this->assertStringContainsString('@media (min-width: 1024px)', $clean);
    }

    /**
     * Test compilation includes layout tokens, container utility, and custom CSS.
     */
    public function test_compiles_custom_css_and_layout_tokens(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--container-max-width' => '1440px',
            '--page-padding-x' => '1.5rem',
            'custom_css' => '.custom-badge { border-radius: 9999px; }',
        ];

        $css = $this->compiler->compile($tokens);

        $this->assertStringContainsString('--container-max-width: 1440px;', $css);
        $this->assertStringContainsString('--page-padding-x: 1.5rem;', $css);
        $this->assertStringContainsString('.portal-container {', $css);
        $this->assertStringContainsString('max-width: var(--container-max-width, 1280px);', $css);
        $this->assertStringContainsString('/* Additional Custom CSS */', $css);
        $this->assertStringContainsString('.custom-badge { border-radius: 9999px; }', $css);
    }

    /**
     * Test ThemeController saves custom_css alongside tokens.
     */
    public function test_theme_controller_saves_custom_css(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        try {
            $controller = app(ThemeController::class);

            $request = Request::create('/admin/portal/themes/tokens', 'POST', [
                'channel_id' => 1,
                'theme_code' => 'portal',
                'tokens' => [
                    '--brand-primary' => '#0284c7',
                    '--container-max-width' => '1600px',
                ],
                'custom_css' => '.header-glow { filter: drop-shadow(0 0 10px blue); }',
            ]);

            $response = $controller->customizeTokens($request);
            $this->assertEquals(302, $response->getStatusCode());

            $saved = app(PortalLayoutSettingRepository::class)->getSettings('theme_tokens', 1, 'portal');
            $this->assertEquals('#0284c7', $saved['--brand-primary']);
            $this->assertEquals('1600px', $saved['--container-max-width']);
            $this->assertEquals('.header-glow { filter: drop-shadow(0 0 10px blue); }', $saved['custom_css']);
        } finally {
            if (property_exists(bouncer(), 'allowsAll')) {
                bouncer()::$allowsAll = false;
            }
        }
    }

    /**
     * Test DTCG export and import with custom_css and layout dimensions.
     */
    public function test_dtcg_exports_and_imports_custom_css_and_layout_dimensions(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--container-max-width' => '1440px',
            'custom_css' => '.hero { min-height: 90vh; }',
        ];

        $dtcg = $this->compiler->exportDtcg($tokens);
        $this->assertEquals('.hero { min-height: 90vh; }', $dtcg['custom_css']);
        $this->assertEquals('1440px', $dtcg['dimension']['container-max-width']['$value']);

        $imported = $this->compiler->importDtcg($dtcg);
        $this->assertEquals('#1c66f6', $imported['--brand-primary']);
        $this->assertEquals('1440px', $imported['--container-max-width']);
        $this->assertEquals('.hero { min-height: 90vh; }', $imported['custom_css']);
    }

    /**
     * Test compilation includes header, footer, card, and button universal token bindings.
     */
    public function test_compiles_header_footer_card_and_button_bindings(): void
    {
        $tokens = [
            '--header-bg' => '#ffffff',
            '--header-text' => '#111827',
            '--footer-bg' => '#0f172a',
            '--footer-text' => '#94a3b8',
            '--card-radius' => '1.25rem',
            '--btn-radius' => '0.75rem',
        ];

        $css = $this->compiler->compile($tokens);

        $this->assertStringContainsString('.portal-header, header[data-portal-header]', $css);
        $this->assertStringContainsString('background-color: var(--header-bg, var(--bg-surface)) !important;', $css);
        $this->assertStringContainsString('color: var(--header-text, var(--text-primary)) !important;', $css);

        $this->assertStringContainsString('.portal-footer, footer[data-portal-footer]', $css);
        $this->assertStringContainsString('background-color: var(--footer-bg, var(--bg-surface-elevated)) !important;', $css);
        $this->assertStringContainsString('color: var(--footer-text, var(--text-secondary)) !important;', $css);

        $this->assertStringContainsString('.portal-card, [data-portal-card]', $css);
        $this->assertStringContainsString('border-radius: var(--card-radius, 1rem) !important;', $css);

        $this->assertStringContainsString('.btn-primary, .primary-button', $css);
        $this->assertStringContainsString('border-radius: var(--btn-radius, 0.5rem) !important;', $css);

        $this->assertStringContainsString('input[type="text"], input[type="email"]', $css);
    }
}
