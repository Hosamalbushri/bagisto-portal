<?php

namespace Platform\Portal\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\ResponseCache\ResponseCache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$args): mixed
    {
        // 1. Delegate to Bagisto's Shop FPC middleware if active
        if (class_exists(\Webkul\Shop\Http\Middleware\CacheResponse::class) && app()->bound(ResponseCache::class)) {
            return app(\Webkul\Shop\Http\Middleware\CacheResponse::class)->handle($request, $next, ...$args);
        }

        /** @var Response $response */
        $response = $next($request);

        // 2. Set browser & CDN HTTP cache headers for cacheable public GET requests
        if (
            $request->isMethod('GET')
            && ! auth()->check()
            && ! auth()->guard('admin')->check()
            && ! $request->has('preview')
            && ! $request->routeIs('*.preview')
            && $response->isSuccessful()
        ) {
            $ttl = (int) config('portal.cache.response_ttl', 300);

            $response->headers->set('Cache-Control', "public, max-age={$ttl}, stale-while-revalidate=60");
        }

        return $response;
    }
}
