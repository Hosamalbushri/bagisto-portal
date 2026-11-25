<?php

namespace Platform\Portal\Tests\Feature\Http;

use Illuminate\Support\Facades\Mail;
use Platform\Portal\Mail\ContactUs;
use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageSection;
use Platform\Portal\Models\PortalPageSectionTranslation;
use Platform\Portal\Models\PortalPageTranslation;
use Platform\Portal\Tests\TestCase;

class StorefrontRoutesTest extends TestCase
{
    public function test_home_page_returns_successful_response(): void
    {
        $response = $this->get(route('portal.home'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertSee('<header', false);
        $response->assertSee('<footer', false);
        $response->assertSee(trans('portal::app.home.welcome-title'));
    }

    public function test_home_page_renders_configured_dynamic_home_page_sections(): void
    {
        $channel = core()->getCurrentChannel();

        $homePage = PortalPage::create([
            'slug' => 'home-dynamic-test-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
            'is_home_page' => 1,
        ]);

        try {
            PortalPageTranslation::create([
                'portal_page_id' => $homePage->id,
                'locale' => 'en',
                'title' => 'Enterprise Portal Home',
                'description' => 'Configured dynamic homepage description',
            ]);

            $section = PortalPageSection::create([
                'portal_page_id' => $homePage->id,
                'type' => 'rich_content',
                'name' => 'Home Intro',
                'sort_order' => 1,
                'status' => 1,
            ]);

            PortalPageSectionTranslation::create([
                'portal_page_section_id' => $section->id,
                'locale' => 'en',
                'options' => [
                    'title' => 'Dynamic Home Headline',
                    'html' => '<p>Configured home section body.</p>',
                ],
            ]);

            $response = $this->get(route('portal.home'));

            $response->assertStatus(200);
            $response->assertSee('Enterprise Portal Home');
            $response->assertSee('Dynamic Home Headline');
            $response->assertSee('Configured home section body.');
        } finally {
            $homePage->delete();
        }
    }

    public function test_page_show_returns_200_for_active_page(): void
    {
        $channel = core()->getCurrentChannel();

        $page = PortalPage::create([
            'slug' => 'test-about-page-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
        ]);

        try {
            PortalPageTranslation::create([
                'portal_page_id' => $page->id,
                'locale' => 'en',
                'title' => 'About Us Test Page',
                'description' => 'Test content for about page',
            ]);

            $response = $this->get(route('portal.page.show', $page->slug));

            $response->assertStatus(200);
            $response->assertSee('About Us Test Page');
        } finally {
            $page->delete();
        }
    }

    public function test_page_show_returns_404_for_non_existent_slug(): void
    {
        $response = $this->get(route('portal.page.show', 'completely-non-existent-slug-xyz'));

        $response->assertStatus(404);
    }

    public function test_dynamic_fallback_route_resolves_custom_page_slug(): void
    {
        $channel = core()->getCurrentChannel();

        $page = PortalPage::create([
            'slug' => 'custom-dynamic-slug-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
        ]);

        try {
            PortalPageTranslation::create([
                'portal_page_id' => $page->id,
                'locale' => 'en',
                'title' => 'Dynamic Fallback Page Title',
            ]);

            $response = $this->get('/'.$page->slug);

            $response->assertStatus(200);
            $response->assertSee('Dynamic Fallback Page Title');
        } finally {
            $page->delete();
        }
    }

    public function test_contact_mail_submission_endpoint(): void
    {
        Mail::fake();

        $response = $this->from('/contact')->post(route('portal.contact_us.send_mail'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'contact' => '+971500000000',
            'message' => 'Hello from dynamic portal test',
            'website' => '',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/contact');
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success', trans('portal::app.home.thanks-for-contact'));

        Mail::assertQueued(ContactUs::class, function (ContactUs $mail) {
            return ($mail->data['name'] ?? null) === 'John Doe'
                && ($mail->data['email'] ?? null) === 'john@example.com'
                && ($mail->data['contact'] ?? null) === '+971500000000'
                && ($mail->data['message'] ?? null) === 'Hello from dynamic portal test';
        });
    }

    public function test_contact_mail_submission_rejects_invalid_and_spam_inputs(): void
    {
        Mail::fake();

        // 1. Missing required fields & malformed email
        $invalidResponse = $this->from('/contact')->post(route('portal.contact_us.send_mail'), [
            'name' => '',
            'email' => 'not-an-email',
            'message' => '',
            'website' => '',
        ]);

        $invalidResponse->assertStatus(302);
        $invalidResponse->assertRedirect('/contact');
        $invalidResponse->assertSessionHasErrors(['name', 'email', 'message']);
        $invalidResponse->assertSessionMissing('success');

        // 2. Honeypot spam submission
        $spamResponse = $this->from('/contact')->post(route('portal.contact_us.send_mail'), [
            'name' => 'Spam Bot',
            'email' => 'bot@spam.example',
            'message' => 'Buy cheap things',
            'website' => 'https://spam.example',
        ]);

        $spamResponse->assertStatus(302);
        $spamResponse->assertSessionHasErrors(['website']);
        $spamResponse->assertSessionMissing('success');

        Mail::assertNothingQueued();
    }

    public function test_contact_mail_submission_handles_mail_failure_gracefully(): void
    {
        Mail::shouldReceive('queue')
            ->once()
            ->andThrow(new \RuntimeException('SMTP delivery failure'));

        $response = $this->from('/contact')->post(route('portal.contact_us.send_mail'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'Hello from dynamic portal test',
            'website' => '',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/contact');
        $response->assertSessionHas('error', 'SMTP delivery failure');
        $response->assertSessionMissing('success');
    }

    public function test_legacy_page_route_renders_view(): void
    {
        $channel = core()->getCurrentChannel();

        $page = PortalPage::create([
            'slug' => 'legacy-page-test-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
        ]);

        try {
            PortalPageTranslation::create([
                'portal_page_id' => $page->id,
                'locale' => 'en',
                'title' => 'Legacy Page Content',
            ]);

            $response = $this->get(route('portal.page.legacy', $page->slug));

            $response->assertStatus(200);
            $response->assertSee('Legacy Page Content');
        } finally {
            $page->delete();
        }
    }
}
