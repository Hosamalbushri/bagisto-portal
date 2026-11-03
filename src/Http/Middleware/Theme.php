<?php

namespace Platform\Portal\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Platform\Portal\Services\ThemeManager;

class Theme
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $themes = themes();

        $channel = core()->getCurrentChannel();

        $themeCode = function_exists('portal_active_theme')
            ? portal_active_theme($channel?->id)
            : 'portal';

        if ($themes->exists($themeCode)) {
            $themes->set($themeCode);
        } else {
            $themes->set('portal');
        }

        // Prepend active theme view paths to the portal namespace for automatic view inheritance & overrides
        if (method_exists($themes, 'current') && ($activeTheme = $themes->current())) {
            // Graceful fallback for uncompiled child theme Vite build directory
            $themeManager = app(ThemeManager::class);
            $manifest = $themeManager->find($activeTheme->code ?? null);

            $hasBuiltAssets = $manifest
                ? $manifest->hasBuiltAssets()
                : (! empty($activeTheme->vite['build_directory']) && file_exists(public_path($activeTheme->vite['build_directory'].'/manifest.json')));

            if (! $hasBuiltAssets) {
                $parent = method_exists($activeTheme, 'getParent') ? $activeTheme->getParent() : null;
                $visited = array_filter([$activeTheme->code ?? null]);

                while ($parent && ! in_array($parent->code, $visited, true)) {
                    $visited[] = $parent->code;

                    if (! empty($parent->vite['build_directory']) && file_exists(public_path($parent->vite['build_directory'].'/manifest.json'))) {
                        $activeTheme->vite = $parent->vite;
                        break;
                    }
                    $parent = method_exists($parent, 'getParent') ? $parent->getParent() : null;
                }

                // Fallback to portal package viter if still unresolved
                if (! file_exists(public_path(($activeTheme->vite['build_directory'] ?? '').'/manifest.json'))) {
                    $portalVite = config('bagisto-vite.viters.portal') ?? config('portal.vite');
                    if (! empty($portalVite) && file_exists(public_path($portalVite['build_directory'].'/manifest.json'))) {
                        $activeTheme->vite = $portalVite;
                    }
                }
            }

            $viewPaths = method_exists($activeTheme, 'getViewPaths') ? $activeTheme->getViewPaths() : [];

            if (empty($viewPaths) && ! empty($activeTheme->viewsPath)) {
                $rawPath = $activeTheme->viewsPath;
                $viewPaths = [str_starts_with($rawPath, '/') ? base_path(substr($rawPath, 1)) : base_path($rawPath)];
            }

            foreach (array_reverse($viewPaths) as $path) {
                if (is_dir($path)) {
                    view()->prependNamespace('portal', $path);
                }
            }
        }

        return $next($request);
    }
}
