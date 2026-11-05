<?php

namespace Platform\Portal\Tests\Unit\Services;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Exceptions;
use Mockery;
use Mockery\MockInterface;
use Platform\Portal\Repositories\PortalLayoutSettingRepository;
use Platform\Portal\Services\ThemeTokenService;
use Platform\Portal\Services\TokenCompilerService;
use Tests\TestCase;

class ThemeTokenServiceTest extends TestCase
{
    protected TokenCompilerService $tokenCompiler;

    /** @var PortalLayoutSettingRepository&MockInterface */
    protected $layoutSettingRepository;

    protected ThemeTokenService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenCompiler = app(TokenCompilerService::class);
        $this->layoutSettingRepository = Mockery::mock(PortalLayoutSettingRepository::class);

        $this->service = new ThemeTokenService(
            $this->tokenCompiler,
            $this->layoutSettingRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test 1: Apply preset successfully retrieves preset, formats dark tokens, sanitizes, and persists.
     */
    public function test_apply_preset_successfully_sanitizes_and_persists_tokens_with_dark_mode(): void
    {
        $channelId = 1;
        $themeCode = 'portal';
        $presetCode = 'default';

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->with(
                'theme_tokens',
                Mockery::on(function (array $tokens) {
                    // Must contain primary brand color
                    $hasBrandPrimary = isset($tokens['--brand-primary']) && $tokens['--brand-primary'] === '#1c66f6';
                    // Must contain dark mode token with --dark- prefix
                    $hasDarkBgApp = isset($tokens['--dark-bg-app']) && $tokens['--dark-bg-app'] === '#080b11';

                    return $hasBrandPrimary && $hasDarkBgApp;
                }),
                $channelId,
                $themeCode
            )
            ->andReturn(true);

        $result = $this->service->applyPreset($channelId, $themeCode, $presetCode);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('--brand-primary', $result);
        $this->assertArrayHasKey('--dark-bg-app', $result);
        $this->assertSame('#1c66f6', $result['--brand-primary']);
        $this->assertSame('#080b11', $result['--dark-bg-app']);
    }

    /**
     * Test 2: Apply preset returns null and does not persist when preset does not exist.
     */
    public function test_apply_preset_returns_null_and_does_not_persist_when_preset_not_found(): void
    {
        $channelId = 1;
        $themeCode = 'portal';
        $presetCode = 'non_existent_preset_xyz';

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->never();

        $result = $this->service->applyPreset($channelId, $themeCode, $presetCode);

        $this->assertNull($result);
    }

    /**
     * Test 3: Import DTCG successfully parses, sanitizes, and persists tokens.
     */
    public function test_import_dtcg_successfully_imports_and_persists_tokens(): void
    {
        $channelId = 2;
        $themeCode = 'midnight';

        $dtcgPayload = [
            '$name' => 'Test Theme Tokens',
            'color' => [
                'brand-primary' => [
                    '$value' => '#ff5500',
                    '$type' => 'color',
                ],
                'brand-accent' => [
                    '$value' => '#00cc88',
                    '$type' => 'color',
                ],
                'dark-bg-app' => [
                    '$value' => '#111827',
                    '$type' => 'color',
                ],
            ],
            'dimension' => [
                'radius-base' => [
                    '$value' => '12px',
                    '$type' => 'dimension',
                ],
            ],
        ];

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->with(
                'theme_tokens',
                Mockery::on(function (array $tokens) {
                    return ($tokens['--brand-primary'] ?? null) === '#ff5500'
                        && ($tokens['--brand-accent'] ?? null) === '#00cc88'
                        && ($tokens['--dark-bg-app'] ?? null) === '#111827'
                        && ($tokens['--radius-base'] ?? null) === '12px';
                }),
                $channelId,
                $themeCode
            )
            ->andReturn(true);

        $result = $this->service->importDtcg($channelId, $themeCode, $dtcgPayload);

        $this->assertIsArray($result);
        $this->assertSame('#ff5500', $result['--brand-primary']);
        $this->assertSame('#00cc88', $result['--brand-accent']);
        $this->assertSame('#111827', $result['--dark-bg-app']);
        $this->assertSame('12px', $result['--radius-base']);
    }

    /**
     * Test 4: Import DTCG returns empty array and does not persist when payload produces no valid tokens.
     */
    public function test_import_dtcg_returns_empty_array_and_does_not_persist_when_payload_has_no_valid_tokens(): void
    {
        $channelId = 1;
        $themeCode = 'portal';
        $invalidPayload = [
            'foo' => 'bar',
            'empty' => [],
        ];

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->never();

        $result = $this->service->importDtcg($channelId, $themeCode, $invalidPayload);

        $this->assertSame([], $result);
    }

    /**
     * Test 5: Customize tokens sanitizes token keys and values, rejecting invalid CSS identifiers.
     */
    public function test_customize_tokens_sanitizes_and_persists_custom_tokens(): void
    {
        $channelId = 3;
        $themeCode = 'velvet';

        $rawTokens = [
            '--brand-primary' => '#3b82f6',
            '--dark-brand-primary' => '#1e40af',
            'invalid-key-no-dashes' => '#ffffff',
            '--bad;rule' => '#000000',
            '--bad-url' => 'url(https://evil.com/leak)',
        ];

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->with(
                'theme_tokens',
                Mockery::on(function (array $tokens) {
                    return isset($tokens['--brand-primary'])
                        && isset($tokens['--dark-brand-primary'])
                        && ! isset($tokens['invalid-key-no-dashes'])
                        && ! isset($tokens['--bad;rule'])
                        && ! isset($tokens['--bad-url']);
                }),
                $channelId,
                $themeCode
            )
            ->andReturn(true);

        $result = $this->service->customizeTokens($channelId, $themeCode, $rawTokens);

        $this->assertSame('#3b82f6', $result['--brand-primary']);
        $this->assertSame('#1e40af', $result['--dark-brand-primary']);
        $this->assertArrayNotHasKey('invalid-key-no-dashes', $result);
        $this->assertArrayNotHasKey('--bad;rule', $result);
        $this->assertArrayNotHasKey('--bad-url', $result);
    }

    /**
     * Test 6: Customize tokens sanitizes custom CSS and attaches it when filled.
     */
    public function test_customize_tokens_sanitizes_and_stores_custom_css(): void
    {
        $channelId = 1;
        $themeCode = 'portal';

        $rawTokens = [
            '--brand-primary' => '#0ea5e9',
        ];

        $maliciousCss = ".hero { color: red; } <script>alert('xss')</script>";

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->with(
                'theme_tokens',
                Mockery::on(function (array $tokens) {
                    return ($tokens['--brand-primary'] ?? null) === '#0ea5e9'
                        && ($tokens['custom_css'] ?? null) === '.hero { color: red; }';
                }),
                $channelId,
                $themeCode
            )
            ->andReturn(true);

        $result = $this->service->customizeTokens($channelId, $themeCode, $rawTokens, $maliciousCss);

        $this->assertSame('#0ea5e9', $result['--brand-primary']);
        $this->assertSame('.hero { color: red; }', $result['custom_css']);
    }

    /**
     * Test 7: Customize tokens does not set custom_css key when custom CSS is empty or whitespace.
     */
    public function test_customize_tokens_does_not_set_custom_css_when_empty_or_whitespace(): void
    {
        $channelId = 1;
        $themeCode = 'portal';

        $rawTokens = [
            '--brand-primary' => '#0ea5e9',
        ];

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->with(
                'theme_tokens',
                Mockery::on(function (array $tokens) {
                    return ! array_key_exists('custom_css', $tokens);
                }),
                $channelId,
                $themeCode
            )
            ->andReturn(true);

        $result = $this->service->customizeTokens($channelId, $themeCode, $rawTokens, '   ');

        $this->assertArrayNotHasKey('custom_css', $result);
    }

    /**
     * Test 8: Reset tokens persists empty array to repository and returns empty array.
     */
    public function test_reset_tokens_persists_empty_array_and_returns_empty_array(): void
    {
        $channelId = 4;
        $themeCode = 'minimalist';

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->with('theme_tokens', [], $channelId, $themeCode)
            ->andReturn(true);

        $result = $this->service->resetTokens($channelId, $themeCode);

        $this->assertSame([], $result);
    }

    /**
     * Test 9: Get tokens retrieves stored settings from repository.
     */
    public function test_get_tokens_retrieves_from_repository(): void
    {
        $channelId = 1;
        $themeCode = 'portal';
        $storedTokens = [
            '--brand-primary' => '#6366f1',
            '--radius-base' => '12px',
        ];

        $this->layoutSettingRepository
            ->shouldReceive('getSettings')
            ->once()
            ->with('theme_tokens', $channelId, $themeCode)
            ->andReturn($storedTokens);

        $result = $this->service->getTokens($channelId, $themeCode);

        $this->assertSame($storedTokens, $result);
    }

    /**
     * Test 10: Database exceptions during persistence are not suppressed.
     */
    public function test_database_exceptions_are_not_suppressed(): void
    {
        $channelId = 1;
        $themeCode = 'portal';

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->andThrow(new \RuntimeException('Database connection lost'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database connection lost');

        $this->service->customizeTokens($channelId, $themeCode, ['--brand-primary' => '#123456']);
    }

    /**
     * Test 11: Apply preset success dispatches exactly one portal.tokens.updated event with correct payload.
     */
    public function test_apply_preset_success_dispatches_event_with_correct_payload(): void
    {
        Event::fake();

        $channelId = 1;
        $themeCode = 'portal';
        $presetCode = 'default';

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->andReturn(true);

        $this->service->applyPreset($channelId, $themeCode, $presetCode);

        Event::assertDispatched('portal.tokens.updated', function ($event, $payload) use ($channelId, $themeCode) {
            $data = is_array($payload) && isset($payload[0]) && is_array($payload[0]) ? $payload[0] : $payload;

            return $event === 'portal.tokens.updated'
                && ($data['channel_id'] ?? null) === $channelId
                && ($data['theme_code'] ?? null) === $themeCode
                && ($data['operation'] ?? null) === 'apply_preset'
                && ! isset($data['tokens'])
                && ! isset($data['custom_css']);
        });

        Event::assertDispatchedTimes('portal.tokens.updated', 1);
    }

    /**
     * Test 12: Missing preset does not dispatch event.
     */
    public function test_missing_preset_does_not_dispatch_event(): void
    {
        Event::fake();

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->never();

        $this->service->applyPreset(1, 'portal', 'non_existent_preset');

        Event::assertNotDispatched('portal.tokens.updated');
    }

    /**
     * Test 13: Successful DTCG import dispatches exactly one event.
     */
    public function test_successful_dtcg_import_dispatches_event(): void
    {
        Event::fake();

        $channelId = 2;
        $themeCode = 'midnight';

        $dtcgPayload = [
            'color' => [
                'brand-primary' => ['$value' => '#ff5500'],
            ],
        ];

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->andReturn(true);

        $this->service->importDtcg($channelId, $themeCode, $dtcgPayload);

        Event::assertDispatched('portal.tokens.updated', function ($event, $payload) use ($channelId, $themeCode) {
            $data = is_array($payload) && isset($payload[0]) && is_array($payload[0]) ? $payload[0] : $payload;

            return $event === 'portal.tokens.updated'
                && ($data['channel_id'] ?? null) === $channelId
                && ($data['theme_code'] ?? null) === $themeCode
                && ($data['operation'] ?? null) === 'import_dtcg'
                && ! isset($data['tokens'])
                && ! isset($data['custom_css']);
        });

        Event::assertDispatchedTimes('portal.tokens.updated', 1);
    }

    /**
     * Test 14: Empty DTCG import does not dispatch event.
     */
    public function test_empty_dtcg_import_does_not_dispatch_event(): void
    {
        Event::fake();

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->never();

        $this->service->importDtcg(1, 'portal', ['invalid' => 'payload']);

        Event::assertNotDispatched('portal.tokens.updated');
    }

    /**
     * Test 15: Customize tokens success dispatches exactly one event.
     */
    public function test_customize_tokens_success_dispatches_event(): void
    {
        Event::fake();

        $channelId = 3;
        $themeCode = 'velvet';

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->andReturn(true);

        $this->service->customizeTokens($channelId, $themeCode, ['--brand-primary' => '#123456'], '.btn { color: red; }');

        Event::assertDispatched('portal.tokens.updated', function ($event, $payload) use ($channelId, $themeCode) {
            $data = is_array($payload) && isset($payload[0]) && is_array($payload[0]) ? $payload[0] : $payload;

            return $event === 'portal.tokens.updated'
                && ($data['channel_id'] ?? null) === $channelId
                && ($data['theme_code'] ?? null) === $themeCode
                && ($data['operation'] ?? null) === 'customize_tokens'
                && ! isset($data['tokens'])
                && ! isset($data['custom_css']);
        });

        Event::assertDispatchedTimes('portal.tokens.updated', 1);
    }

    /**
     * Test 16: Reset tokens success dispatches exactly one event.
     */
    public function test_reset_tokens_success_dispatches_event(): void
    {
        Event::fake();

        $channelId = 4;
        $themeCode = 'minimalist';

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->andReturn(true);

        $this->service->resetTokens($channelId, $themeCode);

        Event::assertDispatched('portal.tokens.updated', function ($event, $payload) use ($channelId, $themeCode) {
            $data = is_array($payload) && isset($payload[0]) && is_array($payload[0]) ? $payload[0] : $payload;

            return $event === 'portal.tokens.updated'
                && ($data['channel_id'] ?? null) === $channelId
                && ($data['theme_code'] ?? null) === $themeCode
                && ($data['operation'] ?? null) === 'reset_tokens'
                && ! isset($data['tokens'])
                && ! isset($data['custom_css']);
        });

        Event::assertDispatchedTimes('portal.tokens.updated', 1);
    }

    /**
     * Test 17: Repository exception prevents event dispatch.
     */
    public function test_repository_exception_prevents_event_dispatch(): void
    {
        Event::fake();

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->andThrow(new \RuntimeException('DB connection failed'));

        $this->expectException(\RuntimeException::class);

        try {
            $this->service->customizeTokens(1, 'portal', ['--brand-primary' => '#123456']);
        } finally {
            Event::assertNotDispatched('portal.tokens.updated');
        }
    }

    /**
     * Test 18: Event listener exception is reported without failing service result.
     */
    public function test_event_listener_exception_is_reported_without_failing_service_result(): void
    {
        Exceptions::fake();

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->andReturn(true);

        Event::listen('portal.tokens.updated', function (array $payload) {
            throw new \RuntimeException('Faulty listener failed');
        });

        $result = $this->service->customizeTokens(1, 'portal', ['--brand-primary' => '#123456']);

        // 1. Result succeeds and is returned
        $this->assertSame('#123456', $result['--brand-primary']);

        // 2. Exception is reported to exception handler
        Exceptions::assertReported(function (\RuntimeException $e) {
            return $e->getMessage() === 'Faulty listener failed';
        });
    }

    /**
     * Test 19: Real Laravel dispatcher delivers a single associative array to normal named listeners.
     */
    public function test_real_dispatcher_delivers_single_associative_array_to_named_listener(): void
    {
        $channelId = 1;
        $themeCode = 'portal';

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->andReturn(true);

        $capturedPayload = null;
        $callCount = 0;

        Event::listen('portal.tokens.updated', function (array $payload) use (&$capturedPayload, &$callCount) {
            $callCount++;
            $capturedPayload = $payload;
        });

        $result = $this->service->customizeTokens($channelId, $themeCode, ['--brand-primary' => '#123456']);

        $this->assertSame(1, $callCount, 'Listener must be called exactly once.');
        $this->assertIsArray($capturedPayload);
        $this->assertSame($channelId, $capturedPayload['channel_id']);
        $this->assertSame($themeCode, $capturedPayload['theme_code']);
        $this->assertSame('customize_tokens', $capturedPayload['operation']);
        $this->assertArrayNotHasKey('tokens', $capturedPayload);
        $this->assertArrayNotHasKey('custom_css', $capturedPayload);
    }

    /**
     * Test 20: Wildcard listener receives event name and outer wrapped payload array.
     */
    public function test_wildcard_listener_receives_event_name_and_wrapped_payload(): void
    {
        $channelId = 5;
        $themeCode = 'dark_mode';

        $this->layoutSettingRepository
            ->shouldReceive('saveSettings')
            ->once()
            ->andReturn(true);

        $capturedEvent = null;
        $capturedArgs = null;
        $wildcardCallCount = 0;

        Event::listen('portal.tokens.*', function ($event, $args) use (&$capturedEvent, &$capturedArgs, &$wildcardCallCount) {
            $wildcardCallCount++;
            $capturedEvent = $event;
            $capturedArgs = $args;
        });

        $this->service->resetTokens($channelId, $themeCode);

        $this->assertSame(1, $wildcardCallCount);
        $this->assertSame('portal.tokens.updated', $capturedEvent);
        $this->assertIsArray($capturedArgs);
        // Wildcard listeners receive the outer wrapped array: [0 => ['channel_id' => ..., ...]]
        $this->assertArrayHasKey(0, $capturedArgs);
        $this->assertSame($channelId, $capturedArgs[0]['channel_id']);
        $this->assertSame($themeCode, $capturedArgs[0]['theme_code']);
        $this->assertSame('reset_tokens', $capturedArgs[0]['operation']);
    }
}
