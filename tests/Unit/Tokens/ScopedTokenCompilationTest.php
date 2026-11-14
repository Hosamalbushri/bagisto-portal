<?php

namespace Platform\Portal\Tests\Unit\Tokens;

use Platform\Portal\Services\TokenCompilerService;
use Tests\TestCase;

class ScopedTokenCompilationTest extends TestCase
{
    protected TokenCompilerService $compiler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->compiler = app(TokenCompilerService::class);
    }

    /**
     * Test 1: Empty sparse delta returns an empty string without emitting selectors.
     */
    public function test_empty_sparse_delta_returns_empty_string(): void
    {
        $pageCss = $this->compiler->compileScoped([], 'page', 1);
        $sectionCss = $this->compiler->compileScoped([], 'section', 10);

        $this->assertSame('', $pageCss);
        $this->assertSame('', $sectionCss);
    }

    /**
     * Test 2: Invalid scope type throws InvalidArgumentException.
     */
    public function test_invalid_scope_type_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid scope type [invalid]. Must be 'page' or 'section'.");

        $this->compiler->compileScoped(['--brand-primary' => '#10b981'], 'invalid', 1);
    }

    /**
     * Test 3: Non-positive scope ID throws InvalidArgumentException.
     */
    public function test_non_positive_scope_id_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Scope ID must be a positive integer, got [0].');

        $this->compiler->compileScoped(['--brand-primary' => '#10b981'], 'page', 0);
    }

    /**
     * Test 4: Primary-only sparse delta emits primary and derived family without platform defaults.
     */
    public function test_primary_only_sparse_delta_emits_family_without_injecting_defaults(): void
    {
        $tokens = [
            '--brand-primary' => '#10b981',
        ];

        $css = $this->compiler->compileScoped($tokens, 'page', 12);

        // 1. Selector structure
        $this->assertStringContainsString('body.portal-page-12 {', $css);
        $this->assertStringContainsString('.dark body.portal-page-12,', $css);
        $this->assertStringContainsString('[data-theme="dark"] body.portal-page-12 {', $css);

        // 2. Primary and derived family are present in light block
        $this->assertStringContainsString('--brand-primary: #10b981;', $css);
        $this->assertStringContainsString('--brand-primary-hover:', $css);
        $this->assertStringContainsString('--brand-primary-light:', $css);
        $this->assertStringContainsString('--on-brand-primary:', $css);
        $this->assertStringContainsString('--border-focus: #10b981;', $css);
        $this->assertStringContainsString('--shadow-glow:', $css);
        $this->assertStringContainsString('--brand-primary-50:', $css);
        $this->assertStringContainsString('--brand-primary-500: #10b981;', $css);
        $this->assertStringContainsString('--brand-primary-950:', $css);

        // 3. CRITICAL: Unrelated platform defaults MUST NOT be injected!
        $this->assertStringNotContainsString('--card-radius', $css);
        $this->assertStringNotContainsString('--btn-radius', $css);
        $this->assertStringNotContainsString('--bg-surface', $css);
        $this->assertStringNotContainsString('--bg-app', $css);
        $this->assertStringNotContainsString('--text-primary', $css);
        $this->assertStringNotContainsString('--header-bg', $css);
        $this->assertStringNotContainsString('--footer-bg', $css);
        $this->assertStringNotContainsString('--container-max-width', $css);
        $this->assertStringNotContainsString('--page-padding-x', $css);
        $this->assertStringNotContainsString(':root {', $css);
        $this->assertStringNotContainsString('.portal-container', $css);
    }

    /**
     * Test 5: Explicit hover value is preserved and not overwritten by local derivation.
     */
    public function test_explicit_hover_value_survives_local_derivation(): void
    {
        $tokens = [
            '--brand-primary' => '#10b981',
            '--brand-primary-hover' => '#064e3b',
        ];

        $css = $this->compiler->compileScoped($tokens, 'section', 42);

        $this->assertStringContainsString('[data-section-id="42"] {', $css);
        $this->assertStringContainsString('--brand-primary: #10b981;', $css);
        $this->assertStringContainsString('--brand-primary-hover: #064e3b;', $css);
        $this->assertStringNotContainsString('--card-radius', $css);
    }

    /**
     * Test 6: Surface-only sparse delta emits only surface declaration and no dark block.
     */
    public function test_surface_only_delta_emits_only_surface_declaration_and_no_dark_block(): void
    {
        $tokens = [
            '--bg-surface' => '#1e293b',
        ];

        $css = $this->compiler->compileScoped($tokens, 'section', 42);

        // Light block contains surface
        $this->assertStringContainsString('[data-section-id="42"] {', $css);
        $this->assertStringContainsString('--bg-surface: #1e293b;', $css);

        // NO brand derivation
        $this->assertStringNotContainsString('--brand-primary', $css);
        $this->assertStringNotContainsString('--brand-accent', $css);
        $this->assertStringNotContainsString('--radius', $css);

        // NO dark block emitted because surfaces are excluded from automatic dark derivation
        $this->assertStringNotContainsString('.dark', $css);
        $this->assertStringNotContainsString('[data-theme="dark"]', $css);
    }

    /**
     * Test 7: Accent family derivation works in scoped compilation.
     */
    public function test_accent_family_derivation_in_scoped_compilation(): void
    {
        $tokens = [
            '--brand-accent' => '#f59e0b',
        ];

        $css = $this->compiler->compileScoped($tokens, 'page', 5);

        $this->assertStringContainsString('body.portal-page-5 {', $css);
        $this->assertStringContainsString('--brand-accent: #f59e0b;', $css);
        $this->assertStringContainsString('--brand-accent-hover:', $css);
        $this->assertStringContainsString('--brand-accent-light:', $css);
        $this->assertStringContainsString('--on-brand-accent:', $css);
        $this->assertStringContainsString('--shadow-glow-accent:', $css);

        // No primary brand or radii injected
        $this->assertStringNotContainsString('--brand-primary', $css);
        $this->assertStringNotContainsString('--card-radius', $css);
    }

    /**
     * Test 8: Light primary with explicit dark override takes precedence over derived dark value.
     */
    public function test_explicit_dark_primary_override_takes_precedence_over_derived_dark(): void
    {
        $tokens = [
            '--brand-primary' => '#10b981',
            '--dark-brand-primary' => '#059669',
        ];

        $css = $this->compiler->compileScoped($tokens, 'page', 7);

        // Light block has light primary
        $this->assertStringContainsString('body.portal-page-7 {', $css);
        $this->assertStringContainsString('--brand-primary: #10b981;', $css);

        // Dark block has explicit dark override (normalized without --dark- prefix)
        $this->assertStringContainsString('.dark body.portal-page-7,', $css);
        $this->assertStringContainsString('[data-theme="dark"] body.portal-page-7 {', $css);
        $this->assertStringContainsString('--brand-primary: #059669;', $css);

        // Derived hover and glow for dark mode are also present
        $this->assertStringContainsString('--brand-primary-hover:', $css);

        // NO leakage of --dark- prefix into any declaration
        $this->assertStringNotContainsString('--dark-brand-primary', $css);
    }

    /**
     * Test 9: Explicit dark override without light override emits dark block only.
     */
    public function test_explicit_dark_override_without_light_override_emits_dark_block_only(): void
    {
        $tokens = [
            '--dark-bg-surface' => '#0f141f',
        ];

        $css = $this->compiler->compileScoped($tokens, 'section', 99);

        // Light block must NOT be emitted (CSS starts directly with dark selector)
        $this->assertStringStartsNotWith('[data-section-id="99"] {', $css);
        $this->assertStringStartsWith('.dark [data-section-id="99"],', $css);

        // Dark block MUST be emitted with normalized key
        $this->assertStringContainsString('.dark [data-section-id="99"],', $css);
        $this->assertStringContainsString('[data-theme="dark"] [data-section-id="99"] {', $css);
        $this->assertStringContainsString('--bg-surface: #0f141f;', $css);
        $this->assertStringNotContainsString('--dark-bg-surface', $css);
    }

    /**
     * Test 10: Explicit dark surface with explicit light surface emits both blocks correctly.
     */
    public function test_explicit_light_and_dark_surface_emits_both_blocks(): void
    {
        $tokens = [
            '--bg-surface' => '#f8fafc',
            '--dark-bg-surface' => '#111827',
        ];

        $css = $this->compiler->compileScoped($tokens, 'section', 15);

        // Light block
        $this->assertStringContainsString("[data-section-id=\"15\"] {\n  --bg-surface: #f8fafc;\n}", $css);

        // Dark block
        $this->assertStringContainsString(".dark [data-section-id=\"15\"],\n[data-theme=\"dark\"] [data-section-id=\"15\"] {\n  --bg-surface: #111827;\n}", $css);

        // No prefix leakage
        $this->assertStringNotContainsString('--dark-bg-surface', $css);
    }

    /**
     * Test 11: Nested dark array is also supported for explicit dark overrides.
     */
    public function test_nested_dark_array_is_supported_for_explicit_dark_overrides(): void
    {
        $tokens = [
            '--bg-surface' => '#ffffff',
            'dark' => [
                '--bg-surface' => '#090d16',
            ],
        ];

        $css = $this->compiler->compileScoped($tokens, 'page', 3);

        $this->assertStringContainsString('body.portal-page-3 {', $css);
        $this->assertStringContainsString('--bg-surface: #ffffff;', $css);

        $this->assertStringContainsString('.dark body.portal-page-3,', $css);
        $this->assertStringContainsString('[data-theme="dark"] body.portal-page-3 {', $css);
        $this->assertStringContainsString('--bg-surface: #090d16;', $css);
    }

    /**
     * Test 12: Custom CSS is excluded from scoped compilation in v1.
     */
    public function test_custom_css_is_excluded_from_scoped_compilation(): void
    {
        $tokens = [
            '--brand-primary' => '#3b82f6',
            'custom_css' => 'body { display: none !important; }',
        ];

        $css = $this->compiler->compileScoped($tokens, 'page', 1);

        $this->assertStringContainsString('--brand-primary: #3b82f6;', $css);
        $this->assertStringNotContainsString('display: none', $css);
        $this->assertStringNotContainsString('/* Additional Custom CSS */', $css);
        $this->assertStringNotContainsString('custom_css', $css);
    }

    /**
     * Test 13: Passing ONLY custom CSS results in empty string.
     */
    public function test_passing_only_custom_css_results_in_empty_string(): void
    {
        $tokens = [
            'custom_css' => '.btn { color: red; }',
        ];

        $css = $this->compiler->compileScoped($tokens, 'section', 10);

        $this->assertSame('', $css);
    }

    /**
     * Test 14: Dynamic radius base derivation in scoped compilation if provided.
     */
    public function test_radius_base_derives_radii_family_without_unrelated_defaults(): void
    {
        $tokens = [
            '--radius-base' => '8px',
        ];

        $css = $this->compiler->compileScoped($tokens, 'page', 4);

        $this->assertStringContainsString('body.portal-page-4 {', $css);
        $this->assertStringContainsString('--radius-base: 8px;', $css);
        $this->assertStringContainsString('--radius-sm: calc(8px * 0.5);', $css);
        $this->assertStringContainsString('--radius-md: 8px;', $css);
        $this->assertStringContainsString('--radius-lg: calc(8px * 1.33);', $css);
        $this->assertStringContainsString('--radius-xl: calc(8px * 1.75);', $css);
        $this->assertStringContainsString('--radius-pill: 9999px;', $css);
        $this->assertStringContainsString('--btn-radius: calc(8px * 0.75);', $css);
        $this->assertStringContainsString('--card-radius: calc(8px * 1.33);', $css);

        // Unrelated defaults must NOT be present
        $this->assertStringNotContainsString('--brand-primary', $css);
        $this->assertStringNotContainsString('--bg-surface', $css);
        $this->assertStringNotContainsString('--header-bg', $css);
    }

    /**
     * Test 15: Backward compatibility - existing Theme compile() behavior remains 100% unchanged.
     */
    public function test_existing_theme_compile_behavior_remains_unchanged(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
        ];

        $themeCss = $this->compiler->compile($tokens);

        // Theme compile MUST continue to emit :root and layout utilities
        $this->assertStringContainsString(':root {', $themeCss);
        $this->assertStringContainsString('.dark,', $themeCss);
        $this->assertStringContainsString('[data-theme="dark"] {', $themeCss);
        $this->assertStringContainsString('.portal-container', $themeCss);
        $this->assertStringContainsString('.rounded-card', $themeCss);
        $this->assertStringContainsString('.rounded-btn', $themeCss);

        // Theme compile MUST continue to inject baseline static defaults
        $this->assertStringContainsString('--btn-radius: 0.5rem;', $themeCss);
        $this->assertStringContainsString('--card-radius: 1rem;', $themeCss);
    }
}
