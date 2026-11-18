<?php

namespace Platform\Portal\Tests\Feature\Http;

use Platform\Portal\Models\PortalMenu;
use Platform\Portal\Models\PortalMenuItem;
use Platform\Portal\Models\PortalMenuItemTranslation;
use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageSection;
use Platform\Portal\Models\PortalPageSectionTranslation;
use Platform\Portal\Models\PortalPageTranslation;
use Platform\Portal\Tests\TestCase;

class PortalApiRoutesTest extends TestCase
{
    public function test_sections_api_returns_registered_section_types(): void
    {
        $response = $this->getJson(route('portal.api.sections.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['code', 'title', 'description', 'icon', 'is_singleton', 'is_pinned'],
            ],
        ]);
    }

    public function test_pages_api_returns_list_of_published_pages(): void
    {
        $channel = core()->getCurrentChannel();

        $page = PortalPage::create([
            'slug' => 'api-test-page-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
        ]);

        PortalPageTranslation::create([
            'portal_page_id' => $page->id,
            'locale' => 'en',
            'title' => 'API Test Page',
            'description' => 'Test page for API listing',
        ]);

        $response = $this->getJson(route('portal.api.pages.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'slug' => $page->slug,
            'title' => 'API Test Page',
        ]);

        $page->delete();
    }

    public function test_page_detail_api_returns_page_with_sections(): void
    {
        $channel = core()->getCurrentChannel();

        $page = PortalPage::create([
            'slug' => 'api-detail-page-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
        ]);

        PortalPageTranslation::create([
            'portal_page_id' => $page->id,
            'locale' => 'en',
            'title' => 'API Detail Page Title',
            'description' => 'Detailed description',
        ]);

        $section = PortalPageSection::create([
            'portal_page_id' => $page->id,
            'type' => 'hero_scaffold',
            'name' => 'Hero Banner',
            'sort_order' => 1,
            'status' => 1,
        ]);

        PortalPageSectionTranslation::create([
            'portal_page_section_id' => $section->id,
            'locale' => 'en',
            'options' => ['title' => 'Welcome to Platform'],
        ]);

        $response = $this->getJson(route('portal.api.pages.show', $page->slug));

        $response->assertStatus(200);
        $response->assertJsonPath('data.title', 'API Detail Page Title');
        $response->assertJsonPath('data.slug', $page->slug);
        $response->assertJsonPath('data.sections.0.type', 'hero_scaffold');

        $page->delete();
    }

    public function test_page_detail_api_returns_404_for_unknown_slug(): void
    {
        $response = $this->getJson(route('portal.api.pages.show', 'unknown-nonexistent-slug-xyz'));

        $response->assertStatus(404);
    }

    public function test_menu_api_returns_menu_with_items_tree(): void
    {
        $channel = core()->getCurrentChannel();

        $menu = PortalMenu::create([
            'name' => 'API Main Navigation',
            'slug' => 'api-main-nav-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
        ]);

        $item = PortalMenuItem::create([
            'portal_menu_id' => $menu->id,
            'type' => 'custom',
            'url' => 'https://example.com',
            'target' => '_blank',
            'sort_order' => 1,
            'status' => 1,
        ]);

        PortalMenuItemTranslation::create([
            'portal_menu_item_id' => $item->id,
            'locale' => 'en',
            'title' => 'Home External Link',
        ]);

        $response = $this->getJson(route('portal.api.menus.show', $menu->slug));

        $response->assertStatus(200);
        $response->assertJsonPath('data.slug', $menu->slug);
        $response->assertJsonPath('data.items.0.title', 'Home External Link');
        $response->assertJsonPath('data.items.0.url', 'https://example.com');

        $menu->delete();
    }
}
