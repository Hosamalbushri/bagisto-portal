<?php

namespace Platform\Portal\Tests\Feature\Tenant;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageTranslation;
use Platform\Portal\Tests\TestCase;

class PageSlugUniquenessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureChannelsExist();
    }

    public function test_b1_duplicate_slug_in_same_channel_on_create_returns_422_without_creating_duplicate(): void
    {
        $existing = $this->createPage('about-us', 1, 'Existing About Us');
        $admin = $this->makeAdminUser(['portal.pages.create']);

        try {
            $this->actingAs($admin, 'admin');

            $response = $this->postJson(route('admin.portal.pages.store'), [
                'slug' => 'about-us',
                'channel_id' => 1,
                'status' => '1',
                'title' => 'Duplicate About Us',
                'locale' => 'en',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['slug']);

            $this->assertSame(1, PortalPage::where('slug', 'about-us')->where('channel_id', 1)->count());
            $this->assertSame('Existing About Us', PortalPage::findOrFail($existing->id)->translate('en')->title);
        } finally {
            PortalPage::where('slug', 'about-us')->delete();
        }
    }

    public function test_b2_duplicate_slug_in_same_channel_on_update_returns_422_without_modifying_records(): void
    {
        $pageA = $this->createPage('page-alpha', 1, 'Alpha Title');
        $pageB = $this->createPage('page-beta', 1, 'Beta Title');
        $admin = $this->makeAdminUser(['portal.pages.edit']);

        try {
            $this->actingAs($admin, 'admin');

            $response = $this->putJson(route('admin.portal.pages.update', $pageB->id), [
                'slug' => 'page-alpha',
                'channel_id' => 1,
                'status' => '1',
                'title' => 'Beta Attempting Alpha Slug',
                'locale' => 'en',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['slug']);

            $this->assertSame('page-alpha', PortalPage::findOrFail($pageA->id)->slug);
            $this->assertSame('page-beta', PortalPage::findOrFail($pageB->id)->slug);
            $this->assertSame('Beta Title', PortalPage::findOrFail($pageB->id)->translate('en')->title);
        } finally {
            $pageA->delete();
            $pageB->delete();
        }
    }

    public function test_b3_same_slug_in_different_channels_succeeds(): void
    {
        $pageCh1 = $this->createPage('pricing', 1, 'Pricing CH1');
        $admin = $this->makeAdminUser(['portal.pages.create']);

        try {
            $this->actingAs($admin, 'admin');

            $response = $this->postJson(route('admin.portal.pages.store'), [
                'slug' => 'pricing',
                'channel_id' => 2,
                'status' => '1',
                'title' => 'Pricing CH2',
                'locale' => 'en',
            ]);

            $response->assertStatus(200);

            $ch1Record = PortalPage::where('slug', 'pricing')->where('channel_id', 1)->first();
            $ch2Record = PortalPage::where('slug', 'pricing')->where('channel_id', 2)->first();

            $this->assertNotNull($ch1Record);
            $this->assertNotNull($ch2Record);
            $this->assertNotSame($ch1Record->id, $ch2Record->id);
            $this->assertSame(1, (int) $ch1Record->channel_id);
            $this->assertSame(2, (int) $ch2Record->channel_id);
        } finally {
            PortalPage::where('slug', 'pricing')->delete();
        }
    }

    public function test_b4_retaining_existing_slug_on_update_succeeds(): void
    {
        $page = $this->createPage('careers', 1, 'Careers Old');
        $admin = $this->makeAdminUser(['portal.pages.edit']);

        try {
            $this->actingAs($admin, 'admin');

            $response = $this->putJson(route('admin.portal.pages.update', $page->id), [
                'slug' => 'careers',
                'channel_id' => 1,
                'status' => '1',
                'title' => 'Careers Updated',
                'locale' => 'en',
            ]);

            $response->assertStatus(200);
            $this->assertSame('careers', PortalPage::findOrFail($page->id)->slug);
        } finally {
            $page->delete();
        }
    }

    public function test_b5_missing_or_invalid_channel_id_is_rejected_with_422(): void
    {
        $admin = $this->makeAdminUser(['portal.pages.create']);

        try {
            $this->actingAs($admin, 'admin');

            $missingChannel = $this->postJson(route('admin.portal.pages.store'), [
                'slug' => 'no-channel-page',
                'status' => '1',
                'title' => 'No Channel',
                'locale' => 'en',
            ]);
            $missingChannel->assertStatus(422)
                ->assertJsonValidationErrors(['channel_id']);

            $invalidChannel = $this->postJson(route('admin.portal.pages.store'), [
                'slug' => 'bad-channel-page',
                'channel_id' => 99999,
                'status' => '1',
                'title' => 'Bad Channel',
                'locale' => 'en',
            ]);
            $invalidChannel->assertStatus(422)
                ->assertJsonValidationErrors(['channel_id']);

            $this->assertSame(0, PortalPage::whereIn('slug', ['no-channel-page', 'bad-channel-page'])->count());
        } finally {
            PortalPage::whereIn('slug', ['no-channel-page', 'bad-channel-page'])->delete();
        }
    }

    protected function ensureChannelsExist(): void
    {
        if (! Schema::hasTable('channels')) {
            Schema::create('channels', function ($table) {
                $table->increments('id');
                $table->string('code')->unique();
            });
        }

        foreach ([1 => 'default', 2 => 'b2b'] as $id => $code) {
            if (! DB::table('channels')->where('id', $id)->exists()) {
                DB::table('channels')->insert(['id' => $id, 'code' => $code]);
            }
        }
    }

    protected function makeAdminUser(array $permissions): User
    {
        $user = new User;
        $user->id = 1003;
        $user->name = 'Tenant Page Admin';
        $user->email = 'pageadmin@example.com';
        $user->setAttribute('role', (object) [
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $user;
    }

    protected function createPage(string $slug, int $channelId, string $title): PortalPage
    {
        $page = PortalPage::create([
            'slug' => $slug,
            'channel_id' => $channelId,
            'status' => 1,
        ]);

        PortalPageTranslation::create([
            'portal_page_id' => $page->id,
            'locale' => 'en',
            'title' => $title,
        ]);

        return $page->fresh();
    }
}
