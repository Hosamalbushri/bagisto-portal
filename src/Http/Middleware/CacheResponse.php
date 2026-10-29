<?php

namespace Platform\Portal\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CacheResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        return $next($request);
    }
}
