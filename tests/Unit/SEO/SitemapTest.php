<?php

namespace Platform\Portal\Tests\Unit\SEO;

use Tests\TestCase;

class SitemapTest extends TestCase
{
    /**
     * Sitemap must return valid XML content-type and include sitemap tags.
     */
    public function test_sitemap_returns_valid_xml(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));

        $content = $response->getContent();
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('<loc>', $content);
        $this->assertStringContainsString('<priority>', $content);
    }
}
