<?php

namespace Platform\Portal\Tests\Unit\Tokens;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Platform\Portal\Http\Controllers\Admin\PageController;
use Platform\Portal\Http\Controllers\Admin\PageCustomizeController;
use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageSection;
use Platform\Portal\Models\PortalPageSectionTranslation;
use Platform\Portal\Models\PortalPageTranslation;
use Platform\Portal\Repositories\PortalPageRepository;
use Platform\Portal\Repositories\PortalPageSectionRepository;
use Platform\Portal\Services\Tokens\TokenSanitizer;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class TokenPersistenceTest extends TestCase
{
    protected PortalPageRepository $pageRepository;

    protected PortalPageSectionRepository $sectionRepository;

    protected TokenSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('channels')) {
            Schema::create('channels', function ($table) {
                $table->increments('id');
                $table->string('code')->unique();
                $table->string('name')->nullable();
                $table->timestamps();
            });

            DB::table('channels')->insert([
                'id' => 1,
                'code' => 'default',
                'name' => 'Default Channel',
            ]);
        }

        $this->pageRepository = app(PortalPageRepository::class);
        $this->sectionRepository = app(PortalPageSectionRepository::class);
        $this->sanitizer = app(TokenSanitizer::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function createTestPage(array $attributes = []): PortalPage
    {
        $channel = core()->getCurrentChannel();

        return PortalPage::create(array_merge([
            'slug' => 'test-page-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
            'is_home_page' => 0,
            'sort_order' => 1,
        ], $attributes));
    }

    protected function createTestSection(int $pageId, array $attributes = []): PortalPageSection
    {
        return PortalPageSection::create(array_merge([
            'portal_page_id' => $pageId,
            'type' => 'hero',
            'name' => 'Hero Test',
            'sort_order' => 1,
            'status' => 1,
        ], $attributes));
    }

    // =========================================================================
    // A. Migration and Models
    // =========================================================================

    public function test_migration_adds_exactly_the_three_approved_columns(): void
    {
        $this->assertTrue(Schema::hasColumn('portal_pages', 'tokens'));
        $this->assertFalse(Schema::hasColumn('portal_pages', 'draft_tokens'));

        $this->assertTrue(Schema::hasColumn('portal_page_sections', 'tokens'));
        $this->assertTrue(Schema::hasColumn('portal_page_sections', 'draft_tokens'));

        $this->assertFalse(Schema::hasColumn('portal_page_translations', 'tokens'));
        $this->assertFalse(Schema::hasColumn('portal_page_translations', 'draft_tokens'));
        $this->assertFalse(Schema::hasColumn('portal_page_section_translations', 'tokens'));
        $this->assertFalse(Schema::hasColumn('portal_page_section_translations', 'draft_tokens'));
    }

    public function test_model_casts_return_arrays_for_stored_json_and_preserve_null(): void
    {
        $tokens = [
            '--brand-primary' => '#10b981',
            'dark' => [
                '--brand-primary' => '#059669',
            ],
        ];

        $page = $this->createTestPage(['tokens' => $tokens]);
        $page->refresh();

        $this->assertIsArray($page->tokens);
        $this->assertSame('#10b981', $page->tokens['--brand-primary']);
        $this->assertSame('#059669', $page->tokens['dark']['--brand-primary']);

        $page->tokens = null;
        $page->save();
        $page->refresh();
        $this->assertNull($page->tokens);

        $section = $this->createTestSection($page->id, [
            'tokens' => $tokens,
            'draft_tokens' => ['--brand-primary' => '#3b82f6'],
        ]);
        $section->refresh();

        $this->assertIsArray($section->tokens);
        $this->assertIsArray($section->draft_tokens);
        $this->assertSame('#3b82f6', $section->draft_tokens['--brand-primary']);

        $section->draft_tokens = [];
        $section->save();
        $section->refresh();
        $this->assertSame([], $section->draft_tokens);

        $section->draft_tokens = null;
        $section->tokens = null;
        $section->save();
        $section->refresh();
        $this->assertNull($section->tokens);
        $this->assertNull($section->draft_tokens);
    }

    public function test_tokens_are_not_placed_in_translations(): void
    {
        $pageTrans = new PortalPageTranslation;
        $this->assertNotContains('tokens', $pageTrans->getFillable());
        $this->assertNotContains('draft_tokens', $pageTrans->getFillable());

        $secTrans = new PortalPageSectionTranslation;
        $this->assertNotContains('tokens', $secTrans->getFillable());
        $this->assertNotContains('draft_tokens', $secTrans->getFillable());
    }

    // =========================================================================
    // B. Normalization
    // =========================================================================

    public function test_normalization_light_tokens(): void
    {
        $raw = ['--brand-primary' => '#10b981'];
        $result = $this->sanitizer->normalizeTokens($raw);

        $this->assertSame(['--brand-primary' => '#10b981'], $result);
    }

    public function test_normalization_nested_dark_tokens(): void
    {
        $raw = [
            '--brand-primary' => '#10b981',
            'dark' => [
                '--brand-primary' => '#059669',
            ],
        ];
        $result = $this->sanitizer->normalizeTokens($raw);

        $this->assertSame([
            '--brand-primary' => '#10b981',
            'dark' => [
                '--brand-primary' => '#059669',
            ],
        ], $result);
    }

    public function test_normalization_flat_dark_tokens_normalized_to_nested(): void
    {
        $raw = [
            '--brand-primary' => '#10b981',
            '--dark-brand-primary' => '#059669',
        ];
        $result = $this->sanitizer->normalizeTokens($raw);

        $this->assertSame([
            '--brand-primary' => '#10b981',
            'dark' => [
                '--brand-primary' => '#059669',
            ],
        ], $result);
    }

    public function test_normalization_collision_precedence_flat_overrides_nested(): void
    {
        $raw = [
            'dark' => [
                '--brand-primary' => '#nested-dark',
            ],
            '--dark-brand-primary' => '#flat-dark',
        ];
        $result = $this->sanitizer->normalizeTokens($raw);

        $this->assertSame([
            'dark' => [
                '--brand-primary' => '#flat-dark',
            ],
        ], $result);
    }

    public function test_normalization_strips_invalid_keys(): void
    {
        $raw = [
            '--valid-key' => '#10b981',
            'invalid_key' => '#000000',
            'dark' => [
                '--valid-dark' => '#059669',
                'invalid_dark_key' => '#ffffff',
            ],
        ];
        $result = $this->sanitizer->normalizeTokens($raw);

        $this->assertSame([
            '--valid-key' => '#10b981',
            'dark' => [
                '--valid-dark' => '#059669',
            ],
        ], $result);
    }

    public function test_normalization_sanitizes_invalid_values(): void
    {
        $raw = [
            '--safe-key' => 'red; background: blue',
            '--xss-key' => 'url(https://evil.com/leak)',
        ];
        $result = $this->sanitizer->normalizeTokens($raw);

        // Breakout semicolon stripped; url() blocked completely
        $this->assertSame(['--safe-key' => 'red background: blue'], $result);
    }

    public function test_normalization_strips_custom_css(): void
    {
        $raw = [
            '--brand-primary' => '#10b981',
            'custom_css' => 'body { background: red; }',
        ];
        $result = $this->sanitizer->normalizeTokens($raw);

        $this->assertArrayNotHasKey('custom_css', $result);
        $this->assertSame(['--brand-primary' => '#10b981'], $result);
    }

    public function test_normalization_preserves_zero_values(): void
    {
        $raw = [
            '--border-width' => 0,
            '--spacing' => '0',
            'dark' => [
                '--elevation' => 0,
            ],
        ];
        $result = $this->sanitizer->normalizeTokens($raw);

        $this->assertSame('0', $result['--border-width']);
        $this->assertSame('0', $result['--spacing']);
        $this->assertSame('0', $result['dark']['--elevation']);
    }

    public function test_normalization_distinguishes_empty_token_set(): void
    {
        $this->assertSame([], $this->sanitizer->normalizeTokens([]));
    }

    public function test_normalization_never_persists_derived_tokens_or_platform_defaults(): void
    {
        $raw = ['--brand-primary' => '#10b981'];
        $result = $this->sanitizer->normalizeTokens($raw);

        $this->assertArrayNotHasKey('--brand-primary-hover', $result);
        $this->assertArrayNotHasKey('--brand-primary-light', $result);
        $this->assertArrayNotHasKey('--bg-surface', $result);
        $this->assertSame(['--brand-primary' => '#10b981'], $result);
    }

    // =========================================================================
    // C. Page Lifecycle
    // =========================================================================

    public function test_page_create_and_update_with_tokens(): void
    {
        $channel = core()->getCurrentChannel();

        $page = $this->pageRepository->create([
            'slug' => 'page-tokens-create-'.uniqid(),
            'channel_id' => $channel->id,
            'status' => 1,
            'tokens' => [
                '--brand-primary' => '#10b981',
                '--dark-brand-primary' => '#059669',
            ],
            'en' => ['title' => 'Page Tokens Create'],
        ]);

        $this->assertIsArray($page->tokens);
        $this->assertSame('#10b981', $page->tokens['--brand-primary']);
        $this->assertSame('#059669', $page->tokens['dark']['--brand-primary']);
    }

    public function test_page_update_omitted_token_field_preserves_existing_values(): void
    {
        $page = $this->createTestPage([
            'tokens' => ['--brand-primary' => '#10b981'],
        ]);

        // Simulating controller update without 'tokens' in $data
        $updatedPage = $this->pageRepository->update([
            'slug' => $page->slug,
            'en' => ['title' => 'Updated Title'],
        ], $page->id);

        $this->assertSame(['--brand-primary' => '#10b981'], $updatedPage->refresh()->tokens);
    }

    public function test_page_update_explicit_empty_submission_clears_tokens(): void
    {
        $page = $this->createTestPage([
            'tokens' => ['--brand-primary' => '#10b981'],
        ]);

        // Update with normalized empty tokens
        $normalized = $this->sanitizer->normalizeTokens([]);
        $dataTokens = empty($normalized) ? null : $normalized;

        $updatedPage = $this->pageRepository->update([
            'tokens' => $dataTokens,
        ], $page->id);

        $this->assertNull($updatedPage->refresh()->tokens);
    }

    public function test_page_update_invalid_input_cannot_bypass_sanitization(): void
    {
        $raw = [
            '--bad' => 'red; background: blue',
            '--xss' => 'url(evil.com)',
            'invalid-key' => 'test',
        ];

        $normalized = $this->sanitizer->normalizeTokens($raw);
        $this->assertSame(['--bad' => 'red background: blue'], $normalized);

        $page = $this->createTestPage();
        $updatedPage = $this->pageRepository->update([
            'tokens' => $normalized,
        ], $page->id);

        $this->assertSame(['--bad' => 'red background: blue'], $updatedPage->refresh()->tokens);
    }

    public function test_page_update_fires_existing_event(): void
    {
        Event::fake(['portal.page.updated']);

        $page = $this->createTestPage();

        $this->pageRepository->update([
            'tokens' => ['--brand-primary' => '#10b981'],
        ], $page->id);

        Event::assertDispatched('portal.page.updated', function ($event, $payload) use ($page) {
            $dispatchedPage = is_object($payload) ? $payload : ($event instanceof PortalPage ? $event : null);

            return $dispatchedPage !== null && $dispatchedPage->id === $page->id;
        });
    }

    // =========================================================================
    // D. Section Lifecycle (12 Specific Cases)
    // =========================================================================

    public function test_section_lifecycle_1_no_published_tokens_and_no_draft(): void
    {
        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id);

        $this->assertNull($section->tokens);
        $this->assertNull($section->draft_tokens);
        $this->assertFalse($this->sectionRepository->hasDraft($section));

        $draftSections = $this->sectionRepository->getDraftSections($page->id, 'en');
        $this->assertNull($draftSections->firstWhere('id', $section->id)->tokens);
    }

    public function test_section_lifecycle_2_first_draft_creation(): void
    {
        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id);

        $updated = $this->sectionRepository->saveDraftTokens($section->id, ['--brand-primary' => '#10b981']);

        $this->assertNull($updated->tokens);
        $this->assertSame(['--brand-primary' => '#10b981'], $updated->draft_tokens);
        $this->assertTrue($this->sectionRepository->hasDraft($updated));

        $draftSections = $this->sectionRepository->getDraftSections($page->id, 'en');
        $this->assertSame(['--brand-primary' => '#10b981'], $draftSections->firstWhere('id', $section->id)->tokens);
    }

    public function test_section_lifecycle_3_updating_an_existing_draft(): void
    {
        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, [
            'draft_tokens' => ['--brand-primary' => '#10b981'],
        ]);

        $updated = $this->sectionRepository->saveDraftTokens($section->id, ['--brand-primary' => '#059669']);

        $this->assertNull($updated->tokens);
        $this->assertSame(['--brand-primary' => '#059669'], $updated->draft_tokens);
        $this->assertTrue($this->sectionRepository->hasDraft($updated));
    }

    public function test_section_lifecycle_4_published_tokens_without_pending_draft(): void
    {
        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, [
            'tokens' => ['--brand-primary' => '#059669'],
            'draft_tokens' => null,
        ]);

        $this->assertSame(['--brand-primary' => '#059669'], $section->tokens);
        $this->assertNull($section->draft_tokens);
        $this->assertFalse($this->sectionRepository->hasDraft($section));

        $draftSections = $this->sectionRepository->getDraftSections($page->id, 'en');
        $this->assertSame(['--brand-primary' => '#059669'], $draftSections->firstWhere('id', $section->id)->tokens);
    }

    public function test_section_lifecycle_5_published_tokens_with_replacement_draft(): void
    {
        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, [
            'tokens' => ['--brand-primary' => '#059669'],
        ]);

        $updated = $this->sectionRepository->saveDraftTokens($section->id, ['--brand-primary' => '#3b82f6']);

        $this->assertSame(['--brand-primary' => '#059669'], $updated->tokens);
        $this->assertSame(['--brand-primary' => '#3b82f6'], $updated->draft_tokens);
        $this->assertTrue($this->sectionRepository->hasDraft($updated));

        // Preview sees draft tokens
        $draftSections = $this->sectionRepository->getDraftSections($page->id, 'en');
        $this->assertSame(['--brand-primary' => '#3b82f6'], $draftSections->firstWhere('id', $section->id)->tokens);
    }

    public function test_section_lifecycle_6_explicit_empty_draft(): void
    {
        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, [
            'tokens' => ['--brand-primary' => '#059669'],
        ]);

        $updated = $this->sectionRepository->saveDraftTokens($section->id, []);

        $this->assertSame(['--brand-primary' => '#059669'], $updated->tokens);
        $this->assertSame([], $updated->draft_tokens);
        $this->assertTrue($this->sectionRepository->hasDraft($updated));

        // Preview sees empty token set (cleared overrides)
        $draftSections = $this->sectionRepository->getDraftSections($page->id, 'en');
        $this->assertSame([], $draftSections->firstWhere('id', $section->id)->tokens);
    }

    public function test_section_lifecycle_7_discard_replacement_draft(): void
    {
        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, [
            'tokens' => ['--brand-primary' => '#059669'],
            'draft_tokens' => ['--brand-primary' => '#3b82f6'],
        ]);

        $this->sectionRepository->discardDrafts($page->id);

        $section->refresh();
        $this->assertSame(['--brand-primary' => '#059669'], $section->tokens);
        $this->assertNull($section->draft_tokens);
        $this->assertFalse($this->sectionRepository->hasDraft($section));
    }

    public function test_section_lifecycle_8_discard_empty_draft(): void
    {
        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, [
            'tokens' => ['--brand-primary' => '#059669'],
            'draft_tokens' => [],
        ]);

        $this->sectionRepository->discardDrafts($page->id);

        $section->refresh();
        $this->assertSame(['--brand-primary' => '#059669'], $section->tokens);
        $this->assertNull($section->draft_tokens);
        $this->assertFalse($this->sectionRepository->hasDraft($section));
    }

    public function test_section_lifecycle_9_publish_without_token_changes(): void
    {
        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, [
            'tokens' => ['--brand-primary' => '#059669'],
            'draft_tokens' => null,
            'draft_status' => 1,
        ]);

        $this->sectionRepository->publish($page->id);

        $section->refresh();
        $this->assertSame(['--brand-primary' => '#059669'], $section->tokens);
        $this->assertNull($section->draft_tokens);
    }

    public function test_section_lifecycle_10_publish_non_empty_draft(): void
    {
        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, [
            'tokens' => ['--brand-primary' => '#059669'],
            'draft_tokens' => ['--brand-primary' => '#3b82f6'],
        ]);

        $this->sectionRepository->publish($page->id);

        $section->refresh();
        $this->assertSame(['--brand-primary' => '#3b82f6'], $section->tokens);
        $this->assertNull($section->draft_tokens);
        $this->assertFalse($this->sectionRepository->hasDraft($section));
    }

    public function test_section_lifecycle_11_publish_explicit_empty_draft(): void
    {
        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, [
            'tokens' => ['--brand-primary' => '#059669'],
            'draft_tokens' => [],
        ]);

        $this->sectionRepository->publish($page->id);

        $section->refresh();
        $this->assertNull($section->tokens);
        $this->assertNull($section->draft_tokens);
        $this->assertFalse($this->sectionRepository->hasDraft($section));
    }

    public function test_section_lifecycle_12_publish_unrelated_section_edits_preserves_published_tokens(): void
    {
        $page = $this->createTestPage();
        $sectionA = $this->createTestSection($page->id, [
            'tokens' => ['--brand-primary' => '#10b981'],
            'draft_tokens' => null,
        ]);
        $sectionB = $this->createTestSection($page->id, [
            'status' => 0,
            'draft_status' => 1,
            'draft_tokens' => null,
        ]);

        $this->sectionRepository->publish($page->id);

        $sectionA->refresh();
        $sectionB->refresh();

        $this->assertSame(['--brand-primary' => '#10b981'], $sectionA->tokens);
        $this->assertNull($sectionA->draft_tokens);
        $this->assertTrue((bool) $sectionB->status);
    }

    // =========================================================================
    // E. Cache and Events
    // =========================================================================

    public function test_draft_save_does_not_invalidate_public_response_cache(): void
    {
        Event::fake([
            'portal.page.published',
            'portal.page.updated',
        ]);

        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id);

        $this->sectionRepository->saveDraftTokens($section->id, ['--brand-primary' => '#10b981']);

        Event::assertNotDispatched('portal.page.published');
        Event::assertNotDispatched('portal.page.updated');
    }

    public function test_draft_discard_does_not_invalidate_public_response_cache(): void
    {
        Event::fake([
            'portal.page.published',
            'portal.page.updated',
        ]);

        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, [
            'draft_tokens' => ['--brand-primary' => '#10b981'],
        ]);

        $this->sectionRepository->discardDrafts($page->id);

        Event::assertNotDispatched('portal.page.published');
        Event::assertNotDispatched('portal.page.updated');
    }

    public function test_successful_publish_dispatches_portal_page_published(): void
    {
        Event::fake(['portal.page.published']);

        $page = $this->createTestPage();
        $this->createTestSection($page->id, [
            'draft_tokens' => ['--brand-primary' => '#10b981'],
        ]);

        $this->sectionRepository->publish($page->id);

        Event::assertDispatched('portal.page.published', function ($event, $payload) use ($page) {
            $dispatchedId = is_int($payload) ? $payload : (is_array($payload) ? ($payload[0] ?? null) : $event);

            return $dispatchedId === $page->id;
        });
    }

    public function test_response_cache_listener_receives_portal_page_published(): void
    {
        if (! class_exists('Spatie\ResponseCache\ResponseCache')) {
            eval('namespace Spatie\ResponseCache; class ResponseCache { public function clear(): void {} }');
        }

        $mockCache = Mockery::mock('Spatie\ResponseCache\ResponseCache');
        $mockCache->shouldReceive('clear')->once();

        app()->instance('Spatie\ResponseCache\ResponseCache', $mockCache);

        Event::dispatch('portal.page.published', [42]);

        $this->addToAssertionCount(1);
    }

    public function test_existing_cache_listeners_remain_intact(): void
    {
        if (! class_exists('Spatie\ResponseCache\ResponseCache')) {
            eval('namespace Spatie\ResponseCache; class ResponseCache { public function clear(): void {} }');
        }

        $mockCache = Mockery::mock('Spatie\ResponseCache\ResponseCache');
        $mockCache->shouldReceive('clear')->times(6);

        app()->instance('Spatie\ResponseCache\ResponseCache', $mockCache);

        Event::dispatch('portal.page.created', [1]);
        Event::dispatch('portal.page.updated', [1]);
        Event::dispatch('portal.page.deleted', [1]);
        Event::dispatch('portal.theme.activated', [1, 'portal']);
        Event::dispatch('portal.menu.updated', [1]);
        Event::dispatch('portal.tokens.updated', [1]);

        $this->addToAssertionCount(6);
    }

    public function test_page_controller_update_saves_and_normalizes_tokens(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $channel = core()->getCurrentChannel();
        $page = $this->createTestPage();

        request()->merge([
            'slug' => $page->slug,
            'channel_id' => $channel->id,
            'status' => '1',
            'title' => 'Page Title',
            'locale' => 'en',
            'tokens' => [
                '--brand-primary' => '#10b981',
                '--dark-brand-primary' => '#059669',
            ],
        ]);

        $controller = app(PageController::class);
        $response = $controller->update($page->id);

        $this->assertTrue($response->isRedirection() || $response instanceof JsonResponse);

        $page->refresh();
        $this->assertIsArray($page->tokens);
        $this->assertSame('#10b981', $page->tokens['--brand-primary']);
        $this->assertSame('#059669', $page->tokens['dark']['--brand-primary']);
    }

    public function test_page_customize_controller_save_draft_tokens_endpoint(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id);

        request()->merge([
            'tokens' => [
                '--brand-primary' => '#3b82f6',
            ],
        ]);

        $controller = app(PageCustomizeController::class);
        $response = $controller->saveDraftTokens($page->id, $section->id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertTrue($data['has_draft']);
        $this->assertSame(['--brand-primary' => '#3b82f6'], $data['tokens']);

        $section->refresh();
        $this->assertSame(['--brand-primary' => '#3b82f6'], $section->draft_tokens);
    }

    public function test_page_customize_controller_fields_returns_tokens(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, [
            'tokens' => ['--brand-primary' => '#10b981'],
            'draft_tokens' => ['--brand-primary' => '#3b82f6'],
        ]);

        $controller = app(PageCustomizeController::class);
        $response = $controller->fields($page->id, $section->id);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertArrayHasKey('tokens', $data);
        $this->assertSame(['--brand-primary' => '#3b82f6'], $data['tokens']);
    }

    // =========================================================================
    // F. Input Validation & Error Handling (Phase 1H.4.1.2)
    // =========================================================================

    public function test_page_update_with_valid_json_string_normalizes_and_saves(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $channel = core()->getCurrentChannel();
        $page = $this->createTestPage(['tokens' => ['--brand-primary' => '#10b981']]);

        request()->merge([
            'slug' => $page->slug,
            'channel_id' => $channel->id,
            'status' => '1',
            'title' => 'Page Title',
            'locale' => 'en',
            'tokens' => '{"--brand-primary": "#3b82f6", "dark": {"--brand-primary": "#1d4ed8"}}',
        ]);

        $controller = app(PageController::class);
        $response = $controller->update($page->id);

        $this->assertTrue($response->isRedirection() || $response instanceof JsonResponse);

        $page->refresh();
        $this->assertIsArray($page->tokens);
        $this->assertSame('#3b82f6', $page->tokens['--brand-primary']);
        $this->assertSame('#1d4ed8', $page->tokens['dark']['--brand-primary']);
    }

    public function test_page_update_with_explicit_empty_json_object_clears_tokens(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $channel = core()->getCurrentChannel();
        $page = $this->createTestPage(['tokens' => ['--brand-primary' => '#10b981']]);

        request()->merge([
            'slug' => $page->slug,
            'channel_id' => $channel->id,
            'status' => '1',
            'title' => 'Page Title',
            'locale' => 'en',
            'tokens' => '{}',
        ]);

        $controller = app(PageController::class);
        $response = $controller->update($page->id);

        $this->assertTrue($response->isRedirection() || $response instanceof JsonResponse);

        $page->refresh();
        $this->assertNull($page->tokens);
    }

    public function test_page_update_rejects_malformed_json_and_preserves_existing_tokens(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $channel = core()->getCurrentChannel();
        $page = $this->createTestPage(['tokens' => ['--brand-primary' => '#10b981']]);

        request()->merge([
            'slug' => $page->slug,
            'channel_id' => $channel->id,
            'status' => '1',
            'title' => 'Page Title',
            'locale' => 'en',
            'tokens' => '{malformed-json-without-closing',
        ]);

        $controller = app(PageController::class);

        try {
            $controller->update($page->id);
            $this->fail('Expected ValidationException was not thrown for malformed JSON.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('tokens', $e->errors());
        }

        $page->refresh();
        $this->assertSame(['--brand-primary' => '#10b981'], $page->tokens);
    }

    public function test_page_update_rejects_json_scalar_values_and_preserves_existing_tokens(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $channel = core()->getCurrentChannel();
        $page = $this->createTestPage(['tokens' => ['--brand-primary' => '#10b981']]);

        $scalarInputs = ['12345', 'true', '"just-a-scalar-string"'];

        foreach ($scalarInputs as $input) {
            request()->merge([
                'slug' => $page->slug,
                'channel_id' => $channel->id,
                'status' => '1',
                'title' => 'Page Title',
                'locale' => 'en',
                'tokens' => $input,
            ]);

            $controller = app(PageController::class);

            try {
                $controller->update($page->id);
                $this->fail("Expected ValidationException was not thrown for JSON scalar: {$input}");
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('tokens', $e->errors());
            }

            $page->refresh();
            $this->assertSame(['--brand-primary' => '#10b981'], $page->tokens);
        }
    }

    public function test_page_update_rejects_invalid_php_input_types_and_preserves_existing_tokens(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $channel = core()->getCurrentChannel();
        $page = $this->createTestPage(['tokens' => ['--brand-primary' => '#10b981']]);

        $invalidTypes = [12345, true, false, 3.14];

        foreach ($invalidTypes as $input) {
            request()->merge([
                'slug' => $page->slug,
                'channel_id' => $channel->id,
                'status' => '1',
                'title' => 'Page Title',
                'locale' => 'en',
                'tokens' => $input,
            ]);

            $controller = app(PageController::class);

            try {
                $controller->update($page->id);
                $this->fail('Expected ValidationException was not thrown for non-array/non-string PHP input.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('tokens', $e->errors());
            }

            $page->refresh();
            $this->assertSame(['--brand-primary' => '#10b981'], $page->tokens);
        }
    }

    public function test_portal_page_mutator_does_not_clear_tokens_on_malformed_json_string(): void
    {
        $page = $this->createTestPage(['tokens' => ['--brand-primary' => '#10b981']]);
        $this->assertSame(['--brand-primary' => '#10b981'], $page->tokens);

        // Attempt direct assignment of malformed string to model mutator
        $page->tokens = '{invalid-json';
        $page->save();

        $page->refresh();
        $this->assertSame(['--brand-primary' => '#10b981'], $page->tokens);
    }

    public function test_section_customize_endpoint_rejects_malformed_json_tokens(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, ['draft_tokens' => ['--brand-primary' => '#original']]);

        request()->merge([
            'tokens' => '{invalid-json',
        ]);

        $controller = app(PageCustomizeController::class);

        try {
            $controller->saveDraftTokens($page->id, $section->id);
            $this->fail('Expected ValidationException was not thrown for malformed JSON section tokens.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('tokens', $e->errors());
        }

        $section->refresh();
        $this->assertSame(['--brand-primary' => '#original'], $section->draft_tokens);
    }

    public function test_section_customize_endpoint_rejects_scalar_tokens(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id, ['draft_tokens' => ['--brand-primary' => '#original']]);

        request()->merge([
            'tokens' => 12345,
        ]);

        $controller = app(PageCustomizeController::class);

        try {
            $controller->saveDraftTokens($page->id, $section->id);
            $this->fail('Expected ValidationException was not thrown for scalar section tokens.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('tokens', $e->errors());
        }

        $section->refresh();
        $this->assertSame(['--brand-primary' => '#original'], $section->draft_tokens);
    }

    // =========================================================================
    // G. Negative Authorization Tests (Phase 1H.4.1.2)
    // =========================================================================

    public function test_save_draft_tokens_forbidden_without_permission(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = false;
        }

        auth()->guard('admin')->logout();

        $page = $this->createTestPage();
        $section = $this->createTestSection($page->id);

        request()->merge([
            'tokens' => ['--brand-primary' => '#3b82f6'],
        ]);

        $controller = app(PageCustomizeController::class);

        try {
            $controller->saveDraftTokens($page->id, $section->id);
            $this->fail('Expected HttpException 403 was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        } finally {
            if (property_exists(bouncer(), 'allowsAll')) {
                bouncer()::$allowsAll = true;
            }
        }
    }

    public function test_page_update_tokens_forbidden_without_permission(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = false;
        }

        auth()->guard('admin')->logout();

        $page = $this->createTestPage();

        request()->merge([
            'slug' => $page->slug,
            'channel_id' => 1,
            'status' => '1',
            'title' => 'Page Title',
            'locale' => 'en',
            'tokens' => ['--brand-primary' => '#3b82f6'],
        ]);

        $controller = app(PageController::class);

        try {
            $controller->update($page->id);
            $this->fail('Expected HttpException 403 was not thrown.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        } finally {
            if (property_exists(bouncer(), 'allowsAll')) {
                bouncer()::$allowsAll = true;
            }
        }
    }

    // =========================================================================
    // H. Negative Ownership / IDOR Tests (Phase 1H.4.1.2)
    // =========================================================================

    public function test_save_draft_tokens_returns_404_on_page_section_ownership_mismatch(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $pageA = $this->createTestPage();
        $pageB = $this->createTestPage();

        $sectionB = $this->createTestSection($pageB->id, [
            'tokens' => ['--brand-primary' => '#10b981'],
            'draft_tokens' => null,
        ]);

        request()->merge([
            'tokens' => ['--brand-primary' => '#ef4444'],
        ]);

        $controller = app(PageCustomizeController::class);

        try {
            // Attempt to update Section B through Page A's route
            $controller->saveDraftTokens($pageA->id, $sectionB->id);
            $this->fail('Expected HttpException 404 was not thrown on ownership mismatch.');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }

        // Verify Section B was NOT modified
        $sectionB->refresh();
        $this->assertSame(['--brand-primary' => '#10b981'], $sectionB->tokens);
        $this->assertNull($sectionB->draft_tokens);
    }

    public function test_save_draft_returns_404_on_page_section_ownership_mismatch(): void
    {
        if (property_exists(bouncer(), 'allowsAll')) {
            bouncer()::$allowsAll = true;
        }

        $pageA = $this->createTestPage();
        $pageB = $this->createTestPage();

        $sectionB = $this->createTestSection($pageB->id);

        request()->merge([
            'options' => ['heading' => 'Hacked Heading'],
            'tokens' => ['--brand-primary' => '#ef4444'],
        ]);

        $controller = app(PageCustomizeController::class);

        try {
            // Attempt to save draft for Section B through Page A's route
            $controller->saveDraft($pageA->id, $sectionB->id);
            $this->fail('Expected HttpException 404 was not thrown on ownership mismatch in saveDraft.');
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
    }
}
