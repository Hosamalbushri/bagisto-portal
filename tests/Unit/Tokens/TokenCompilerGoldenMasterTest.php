<?php

namespace Platform\Portal\Tests\Unit\Tokens;

use Platform\Portal\Services\TokenCompilerService;
use Tests\TestCase;

class TokenCompilerGoldenMasterTest extends TestCase
{
    protected TokenCompilerService $compiler;

    protected string $fixturesPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->compiler = app(TokenCompilerService::class);
        $this->fixturesPath = dirname(__DIR__, 2).'/Fixtures/Tokens';
    }

    /**
     * Helper to read fixture file.
     */
    protected function getFixture(string $name): string
    {
        $file = "{$this->fixturesPath}/{$name}.css";
        $this->assertFileExists($file, "Golden master fixture [{$name}.css] does not exist.");

        return (string) file_get_contents($file);
    }

    /**
     * Test byte-for-byte exact output equivalence for empty token input.
     */
    public function test_golden_master_empty_input(): void
    {
        $expected = $this->getFixture('scenario_empty');
        $actual = $this->compiler->compile([]);

        $this->assertSame($expected, $actual, 'Compiled CSS does not match golden master for empty input.');
    }

    /**
     * Test byte-for-byte exact output equivalence for standard brand and surface tokens.
     */
    public function test_golden_master_standard_brand_surfaces(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--brand-accent' => '#fe8312',
            '--bg-app' => '#f8fafc',
            '--bg-surface' => '#ffffff',
            '--text-primary' => '#090d16',
            '--font-arabic' => "'Cairo', sans-serif",
            '--radius-base' => '0.75rem',
        ];

        $expected = $this->getFixture('scenario_standard_brand_surfaces');
        $actual = $this->compiler->compile($tokens);

        $this->assertSame($expected, $actual, 'Compiled CSS does not match golden master for standard brand & surfaces.');
    }

    /**
     * Test byte-for-byte exact output equivalence for partial token input.
     */
    public function test_golden_master_partial_tokens(): void
    {
        $tokens = [
            '--brand-primary' => '#059669',
        ];

        $expected = $this->getFixture('scenario_partial_tokens');
        $actual = $this->compiler->compile($tokens);

        $this->assertSame($expected, $actual, 'Compiled CSS does not match golden master for partial tokens.');
    }

    /**
     * Test byte-for-byte exact output equivalence for dark overrides using --dark-*.
     */
    public function test_golden_master_dark_overrides(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--bg-app' => '#f8fafc',
            '--dark-bg-app' => '#080b11',
            '--dark-text-primary' => '#f8fafc',
        ];

        $expected = $this->getFixture('scenario_dark_overrides');
        $actual = $this->compiler->compile($tokens);

        $this->assertSame($expected, $actual, 'Compiled CSS does not match golden master for dark overrides.');
    }

    /**
     * Test byte-for-byte exact output equivalence with custom CSS appended.
     */
    public function test_golden_master_custom_css(): void
    {
        $tokens = [
            '--brand-primary' => '#7c3aed',
            'custom_css' => '.badge-glow { filter: drop-shadow(0 0 10px rgba(124, 58, 237, 0.5)); }',
        ];

        $expected = $this->getFixture('scenario_custom_css');
        $actual = $this->compiler->compile($tokens);

        $this->assertSame($expected, $actual, 'Compiled CSS does not match golden master for custom CSS.');
    }

    /**
     * Test byte-for-byte exact output equivalence with theme code argument passed.
     */
    public function test_golden_master_theme_code_arg(): void
    {
        $tokens = [
            '--brand-primary' => '#d97706',
        ];

        $expected = $this->getFixture('scenario_theme_code_arg');
        $actual = $this->compiler->compile($tokens, 'emerald_tech');

        $this->assertSame($expected, $actual, 'Compiled CSS does not match golden master for theme code argument.');
    }

    /**
     * Test byte-for-byte exact output equivalence for component bindings tokens.
     */
    public function test_golden_master_component_bindings(): void
    {
        $tokens = [
            '--header-bg' => '#1e293b',
            '--header-text' => '#f8fafc',
            '--footer-bg' => '#0f172a',
            '--footer-text' => '#94a3b8',
            '--card-radius' => '1.5rem',
            '--btn-radius' => '0.375rem',
        ];

        $expected = $this->getFixture('scenario_component_bindings');
        $actual = $this->compiler->compile($tokens);

        $this->assertSame($expected, $actual, 'Compiled CSS does not match golden master for component bindings.');
    }
}
