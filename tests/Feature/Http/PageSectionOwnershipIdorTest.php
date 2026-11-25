<?php

namespace Platform\Portal\Tests\Feature\Http;

use Illuminate\Foundation\Auth\User;
use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageSection;
use Platform\Portal\Models\PortalPageSectionTranslation;
use Platform\Portal\Models\PortalPageTranslation;
use Platform\Portal\Tests\TestCase;

class PageSectionOwnershipIdorTest extends TestCase
{
    public function test_authorized_user_can_update_section_belonging_to_same_page(): void
    {
        $pageA = $this->createPage('idor-page-a-ok', 'Page A');
        $sectionA = $this->createSection($pageA->id, 'Section A', '<p>Section A Original</p>');
        $admin = $this->makeAdminUser(['portal.pages.customize']);

        try {
            $this->actingAs($admin, 'admin');

            $response = $this->postJson(
                route('admin.portal.pages.sections.draft', ['id' => $pageA->id, 'sectionId' => $sectionA->id]),
                ['options' => ['content' => '<p>Section A Updated</p>']]
            );

            $response->assertStatus(200)
                ->assertJson(['has_draft' => true]);

            $reloadedA = PortalPageSection::findOrFail($sectionA->id);
            $this->assertSame(['content' => '<p>Section A Updated</p>'], $reloadedA->translate('en')->draft_options);
            $this->assertSame(['content' => '<p>Section A Original</p>'], $reloadedA->translate('en')->options);
        } finally {
            $pageA->delete();
        }
    }

    public function test_updating_section_belonging_to_another_page_is_rejected_without_modification(): void
    {
        $pageA = $this->createPage('idor-page-a', 'Page A');
        $pageB = $this->createPage('idor-page-b', 'Page B');
        $sectionA = $this->createSection($pageA->id, 'Section A', '<p>Section A Original</p>');
        $sectionB = $this->createSection($pageB->id, 'Section B', '<p>Section B Original</p>', ['--brand-primary' => '#111111']);
        $admin = $this->makeAdminUser(['portal.pages.customize']);

        try {
            $this->actingAs($admin, 'admin');

            // Attempt IDOR: update Section B (belongs to Page B) through Page A's route
            $draftResponse = $this->postJson(
                route('admin.portal.pages.sections.draft', ['id' => $pageA->id, 'sectionId' => $sectionB->id]),
                ['options' => ['content' => '<p>Tampered via Page A</p>']]
            );
            $draftResponse->assertStatus(404);

            // Attempt IDOR on tokens, status, and delete endpoints as well
            $this->postJson(
                route('admin.portal.pages.sections.tokens', ['id' => $pageA->id, 'sectionId' => $sectionB->id]),
                ['tokens' => ['--brand-primary' => '#ff0000']]
            )->assertStatus(404);

            $this->postJson(
                route('admin.portal.pages.sections.status', ['id' => $pageA->id, 'sectionId' => $sectionB->id]),
                ['status' => false]
            )->assertStatus(404);

            $this->deleteJson(
                route('admin.portal.pages.sections.delete', ['id' => $pageA->id, 'sectionId' => $sectionB->id])
            )->assertStatus(404);

            // Reload Section B from DB and prove it was completely untouched
            $reloadedB = PortalPageSection::find($sectionB->id);
            $this->assertNotNull($reloadedB, 'Section B must still exist after rejected delete.');
            $this->assertSame($pageB->id, (int) $reloadedB->portal_page_id);
            $this->assertTrue((bool) $reloadedB->draft_status);
            $this->assertSame(['--brand-primary' => '#111111'], $reloadedB->draft_tokens);
            $this->assertSame(['content' => '<p>Section B Original</p>'], $reloadedB->translate('en')->draft_options);
            $this->assertSame(['content' => '<p>Section B Original</p>'], $reloadedB->translate('en')->options);

            // Section A also remains untouched
            $reloadedA = PortalPageSection::findOrFail($sectionA->id);
            $this->assertSame(['content' => '<p>Section A Original</p>'], $reloadedA->translate('en')->draft_options);
        } finally {
            $pageA->delete();
            $pageB->delete();
        }
    }

    public function test_nonexistent_section_returns_404(): void
    {
        $pageA = $this->createPage('idor-missing-section-page', 'Page A');
        $admin = $this->makeAdminUser(['portal.pages.customize']);

        try {
            $this->actingAs($admin, 'admin');

            $response = $this->postJson(
                route('admin.portal.pages.sections.draft', ['id' => $pageA->id, 'sectionId' => 999999]),
                ['options' => ['content' => '<p>Does not exist</p>']]
            );

            $response->assertStatus(404);
        } finally {
            $pageA->delete();
        }
    }

    public function test_unauthorized_user_receives_403_before_ownership_check_and_cannot_modify_section(): void
    {
        $pageA = $this->createPage('idor-unauth-page-a', 'Page A');
        $pageB = $this->createPage('idor-unauth-page-b', 'Page B');
        $sectionB = $this->createSection($pageB->id, 'Section B', '<p>Protected Content</p>');
        $unauthorizedAdmin = $this->makeAdminUser(['portal.pages.edit']);

        try {
            $this->actingAs($unauthorizedAdmin, 'admin');

            $response = $this->postJson(
                route('admin.portal.pages.sections.draft', ['id' => $pageA->id, 'sectionId' => $sectionB->id]),
                ['options' => ['content' => '<p>Unauthorized IDOR</p>']]
            );

            $response->assertStatus(403);

            $reloadedB = PortalPageSection::findOrFail($sectionB->id);
            $this->assertSame(['content' => '<p>Protected Content</p>'], $reloadedB->translate('en')->draft_options);
        } finally {
            $pageA->delete();
            $pageB->delete();
        }
    }

    protected function makeAdminUser(array $permissions): User
    {
        $user = new User;
        $user->id = 1002;
        $user->name = 'Customizer Admin';
        $user->email = 'customizer@example.com';
        $user->setAttribute('role', (object) [
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $user;
    }

    protected function createPage(string $slug, string $title): PortalPage
    {
        $page = PortalPage::create([
            'slug' => $slug,
            'channel_id' => 1,
            'status' => 1,
        ]);

        PortalPageTranslation::create([
            'portal_page_id' => $page->id,
            'locale' => 'en',
            'title' => $title,
        ]);

        return $page->fresh();
    }

    protected function createSection(int $pageId, string $name, string $content, ?array $draftTokens = null): PortalPageSection
    {
        $section = PortalPageSection::create([
            'portal_page_id' => $pageId,
            'type' => 'rich_content',
            'name' => $name,
            'sort_order' => 1,
            'status' => 1,
            'draft_status' => true,
            'draft_tokens' => $draftTokens,
        ]);

        PortalPageSectionTranslation::create([
            'portal_page_section_id' => $section->id,
            'locale' => 'en',
            'options' => ['content' => $content],
            'draft_options' => ['content' => $content],
        ]);

        return $section->fresh();
    }
}
