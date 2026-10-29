<?php

namespace Platform\Portal\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Theme
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $themes = themes();

        $channel = core()->getCurrentChannel();

        if (
            $channel
            && $channelThemeCode = $channel->theme
        ) {
            $themes->exists($channelThemeCode)
                ? $themes->set($channelThemeCode)
                : $themes->set(config('themes.shop-default', 'default'));
        } else {
            $themes->set(config('themes.shop-default', 'default'));
        }

        return $next($request);
    }
}
