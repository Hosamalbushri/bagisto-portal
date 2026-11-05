<?php

namespace Platform\Portal\Tests\Unit\Tokens;

use Platform\Portal\Services\TokenCompilerService;
use Platform\Portal\Services\Tokens\TokenCompiler;
use Platform\Portal\Services\Tokens\TokenDeriver;
use Platform\Portal\Services\Tokens\TokenSanitizer;
use ReflectionClass;
use Tests\TestCase;

class TokenCompilerTest extends TestCase
{
    protected TokenCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->compiler = new TokenCompiler;
    }

    /**
     * Test 1: TokenCompiler has no TokenSanitizer dependency.
     */
    public function test_token_compiler_has_no_token_sanitizer_dependency(): void
    {
        $reflector = new ReflectionClass(TokenCompiler::class);

        $this->assertFalse(
            $reflector->hasProperty('sanitizer'),
            'TokenCompiler should not maintain a property or dependency on TokenSanitizer.'
        );

        $constructor = $reflector->getConstructor();
        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();
                $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : (string) $type;
                $this->assertNotEquals(TokenSanitizer::class, $typeName, 'TokenCompiler constructor should not require TokenSanitizer.');
            }
        }
    }

    /**
     * Test 2: TokenCompiler has no TokenDeriver dependency.
     */
    public function test_token_compiler_has_no_token_deriver_dependency(): void
    {
        $reflector = new ReflectionClass(TokenCompiler::class);

        $this->assertFalse(
            $reflector->hasProperty('deriver'),
            'TokenCompiler should not maintain a property or dependency on TokenDeriver.'
        );

        $constructor = $reflector->getConstructor();
        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();
                $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : (string) $type;
                $this->assertNotEquals(TokenDeriver::class, $typeName, 'TokenCompiler constructor should not require TokenDeriver.');
            }
        }
    }

    /**
     * Test 3: TokenCompiler compiles prepared tokens into root, dark, and layout CSS.
     */
    public function test_token_compiler_compiles_prepared_tokens(): void
    {
        $rootTokens = [
            '--brand-primary' => '#1c66f6',
            '--radius-base' => '12px',
        ];
        $darkTokens = [
            '--brand-primary' => '#3e7df7',
            '--bg-app' => '#080b11',
        ];
        $customCss = '.my-badge { opacity: 0.9; }';

        $css = $this->compiler->compile($rootTokens, $darkTokens, $customCss);

        // Verify :root block contains root tokens
        $this->assertStringContainsString(':root {', $css);
        $this->assertStringContainsString('--brand-primary: #1c66f6;', $css);
        $this->assertStringContainsString('--radius-base: 12px;', $css);

        // Verify .dark block contains dark tokens
        $this->assertStringContainsString(".dark,\n[data-theme=\"dark\"] {", $css);
        $this->assertStringContainsString('--brand-primary: #3e7df7;', $css);
        $this->assertStringContainsString('--bg-app: #080b11;', $css);

        // Verify layout utilities and custom CSS
        $this->assertStringContainsString('.portal-container {', $css);
        $this->assertStringContainsString('/* Additional Custom CSS */', $css);
        $this->assertStringContainsString('.my-badge { opacity: 0.9; }', $css);
    }

    /**
     * Test 4: TokenCompilerService orchestrates sanitization, derivation, and compilation.
     */
    public function test_token_compiler_service_orchestrates_sanitization_and_derivation(): void
    {
        $service = app(TokenCompilerService::class);

        $rawTokens = [
            '--brand-primary' => '#059669',
            '--dark-bg-app' => '#080b11',
            '--dirty;key' => 'blocked',
            'custom_css' => '<script>alert(1)</script>.safe { margin: 0; }',
        ];

        $css = $service->compile($rawTokens);

        // Sanitization happened
        $this->assertStringNotContainsString('--dirty', $css);
        $this->assertStringNotContainsString('<script>', $css);
        $this->assertStringContainsString('.safe { margin: 0; }', $css);

        // Derivation happened: tonal ramp and dark brand colors generated
        $this->assertStringContainsString('--brand-primary: #059669;', $css);
        $this->assertStringContainsString('--brand-primary-500: #059669;', $css);
        $this->assertStringContainsString('--brand-primary-hover:', $css);
        $this->assertStringContainsString('--bg-app: #080b11;', $css);
    }

    /**
     * Test 5: Existing public APIs of TokenCompilerService remain compatible.
     */
    public function test_existing_public_apis_remain_compatible(): void
    {
        $service = new TokenCompilerService;

        $this->assertTrue(method_exists($service, 'compile'));
        $this->assertTrue(method_exists($service, 'deriveTokens'));
        $this->assertTrue(method_exists($service, 'generateTonalPalette'));
        $this->assertTrue(method_exists($service, 'sanitizeTokens'));
        $this->assertTrue(method_exists($service, 'sanitizeCustomCss'));
        $this->assertTrue(method_exists($service, 'getPresets'));
        $this->assertTrue(method_exists($service, 'getPreset'));
        $this->assertTrue(method_exists($service, 'exportDtcg'));
        $this->assertTrue(method_exists($service, 'importDtcg'));
        $this->assertTrue(method_exists($service, 'adjustBrightness'));
        $this->assertTrue(method_exists($service, 'hexToRgba'));
        $this->assertTrue(method_exists($service, 'getGoogleFontsUrl'));
        $this->assertTrue(method_exists($service, 'calculateLuminance'));
        $this->assertTrue(method_exists($service, 'calculateContrastRatio'));
        $this->assertTrue(method_exists($service, 'getContrastTextColor'));

        // Verify method calls return expected types
        $this->assertIsString($service->adjustBrightness('#1c66f6', 0.1));
        $this->assertIsString($service->hexToRgba('#1c66f6', 0.5));
        $this->assertIsFloat($service->calculateLuminance('#ffffff'));
        $this->assertIsFloat($service->calculateContrastRatio('#000000', '#ffffff'));
        $this->assertIsString($service->getContrastTextColor('#ffffff'));
        $this->assertIsArray($service->deriveTokens(['--brand-primary' => '#1c66f6']));
        $this->assertIsArray($service->sanitizeTokens(['--valid' => 'value']));
    }

    /**
     * Test 6: Direct construction works without container.
     */
    public function test_direct_construction_works_without_container(): void
    {
        $directCompiler = new TokenCompiler;
        $this->assertInstanceOf(TokenCompiler::class, $directCompiler);

        $directServiceNoArgs = new TokenCompilerService;
        $this->assertInstanceOf(TokenCompilerService::class, $directServiceNoArgs);

        $directServiceWithArgs = new TokenCompilerService(
            new TokenCompiler,
            new TokenSanitizer,
            new TokenDeriver
        );
        $this->assertInstanceOf(TokenCompilerService::class, $directServiceWithArgs);

        $css = $directServiceWithArgs->compile(['--brand-primary' => '#10b981']);
        $this->assertStringContainsString('--brand-primary: #10b981;', $css);
    }

    /**
     * Test declaration ordering: fonts import first, then :root, then .dark, then component utilities, then custom CSS.
     */
    public function test_declaration_ordering(): void
    {
        $rootTokens = [
            '--brand-primary' => '#1c66f6',
            '--font-arabic' => "'Cairo', sans-serif",
        ];
        $darkTokens = [
            '--brand-primary' => '#3e7df7',
        ];
        $customCss = '.my-special-rule { opacity: 0.8; }';

        $css = $this->compiler->compile($rootTokens, $darkTokens, $customCss);

        $fontPos = strpos($css, "@import url('https://fonts.googleapis.com");
        $rootPos = strpos($css, ':root {');
        $darkPos = strpos($css, '.dark,');
        $utilPos = strpos($css, '.portal-header');
        $customPos = strpos($css, '.my-special-rule { opacity: 0.8; }');

        $this->assertNotFalse($fontPos);
        $this->assertNotFalse($rootPos);
        $this->assertNotFalse($darkPos);
        $this->assertNotFalse($utilPos);
        $this->assertNotFalse($customPos);

        $this->assertLessThan($rootPos, $fontPos);
        $this->assertLessThan($darkPos, $rootPos);
        $this->assertLessThan($utilPos, $darkPos);
        $this->assertLessThan($customPos, $utilPos);
    }

    /**
     * Test component bindings CSS preservation with !important.
     */
    public function test_preserves_component_bindings_css(): void
    {
        $css = $this->compiler->compile(['--brand-primary' => '#1c66f6']);

        $this->assertStringContainsString('.portal-header, header[data-portal-header]', $css);
        $this->assertStringContainsString('background-color: var(--header-bg, var(--bg-surface)) !important;', $css);
        $this->assertStringContainsString('.portal-footer, footer[data-portal-footer]', $css);
        $this->assertStringContainsString('.btn-primary, .primary-button', $css);
        $this->assertStringContainsString('background-color: var(--brand-primary) !important;', $css);
        $this->assertStringContainsString('.portal-card, [data-portal-card]', $css);
    }

    /**
     * Test empty token input returns empty string when all inputs are empty.
     */
    public function test_returns_empty_when_all_prepared_inputs_are_empty(): void
    {
        $this->assertSame('', $this->compiler->compile([], [], null));
        $this->assertSame('', $this->compiler->compile([], [], ''));
    }
}
