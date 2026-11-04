<?php

namespace Platform\Portal\Tests\Unit\Features;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BestPracticesComponentsTest extends TestCase
{
    /**
     * Test shimmer page component renders without errors.
     */
    public function test_shimmer_page_component_renders(): void
    {
        $html = Blade::render('<x-portal::shimmer.page />');

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('animate-pulse', $html);
    }

    /**
     * Test shimmer card component renders count.
     */
    public function test_shimmer_card_component_renders_with_count(): void
    {
        $html = Blade::render('<x-portal::shimmer.card :count="2" />');

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('animate-pulse', $html);
    }

    /**
     * Test cookie consent component renders privacy banner.
     */
    public function test_cookie_consent_component_renders(): void
    {
        $html = Blade::render('<x-portal::layouts.cookie />');

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('id="portal-cookie-consent"', $html);
        $this->assertStringContainsString('portal-cookie-accept', $html);
        $this->assertStringContainsString('portal-cookie-reject', $html);
    }

    /**
     * Test SEO component renders canonical, OpenGraph, Twitter, and Schema.org JSON-LD.
     */
    public function test_seo_component_renders_full_meta_and_json_ld(): void
    {
        $html = Blade::render('<x-portal::seo
            title="Awesome Page"
            description="A detailed description for search engines."
            keywords="portal, content, news"
            type="article"
            author="Hosam"
        />');

        $this->assertStringContainsString('canonical', $html);
        $this->assertStringContainsString('og:title', $html);
        $this->assertStringContainsString('og:description', $html);
        $this->assertStringContainsString('twitter:card', $html);
        $this->assertStringContainsString('application/ld+json', $html);
        $this->assertStringContainsString('"@type":"Article"', $html);
        $this->assertStringContainsString('"name":"Hosam"', $html);
    }

    /**
     * Test breadcrumbs partial includes Schema.org BreadcrumbList microdata.
     */
    public function test_breadcrumbs_renders_schema_org_json_ld(): void
    {
        $dummyBreadcrumbs = collect([
            (object) ['title' => 'Home', 'url' => url('/')],
            (object) ['title' => 'Blog', 'url' => url('/blog')],
            (object) ['title' => 'Post Detail', 'url' => null],
        ]);

        $html = view('portal::partials.breadcrumbs', ['breadcrumbs' => $dummyBreadcrumbs])->render();

        $this->assertStringContainsString('aria-label="breadcrumbs"', $html);
        $this->assertStringContainsString('application/ld+json', $html);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
        $this->assertStringContainsString('"name":"Home"', $html);
        $this->assertStringContainsString('"name":"Post Detail"', $html);
    }

    /**
     * Test cache invalidator listens to portal domain events.
     */
    public function test_cache_invalidator_listens_to_page_and_theme_events(): void
    {
        $events = [
            'portal.page.created',
            'portal.page.updated',
            'portal.page.deleted',
            'portal.theme.activated',
            'portal.menu.updated',
        ];

        foreach ($events as $event) {
            $this->assertTrue(Event::hasListeners($event), "Event {$event} should have registered listeners.");
        }
    }
}
