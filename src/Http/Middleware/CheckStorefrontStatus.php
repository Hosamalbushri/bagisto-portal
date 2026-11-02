<?php

namespace Platform\Portal\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Platform\Portal\Helpers\PortalStorefront;
use Webkul\Core\Http\Middleware\SecureHeaders;

class CheckStorefrontStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Allow visual customizer preview in admin iframe only for authenticated admins or signed preview URLs
        $isAdmin = auth()->guard('admin')->check();
        $isFramedPreview = (bool) $request->attributes->get(SecureHeaders::FRAMABLE);
        $isPreviewRoute = $request->routeIs('shop.appearance.preview')
            || $request->routeIs('portal.appearance.preview')
            || $request->routeIs('portal.page.preview');

        if (($isFramedPreview || $isPreviewRoute || $request->boolean('preview')) && ($isAdmin || $request->hasValidSignature())) {
            return $next($request);
        }

        if (! PortalStorefront::isEnabled()) {
            if (auth()->guard('admin')->check()) {
                return redirect()->route('admin.dashboard.index');
            }

            return redirect()->route('admin.session.create');
        }

        return $next($request);
    }
}
