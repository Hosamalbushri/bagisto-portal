<?php

namespace Platform\Portal\Tests\Unit\Features;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageTranslation;
use Tests\TestCase;
use Webkul\Core\Repositories\SubscribersListRepository;

class PortalArchitectureFeaturesTest extends TestCase
{
    /**
     * Test WebMCP blade component renders declarative agent tools and JS bridge.
     */
    public function test_webmcp_component_renders_declarative_tools(): void
    {
        $html = Blade::render('<x-portal::layouts.webmcp /> @stack("scripts")');

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('toolname="search_portal"', $html);
        $this->assertStringContainsString('toolname="view_portal_page"', $html);
        $this->assertStringContainsString('toolname="subscribe_newsletter"', $html);
        $this->assertStringContainsString('modelContext', $html);
    }

    /**
     * Test contact us view includes anti-spam honeypot input.
     */
    public function test_contact_us_view_includes_honeypot_field(): void
    {
        $response = $this->get(route('portal.contact_us'));

        $response->assertStatus(200);
        $response->assertSee('name="website"', false);
        $response->assertSee('tabindex="-1"', false);
    }

    /**
     * Test newsletter subscription endpoint successfully subscribes an email.
     */
    public function test_newsletter_subscription_stores_subscriber(): void
    {
        Event::fake([
            'portal.customer.subscription.before',
            'portal.customer.subscription.after',
        ]);

        $email = 'test_'.uniqid().'@example.com';

        $response = $this->postJson(route('portal.subscription.store'), [
            'email' => $email,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        Event::assertDispatched('portal.customer.subscription.before');
        Event::assertDispatched('portal.customer.subscription.after');

        $repository = app(SubscribersListRepository::class);
        $subscriber = $repository->findOneByField('email', $email);

        $this->assertNotNull($subscriber);
        $this->assertEquals(1, $subscriber->is_subscribed);

        // Attempting to subscribe again with same email returns 422 warning
        $secondResponse = $this->postJson(route('portal.subscription.store'), [
            'email' => $email,
        ]);

        $secondResponse->assertStatus(422);

        // Clean up
        $repository->delete($subscriber->id);
    }

    /**
     * Test newsletter unsubscribe removes subscriber.
     */
    public function test_newsletter_unsubscription_by_token(): void
    {
        $repository = app(SubscribersListRepository::class);
        $channel = core()->getCurrentChannel();

        $subscriber = $repository->create([
            'email' => 'unsub_'.uniqid().'@example.com',
            'token' => 'test_token_'.uniqid(),
            'is_subscribed' => 1,
            'channel_id' => $channel->id,
        ]);

        $response = $this->get(route('portal.subscription.destroy', $subscriber->token));

        $response->assertRedirect(route('portal.home'));

        $check = $repository->findOneByField('token', $subscriber->token);
        $this->assertNull($check);
    }

    /**
     * Test search controller returns json and view.
     */
    public function test_search_controller_handles_queries(): void
    {
        $channel = core()->getCurrentChannel();

        $page = PortalPage::create([
            'slug' => 'unique-searchable-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
        ]);

        PortalPageTranslation::create([
            'portal_page_id' => $page->id,
            'locale' => 'en',
            'title' => 'Unique Searchable Feature Page',
            'meta_description' => 'A unique description for search indexing.',
        ]);

        // HTML Response
        $response = $this->get(route('portal.search.index', ['query' => 'Unique Searchable']));
        $response->assertStatus(200);
        $response->assertSee('Unique Searchable Feature Page');

        // JSON Response
        $jsonResponse = $this->getJson(route('portal.search.index', ['query' => 'Unique Searchable']));
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJsonStructure([
            'query',
            'count',
            'data',
        ]);
        $this->assertGreaterThanOrEqual(1, $jsonResponse->json('count'));

        $page->delete();
    }

    /**
     * Test PWA web manifest endpoint returns valid metadata.
     */
    public function test_web_manifest_endpoint_returns_pwa_metadata(): void
    {
        $response = $this->get(route('portal.manifest'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/manifest+json', $response->headers->get('Content-Type'));

        $data = $response->json();
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('start_url', $data);
        $this->assertEquals('standalone', $data['display']);
        $this->assertArrayHasKey('icons', $data);
    }

    /**
     * Test error views render custom portal branded page.
     */
    public function test_error_views_render_portal_branded_pages(): void
    {
        $html404 = Blade::render('@include("portal::errors.404")');
        $this->assertStringContainsString('404', $html404);

        $html503 = Blade::render('@include("portal::errors.503")');
        $this->assertStringContainsString('503', $html503);
    }

    /**
     * Test SEO component renders hreflang tags when channel has multiple locales.
     */
    public function test_seo_component_renders_hreflang_tags(): void
    {
        $html = Blade::render('<x-portal::seo title="Test Multilingual" />');

        $this->assertNotEmpty($html);
        $this->assertStringContainsString('canonical', $html);
    }
}
