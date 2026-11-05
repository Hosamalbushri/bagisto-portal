<?php

namespace Platform\Portal\Tests\Unit\Tokens;

use Platform\Portal\Services\Tokens\TokenDefaultResolver;
use stdClass;
use Tests\TestCase;

class TokenDefaultResolverTest extends TestCase
{
    protected TokenDefaultResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new TokenDefaultResolver;
    }

    /**
     * Test resolving all 9 baseline defaults on empty input.
     */
    public function test_resolves_all_defaults_on_empty_input(): void
    {
        $resolved = $this->resolver->resolveDefaults([]);

        $this->assertCount(9, $resolved);
        $this->assertSame('0.5rem', $resolved['--btn-radius']);
        $this->assertSame('1rem', $resolved['--card-radius']);
        $this->assertSame('0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.03)', $resolved['--shadow-card']);
        $this->assertSame('#ffffff', $resolved['--header-bg']);
        $this->assertSame('#090d16', $resolved['--header-text']);
        $this->assertSame('#0f172a', $resolved['--footer-bg']);
        $this->assertSame('#94a3b8', $resolved['--footer-text']);
        $this->assertSame('1280px', $resolved['--container-max-width']);
        $this->assertSame('1rem', $resolved['--page-padding-x']);
    }

    /**
     * Test preserving zero values (0, '0', '0px') without replacing them with defaults.
     */
    public function test_preserves_numeric_and_string_zero(): void
    {
        $input = [
            '--btn-radius' => 0,
            '--card-radius' => '0',
            '--page-padding-x' => '0px',
        ];

        $resolved = $this->resolver->resolveDefaults($input);

        $this->assertSame(0, $resolved['--btn-radius']);
        $this->assertSame('0', $resolved['--card-radius']);
        $this->assertSame('0px', $resolved['--page-padding-x']);
    }

    /**
     * Test null, empty strings, and whitespace fall back to default values.
     */
    public function test_null_empty_string_and_whitespace_trigger_defaults(): void
    {
        $input = [
            '--container-max-width' => null,
            '--card-radius' => '',
            '--btn-radius' => '   ',
        ];

        $resolved = $this->resolver->resolveDefaults($input);

        $this->assertSame('1280px', $resolved['--container-max-width']);
        $this->assertSame('1rem', $resolved['--card-radius']);
        $this->assertSame('0.5rem', $resolved['--btn-radius']);
    }

    /**
     * Test type-safe check rejects non-scalars (arrays, objects, booleans) without throwing errors.
     */
    public function test_type_safe_has_non_empty_value_on_non_scalars(): void
    {
        $input = [
            '--array' => ['invalid'],
            '--object' => new stdClass,
            '--bool-true' => true,
            '--bool-false' => false,
            '--valid' => '12px',
        ];

        $this->assertFalse(TokenDefaultResolver::hasNonEmptyValue($input, '--array'));
        $this->assertFalse(TokenDefaultResolver::hasNonEmptyValue($input, '--object'));
        $this->assertFalse(TokenDefaultResolver::hasNonEmptyValue($input, '--bool-true'));
        $this->assertFalse(TokenDefaultResolver::hasNonEmptyValue($input, '--bool-false'));
        $this->assertTrue(TokenDefaultResolver::hasNonEmptyValue($input, '--valid'));
    }

    /**
     * Test contextual fallbacks resolve dynamically from related surface and text tokens.
     */
    public function test_contextual_fallbacks_from_surface_and_text(): void
    {
        $input = [
            '--bg-surface' => '#1e293b',
            '--text-primary' => '#f8fafc',
            '--bg-surface-elevated' => '#334155',
            '--text-secondary' => '#94a3b8',
        ];

        $resolved = $this->resolver->resolveDefaults($input);

        $this->assertSame('#1e293b', $resolved['--header-bg']);
        $this->assertSame('#f8fafc', $resolved['--header-text']);
        $this->assertSame('#334155', $resolved['--footer-bg']);
        $this->assertSame('#94a3b8', $resolved['--footer-text']);
    }

    /**
     * Test explicit user values are never overridden by defaults or fallbacks.
     */
    public function test_explicit_user_values_never_overridden(): void
    {
        $input = [
            '--container-max-width' => '1600px',
            '--header-bg' => '#ff0000',
            '--bg-surface' => '#121212',
        ];

        $resolved = $this->resolver->resolveDefaults($input);

        $this->assertSame('1600px', $resolved['--container-max-width']);
        $this->assertSame('#ff0000', $resolved['--header-bg']);
    }

    /**
     * Test historical key order is strictly preserved in empty input resolution.
     */
    public function test_historical_key_order_is_preserved(): void
    {
        $resolved = $this->resolver->resolveDefaults([]);

        $expectedOrder = [
            '--btn-radius',
            '--card-radius',
            '--shadow-card',
            '--header-bg',
            '--header-text',
            '--footer-bg',
            '--footer-text',
            '--container-max-width',
            '--page-padding-x',
        ];

        $this->assertSame($expectedOrder, array_keys($resolved));
    }

    /**
     * Test getStaticDefaults returns static defaults array.
     */
    public function test_static_defaults_getter(): void
    {
        $defaults = $this->resolver->getStaticDefaults();

        $this->assertArrayHasKey('--btn-radius', $defaults);
        $this->assertArrayHasKey('--card-radius', $defaults);
        $this->assertArrayHasKey('--shadow-card', $defaults);
        $this->assertArrayHasKey('--container-max-width', $defaults);
        $this->assertArrayHasKey('--page-padding-x', $defaults);
    }
}
