<?php

namespace Platform\Portal\Tests\Unit\Security;

use Platform\Portal\Models\PortalMenuItem;
use Platform\Portal\Repositories\PortalMenuItemRepository;
use Tests\TestCase;

class MenuUrlSanitizationTest extends TestCase
{
    /**
     * Test that dangerous URL schemes and invisible control characters are blocked in PortalMenuItem.
     */
    public function test_dangerous_schemes_and_control_characters_are_blocked(): void
    {
        $item = new PortalMenuItem;
        $item->type = 'custom';

        // 1. Direct javascript / data / vbscript
        $item->url = 'javascript:alert("XSS")';
        $this->assertEquals('#', $item->getUrl(), 'javascript: scheme must resolve to #');

        $item->url = 'JaVaScRiPt:alert(1)';
        $this->assertEquals('#', $item->getUrl(), 'Mixed-case javascript: scheme must resolve to #');

        $item->url = 'data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==';
        $this->assertEquals('#', $item->getUrl(), 'data: scheme must resolve to #');

        $item->url = 'vbscript:msgbox(1)';
        $this->assertEquals('#', $item->getUrl(), 'vbscript: scheme must resolve to #');

        // 2. Control characters & invisible spaces evasion
        $item->url = "java\x00script:alert(1)";
        $this->assertEquals('#', $item->getUrl(), 'Null-byte evasion in javascript: must resolve to #');

        $item->url = "java\x09script:alert(1)";
        $this->assertEquals('#', $item->getUrl(), 'Tab evasion in javascript: must resolve to #');

        $item->url = "\x1fjavascript:alert(1)";
        $this->assertEquals('#', $item->getUrl(), 'Control character prefix must resolve to #');

        // 3. Non-whitelisted schemes (Allowlist enforcement)
        $item->url = 'ftp://files.example.com';
        $this->assertEquals('#', $item->getUrl(), 'ftp:// scheme must resolve to #');

        $item->url = 'file:///etc/passwd';
        $this->assertEquals('#', $item->getUrl(), 'file:// scheme must resolve to #');

        // 4. Exclusively allowed URL schemes
        $item->url = 'https://example.com/portal';
        $this->assertEquals('https://example.com/portal', $item->getUrl());

        $item->url = 'http://example.com/portal';
        $this->assertEquals('http://example.com/portal', $item->getUrl());

        $item->url = '/about-us';
        $this->assertEquals('/about-us', $item->getUrl());

        $item->url = '#section-contact';
        $this->assertEquals('#section-contact', $item->getUrl());

        $item->url = 'mailto:support@example.com';
        $this->assertEquals('mailto:support@example.com', $item->getUrl());

        $item->url = 'tel:+1234567890';
        $this->assertEquals('tel:+1234567890', $item->getUrl());
    }

    /**
     * Test repository sanitizeUrl method directly.
     */
    public function test_repository_sanitize_url_method(): void
    {
        $repo = app(PortalMenuItemRepository::class);

        $this->assertEquals('#', $repo->sanitizeUrl("java\x00script:alert(1)"));
        $this->assertEquals('#', $repo->sanitizeUrl('ftp://example.com'));
        $this->assertEquals('https://example.com', $repo->sanitizeUrl('https://example.com'));
        $this->assertEquals('/path/to/page', $repo->sanitizeUrl('/path/to/page'));
        $this->assertEquals('#anchor', $repo->sanitizeUrl('#anchor'));
        $this->assertEquals('mailto:admin@example.com', $repo->sanitizeUrl('mailto:admin@example.com'));
        $this->assertEquals('tel:+966500000000', $repo->sanitizeUrl('tel:+966500000000'));
    }
}
