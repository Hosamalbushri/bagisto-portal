<?php

namespace Platform\Portal\Tests\Unit\Tokens;

use Platform\Portal\Services\Tokens\TokenSanitizer;
use Tests\TestCase;

class TokenSanitizerTest extends TestCase
{
    protected TokenSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = app(TokenSanitizer::class);
    }

    /**
     * Test sanitizer preserves valid CSS custom properties.
     */
    public function test_sanitizes_valid_tokens_properly(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6',
            '--brand-accent' => 'rgba(254, 131, 18, 0.9)',
            '--radius-base' => '12px',
            '--font-arabic' => "'Cairo', sans-serif",
            '--container-max-width' => '1280px',
            '--numeric-value' => 42,
        ];

        $clean = $this->sanitizer->sanitizeTokens($tokens);

        $this->assertEquals('#1c66f6', $clean['--brand-primary']);
        $this->assertEquals('rgba(254, 131, 18, 0.9)', $clean['--brand-accent']);
        $this->assertEquals('12px', $clean['--radius-base']);
        $this->assertEquals("'Cairo', sans-serif", $clean['--font-arabic']);
        $this->assertEquals('1280px', $clean['--container-max-width']);
        $this->assertEquals('42', $clean['--numeric-value']);
    }

    /**
     * Test sanitizer rejects invalid keys without dashes or with illegal characters.
     */
    public function test_rejects_invalid_token_keys(): void
    {
        $tokens = [
            'no_dashes' => '#1c66f6',
            '-single-dash' => '#1c66f6',
            '--key;semicolon' => '#1c66f6',
            '--key{brace' => '#1c66f6',
            '--key with space' => '#1c66f6',
            '--key$dollar' => '#1c66f6',
            '--valid-key_123' => '#059669',
        ];

        $clean = $this->sanitizer->sanitizeTokens($tokens);

        $this->assertArrayNotHasKey('no_dashes', $clean);
        $this->assertArrayNotHasKey('-single-dash', $clean);
        $this->assertArrayNotHasKey('--key;semicolon', $clean);
        $this->assertArrayNotHasKey('--key{brace', $clean);
        $this->assertArrayNotHasKey('--key with space', $clean);
        $this->assertArrayNotHasKey('--key$dollar', $clean);
        $this->assertArrayHasKey('--valid-key_123', $clean);
    }

    /**
     * Test sanitizer strips punctuation that breaks out of CSS rule.
     */
    public function test_strips_breakout_punctuation_from_values(): void
    {
        $tokens = [
            '--brand-primary' => '#1c66f6; color: red;',
            '--border-color' => 'blue } .evil { display: none; }',
            '--html-payload' => '<style>body { background: black; }</style>',
        ];

        $clean = $this->sanitizer->sanitizeTokens($tokens);

        $this->assertEquals('#1c66f6 color: red', $clean['--brand-primary']);
        $this->assertEquals('blue  .evil  display: none ', $clean['--border-color']);
        $this->assertEquals('body  background: black ', $clean['--html-payload']);
    }

    /**
     * Test sanitizer blocks dangerous function calls, URLs, and comments.
     */
    public function test_blocks_dangerous_injection_vectors(): void
    {
        $tokens = [
            '--url-vector' => 'url("https://attacker.com/leak.png")',
            '--expression-vector' => 'expression(alert(1))',
            '--import-vector' => '@import "evil.css"',
            '--backslash-vector' => '\\00002a',
            '--comment-vector' => '/* comment */ #fff',
            '--safe-token' => '#ffffff',
        ];

        $clean = $this->sanitizer->sanitizeTokens($tokens);

        $this->assertArrayNotHasKey('--url-vector', $clean);
        $this->assertArrayNotHasKey('--expression-vector', $clean);
        $this->assertArrayNotHasKey('--import-vector', $clean);
        $this->assertArrayNotHasKey('--backslash-vector', $clean);
        $this->assertArrayNotHasKey('--comment-vector', $clean);
        $this->assertArrayHasKey('--safe-token', $clean);
    }

    /**
     * Test Custom CSS sanitizer allows valid rules and neutralizes XSS.
     */
    public function test_sanitizes_custom_css_properly(): void
    {
        $customCss = "
            /* Valid Header Style */
            .header-banner {
                background: linear-gradient(135deg, #1c66f6, #fe8312);
                border-radius: 8px;
            }

            @media (max-width: 768px) {
                .header-banner { padding: 4px; }
            }

            <script>alert('xss')</script>
            </style><svg onload=\"alert(2)\">
            .test { behavior: url(script.htc); -moz-binding: url(binding.xml); }
            .test2 { color: expression(alert(3)); }
            .test3 { background: javascript:alert(4); }
            @import url('https://evil.com/style.css');
        ";

        $clean = $this->sanitizer->sanitizeCustomCss($customCss);

        $this->assertStringContainsString('.header-banner {', $clean);
        $this->assertStringContainsString('@media (max-width: 768px)', $clean);
        $this->assertStringNotContainsString('<script>', $clean);
        $this->assertStringNotContainsString('</script>', $clean);
        $this->assertStringNotContainsString('</style>', $clean);
        $this->assertStringNotContainsString('<svg', $clean);
        $this->assertStringNotContainsString('behavior:', $clean);
        $this->assertStringNotContainsString('expression(', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('@import', $clean);
    }

    /**
     * Test Custom CSS sanitizer gracefully handles null and non-string inputs.
     */
    public function test_sanitizes_empty_and_null_custom_css(): void
    {
        $this->assertEquals('', $this->sanitizer->sanitizeCustomCss(null));
        $this->assertEquals('', $this->sanitizer->sanitizeCustomCss(''));
        $this->assertEquals('', $this->sanitizer->sanitizeCustomCss('   '));
    }
}
