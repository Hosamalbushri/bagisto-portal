<?php

namespace Platform\Portal\Tests\Unit\Tokens;

use Platform\Portal\Services\Tokens\TokenDeriver;
use Tests\TestCase;

class TokenDeriverTest extends TestCase
{
    protected TokenDeriver $deriver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->deriver = app(TokenDeriver::class);
    }

    /**
     * Test derivation of brand primary and accent contextual variations.
     */
    public function test_derives_brand_contextual_variations(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--brand-accent' => '#fe8312',
        ];

        $derived = $this->deriver->deriveTokens($tokens);

        // Hover states
        $this->assertArrayHasKey('--brand-primary-hover', $derived);
        $this->assertArrayHasKey('--brand-accent-hover', $derived);
        $this->assertNotEquals('#1c66f6', $derived['--brand-primary-hover']);
        $this->assertNotEquals('#fe8312', $derived['--brand-accent-hover']);

        // Light tints (RGBA)
        $this->assertArrayHasKey('--brand-primary-light', $derived);
        $this->assertArrayHasKey('--brand-accent-light', $derived);
        $this->assertStringStartsWith('rgba(', $derived['--brand-primary-light']);
        $this->assertStringStartsWith('rgba(', $derived['--brand-accent-light']);

        // Contrast text
        $this->assertArrayHasKey('--on-brand-primary', $derived);
        $this->assertArrayHasKey('--on-brand-accent', $derived);
        $this->assertEquals('#ffffff', $derived['--on-brand-primary']);

        // Glow shadows & focus border
        $this->assertArrayHasKey('--border-focus', $derived);
        $this->assertEquals('#1c66f6', $derived['--border-focus']);
        $this->assertArrayHasKey('--shadow-glow', $derived);
        $this->assertArrayHasKey('--shadow-glow-accent', $derived);

        // Tonal ramp tokens
        $this->assertArrayHasKey('--brand-primary-50', $derived);
        $this->assertArrayHasKey('--brand-primary-100', $derived);
        $this->assertArrayHasKey('--brand-primary-500', $derived);
        $this->assertEquals('#1c66f6', $derived['--brand-primary-500']);
        $this->assertArrayHasKey('--brand-primary-950', $derived);
    }

    /**
     * Test derivation of dynamic radii when radius-base is provided.
     */
    public function test_derives_dynamic_radii(): void
    {
        $tokens = [
            '--radius-base' => '1rem',
        ];

        $derived = $this->deriver->deriveTokens($tokens);

        $this->assertEquals('calc(1rem * 0.5)', $derived['--radius-sm']);
        $this->assertEquals('1rem', $derived['--radius-md']);
        $this->assertEquals('calc(1rem * 1.33)', $derived['--radius-lg']);
        $this->assertEquals('calc(1rem * 1.75)', $derived['--radius-xl']);
        $this->assertEquals('9999px', $derived['--radius-pill']);
        $this->assertEquals('calc(1rem * 0.75)', $derived['--btn-radius']);
        $this->assertEquals('calc(1rem * 1.33)', $derived['--card-radius']);
    }

    /**
     * Test fallback defaults when tokens are empty.
     */
    public function test_derives_default_components_and_dimensions(): void
    {
        $derived = $this->deriver->deriveTokens([]);

        $this->assertEquals('0.5rem', $derived['--btn-radius']);
        $this->assertEquals('1rem', $derived['--card-radius']);
        $this->assertEquals('#ffffff', $derived['--header-bg']);
        $this->assertEquals('#090d16', $derived['--header-text']);
        $this->assertEquals('#0f172a', $derived['--footer-bg']);
        $this->assertEquals('#94a3b8', $derived['--footer-text']);
        $this->assertEquals('1280px', $derived['--container-max-width']);
        $this->assertEquals('1rem', $derived['--page-padding-x']);
        $this->assertArrayHasKey('--shadow-card', $derived);
    }

    /**
     * Test tonal palette generator creates 11 distinct shades.
     */
    public function test_generates_full_tonal_palette(): void
    {
        $palette = $this->deriver->generateTonalPalette('#059669');

        $this->assertCount(11, $palette);
        $this->assertEquals('#059669', $palette[500]);
        $this->assertArrayHasKey(50, $palette);
        $this->assertArrayHasKey(100, $palette);
        $this->assertArrayHasKey(200, $palette);
        $this->assertArrayHasKey(300, $palette);
        $this->assertArrayHasKey(400, $palette);
        $this->assertArrayHasKey(600, $palette);
        $this->assertArrayHasKey(700, $palette);
        $this->assertArrayHasKey(800, $palette);
        $this->assertArrayHasKey(900, $palette);
        $this->assertArrayHasKey(950, $palette);
    }

    /**
     * Test adjustBrightness with 3-digit and 6-digit hex codes.
     */
    public function test_adjust_brightness(): void
    {
        // Darken
        $darker = $this->deriver->adjustBrightness('#ffffff', -0.5);
        $this->assertNotEquals('#ffffff', $darker);

        // Lighten
        $lighter = $this->deriver->adjustBrightness('#000000', 0.5);
        $this->assertNotEquals('#000000', $lighter);

        // 3-digit hex expansion
        $expanded = $this->deriver->adjustBrightness('#fff', -0.2);
        $this->assertEquals(7, strlen($expanded));
    }

    /**
     * Test hexToRgba conversion.
     */
    public function test_hex_to_rgba(): void
    {
        $rgba = $this->deriver->hexToRgba('#1c66f6', 0.5);
        $this->assertStringStartsWith('rgba(', $rgba);
        $this->assertStringContainsString('0.50', $rgba);

        // 3-digit hex
        $rgba3 = $this->deriver->hexToRgba('#fff', 0.8);
        $this->assertEquals('rgba(255, 255, 255, 0.80)', $rgba3);
    }

    /**
     * Test WCAG luminance, contrast ratio, and contrast text selection.
     */
    public function test_wcag_calculations(): void
    {
        // Black and white
        $contrast = $this->deriver->calculateContrastRatio('#000000', '#ffffff');
        $this->assertEquals(21.0, round($contrast, 1));

        // Same color
        $same = $this->deriver->calculateContrastRatio('#1c66f6', '#1c66f6');
        $this->assertEquals(1.0, round($same, 1));

        // Contrast text for white (bright background -> dark text)
        $this->assertEquals('#090d16', $this->deriver->getContrastTextColor('#ffffff'));

        // Contrast text for dark navy (dark background -> white text)
        $this->assertEquals('#ffffff', $this->deriver->getContrastTextColor('#0f172a'));
    }

    /**
     * Test derivation of dark mode brand tokens.
     */
    public function test_derives_dark_tokens_for_brands(): void
    {
        $rootTokens = [
            '--brand-primary' => '#1c66f6',
            '--brand-accent' => '#fe8312',
        ];

        $dark = $this->deriver->deriveDarkTokens($rootTokens);

        $this->assertArrayHasKey('--brand-primary', $dark);
        $this->assertEquals('#1c66f6', $dark['--brand-primary']);
        $this->assertArrayHasKey('--brand-primary-hover', $dark);
        $this->assertArrayHasKey('--brand-primary-light', $dark);
        $this->assertArrayHasKey('--on-brand-primary', $dark);
        $this->assertArrayHasKey('--shadow-glow', $dark);
        $this->assertArrayHasKey('--border-focus', $dark);

        $this->assertArrayHasKey('--brand-accent', $dark);
        $this->assertEquals('#fe8312', $dark['--brand-accent']);
        $this->assertArrayHasKey('--brand-accent-hover', $dark);
        $this->assertArrayHasKey('--brand-accent-light', $dark);
        $this->assertArrayHasKey('--on-brand-accent', $dark);
        $this->assertArrayHasKey('--shadow-glow-accent', $dark);
    }

    /**
     * Test pure derive() returns pure mathematical derivations without injecting defaults.
     */
    public function test_pure_derive_does_not_inject_defaults(): void
    {
        $derived = $this->deriver->derive([]);

        $this->assertSame([], $derived);
    }

    /**
     * Test deriver preserves explicit tonal ramp values (does not overwrite user shade).
     */
    public function test_preserves_explicit_tonal_ramp_values(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--brand-primary-500' => '#047857',
        ];

        $derived = $this->deriver->derive($tokens);

        $this->assertSame('#047857', $derived['--brand-primary-500']);
        $this->assertArrayHasKey('--brand-primary-50', $derived);
        $this->assertArrayHasKey('--brand-primary-950', $derived);
    }

    /**
     * Test deriver preserves explicit radius sub-tokens when radius-base is provided.
     */
    public function test_preserves_explicit_radius_sub_tokens(): void
    {
        $tokens = [
            '--radius-base' => '1rem',
            '--radius-sm' => '4px',
            '--btn-radius' => '0.25rem',
        ];

        $derived = $this->deriver->derive($tokens);

        $this->assertSame('4px', $derived['--radius-sm']);
        $this->assertSame('0.25rem', $derived['--btn-radius']);
        $this->assertSame('calc(1rem * 1.33)', $derived['--radius-lg']);
        $this->assertSame('calc(1rem * 1.33)', $derived['--card-radius']);
    }

    /**
     * Test direct construction via new TokenDeriver() works and provides full backward compatibility.
     */
    public function test_direct_construction_with_new_deriver_works(): void
    {
        $directDeriver = new TokenDeriver;
        $legacyResult = $directDeriver->deriveTokens([]);

        $this->assertCount(9, $legacyResult);
        $this->assertSame('0.5rem', $legacyResult['--btn-radius']);
        $this->assertSame('1280px', $legacyResult['--container-max-width']);
    }
}
