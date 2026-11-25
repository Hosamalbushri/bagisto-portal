<?php

namespace Platform\Portal\Tests\Feature\Http;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageSection;
use Platform\Portal\Models\PortalPageSectionTranslation;
use Platform\Portal\Models\PortalPageTranslation;
use Platform\Portal\Tests\TestCase;

class AdminRbacAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('channels')) {
            Schema::create('channels', function ($table) {
                $table->increments('id');
                $table->string('code')->unique();
            });

            DB::table('channels')->insert([
                'id' => 1,
                'code' => 'default',
            ]);
        }
    }

    public function test_guest_user_cannot_access_protected_admin_routes(): void
    {
        $page = $this->createPage('guest-guard-page', 'Guest Guard Page');
        $section = $this->createSection($page->id, 'Guest Guard Section');

        try {
            $webResponse = $this->get(route('admin.portal.pages.index'));
            $webResponse->assertRedirect(route('admin.session.create'));

            $jsonResponse = $this->postJson(
                route('admin.portal.pages.sections.draft', ['id' => $page->id, 'sectionId' => $section->id]),
                ['options' => ['content' => '<p>Guest attempt</p>']]
            );
            $jsonResponse->assertStatus(401);

            $this->assertFalse(bouncer()->hasPermission('portal.pages.customize'));

            $reloadedSection = PortalPageSection::findOrFail($section->id);
            $this->assertSame(['content' => '<p>Original</p>'], $reloadedSection->translate('en')->draft_options);
        } finally {
            $page->delete();
        }
    }

    public function test_authenticated_admin_without_permissions_is_denied_across_protected_routes(): void
    {
        $page = $this->createPage('no-perm-page', 'Original Page Title');
        $section = $this->createSection($page->id, 'No Perm Section');
        $admin = $this->makeAdminUser(permissions: []);

        try {
            $this->actingAs($admin, 'admin');

            $this->assertFalse(bouncer()->hasPermission('portal.pages.edit'));
            $this->assertFalse(bouncer()->hasPermission('portal.pages.customize'));
            $this->assertFalse(bouncer()->hasPermission('portal.customize.themes'));

            $updatePageResponse = $this->putJson(route('admin.portal.pages.update', $page->id), [
                'slug' => $page->slug,
                'channel_id' => 1,
                'status' => '1',
                'title' => 'Unauthorized Title Change',
                'locale' => 'en',
            ]);
            $updatePageResponse->assertStatus(403);

            $draftResponse = $this->postJson(
                route('admin.portal.pages.sections.draft', ['id' => $page->id, 'sectionId' => $section->id]),
                ['options' => ['content' => '<p>Unauthorized draft</p>']]
            );
            $draftResponse->assertStatus(403);

            $themeResponse = $this->postJson(route('admin.portal.themes.activate'), [
                'theme' => 'portal',
            ]);
            $themeResponse->assertStatus(403);

            $this->assertSame('Original Page Title', PortalPage::findOrFail($page->id)->translate('en')->title);
            $this->assertSame(['content' => '<p>Original</p>'], PortalPageSection::findOrFail($section->id)->translate('en')->draft_options);
        } finally {
            $page->delete();
        }
    }

    public function test_different_permission_names_produce_different_authorization_outcomes(): void
    {
        $page = $this->createPage('scoped-perm-page', 'Initial Scoped Title');
        $section = $this->createSection($page->id, 'Scoped Section');

        try {
            // Admin with only view/index permission cannot edit pages or customize sections
            $viewer = $this->makeAdminUser(permissions: ['portal.pages.index']);
            $this->actingAs($viewer, 'admin');

            $this->assertTrue(bouncer()->hasPermission('portal.pages.index'));
            $this->assertFalse(bouncer()->hasPermission('portal.pages.edit'));
            $this->assertFalse(bouncer()->hasPermission('portal.pages.customize'));

            $this->putJson(route('admin.portal.pages.update', $page->id), [
                'slug' => $page->slug,
                'channel_id' => 1,
                'status' => '1',
                'title' => 'Viewer Should Not Edit',
                'locale' => 'en',
            ])->assertStatus(403);

            $this->postJson(
                route('admin.portal.pages.sections.draft', ['id' => $page->id, 'sectionId' => $section->id]),
                ['options' => ['content' => '<p>Viewer Should Not Customize</p>']]
            )->assertStatus(403);

            // Admin with portal.pages.edit can update the page, but still cannot customize sections
            $editor = $this->makeAdminUser(permissions: ['portal.pages.edit']);
            $this->actingAs($editor, 'admin');

            $this->assertTrue(bouncer()->hasPermission('portal.pages.edit'));
            $this->assertFalse(bouncer()->hasPermission('portal.pages.customize'));

            $this->putJson(route('admin.portal.pages.update', $page->id), [
                'slug' => 'editor-updated-slug',
                'channel_id' => 1,
                'status' => '1',
                'title' => 'Editor Updated Title',
                'locale' => 'en',
            ])->assertStatus(200);

            $this->assertSame('editor-updated-slug', PortalPage::findOrFail($page->id)->slug);

            $this->postJson(
                route('admin.portal.pages.sections.draft', ['id' => $page->id, 'sectionId' => $section->id]),
                ['options' => ['content' => '<p>Editor Should Not Customize</p>']]
            )->assertStatus(403);

            $this->assertSame(['content' => '<p>Original</p>'], PortalPageSection::findOrFail($section->id)->translate('en')->draft_options);

            // Admin with portal.pages.customize can save section draft, but cannot update page metadata or activate themes
            $customizer = $this->makeAdminUser(permissions: ['portal.pages.customize']);
            $this->actingAs($customizer, 'admin');

            $this->postJson(
                route('admin.portal.pages.sections.draft', ['id' => $page->id, 'sectionId' => $section->id]),
                ['options' => ['content' => '<p>Customizer Updated</p>']]
            )->assertStatus(200);

            $this->assertSame(['content' => '<p>Customizer Updated</p>'], PortalPageSection::findOrFail($section->id)->translate('en')->draft_options);

            $this->putJson(route('admin.portal.pages.update', $page->id), [
                'slug' => $page->slug,
                'channel_id' => 1,
                'status' => '1',
                'title' => 'Customizer Should Not Edit Page',
                'locale' => 'en',
            ])->assertStatus(403);

            $this->postJson(route('admin.portal.themes.activate'), [
                'theme' => 'portal',
            ])->assertStatus(403);
        } finally {
            $page->delete();
        }
    }

    public function test_super_admin_role_with_all_permission_type_is_allowed(): void
    {
        $page = $this->createPage('super-admin-page', 'Before Super Admin');
        $section = $this->createSection($page->id, 'Super Admin Section');
        $superAdmin = $this->makeAdminUser(permissions: [], permissionType: 'all');

        try {
            $this->actingAs($superAdmin, 'admin');

            $this->assertTrue(bouncer()->hasPermission('portal.pages.edit'));
            $this->assertTrue(bouncer()->hasPermission('portal.pages.customize'));

            $this->putJson(route('admin.portal.pages.update', $page->id), [
                'slug' => 'super-admin-updated-slug',
                'channel_id' => 1,
                'status' => '1',
                'title' => 'After Super Admin',
                'locale' => 'en',
            ])->assertStatus(200);

            $this->postJson(
                route('admin.portal.pages.sections.draft', ['id' => $page->id, 'sectionId' => $section->id]),
                ['options' => ['content' => '<p>Super Admin Draft</p>']]
            )->assertStatus(200);

            $this->assertSame('super-admin-updated-slug', PortalPage::findOrFail($page->id)->slug);
            $this->assertSame(['content' => '<p>Super Admin Draft</p>'], PortalPageSection::findOrFail($section->id)->translate('en')->draft_options);
        } finally {
            $page->delete();
        }
    }

    protected function makeAdminUser(array $permissions, string $permissionType = 'custom'): User
    {
        $user = new User;
        $user->id = 1001;
        $user->name = 'Test Admin';
        $user->email = 'admin@example.com';
        $user->setAttribute('role', (object) [
            'permission_type' => $permissionType,
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

    protected function createSection(int $pageId, string $name): PortalPageSection
    {
        $section = PortalPageSection::create([
            'portal_page_id' => $pageId,
            'type' => 'rich_content',
            'name' => $name,
            'sort_order' => 1,
            'status' => 1,
            'draft_status' => true,
        ]);

        PortalPageSectionTranslation::create([
            'portal_page_section_id' => $section->id,
            'locale' => 'en',
            'options' => ['content' => '<p>Original</p>'],
            'draft_options' => ['content' => '<p>Original</p>'],
        ]);

        return $section->fresh();
    }
}
