<?php

namespace Platform\Portal\Tests\Feature\Http;

use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageTranslation;
use Platform\Portal\Tests\TestCase;

class StorefrontRoutesTest extends TestCase
{
    public function test_home_page_returns_successful_response(): void
    {
        $response = $this->get(route('portal.home'));

        $response->assertStatus(200);
    }

    public function test_page_show_returns_200_for_active_page(): void
    {
        $channel = core()->getCurrentChannel();

        $page = PortalPage::create([
            'slug' => 'test-about-page-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
        ]);

        PortalPageTranslation::create([
            'portal_page_id' => $page->id,
            'locale' => 'en',
            'title' => 'About Us Test Page',
            'description' => 'Test content for about page',
        ]);

        $response = $this->get(route('portal.page.show', $page->slug));

        $response->assertStatus(200);
        $response->assertSee('About Us Test Page');

        $page->delete();
    }

    public function test_page_show_returns_404_for_non_existent_slug(): void
    {
        $response = $this->get(route('portal.page.show', 'completely-non-existent-slug-xyz'));

        $response->assertStatus(404);
    }

    public function test_contact_us_page_returns_successful_response(): void
    {
        $response = $this->get(route('portal.contact_us'));

        $response->assertStatus(200);
    }

    public function test_legacy_page_route_renders_view(): void
    {
        $channel = core()->getCurrentChannel();

        $page = PortalPage::create([
            'slug' => 'legacy-page-test-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
        ]);

        PortalPageTranslation::create([
            'portal_page_id' => $page->id,
            'locale' => 'en',
            'title' => 'Legacy Page Content',
        ]);

        $response = $this->get(route('portal.page.legacy', $page->slug));

        $response->assertStatus(200);
        $response->assertSee('Legacy Page Content');

        $page->delete();
    }
}
