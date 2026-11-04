<?php

namespace Platform\Portal\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ManifestController extends Controller
{
    /**
     * Generate dynamic PWA Web App Manifest for the portal.
     */
    public function index(): JsonResponse
    {
        $channel = core()->getCurrentChannel();

        $name = $channel?->name ?? config('app.name', 'Bagisto Portal');
        $desc = $channel?->description ?? config('app.description', 'Modern portal and content engine.');
        $favicon = $channel?->favicon_url ?? portal_asset('images/favicon.ico', asset('favicon.ico'));
        $logo = $channel?->logo_url ?? portal_asset('images/logo.svg', asset('images/logo.svg'));

        $manifest = [
            'name' => $name,
            'short_name' => $name,
            'description' => $desc,
            'start_url' => portal_home_url(),
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => '#ffffff',
            'theme_color' => '#1c66f6',
            'icons' => array_values(array_filter([
                $favicon ? [
                    'src' => $favicon,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable',
                ] : null,
                $logo ? [
                    'src' => $logo,
                    'sizes' => '512x512',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any',
                ] : null,
            ])),
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json; charset=utf-8',
        ]);
    }
}
