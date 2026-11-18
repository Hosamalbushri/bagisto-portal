<?php

namespace Platform\Portal\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Platform\Portal\Helpers\PortalCache;
use Platform\Portal\Http\Resources\PortalMenuResource;
use Platform\Portal\Repositories\PortalMenuRepository;

class MenuController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PortalMenuRepository $menuRepository,
        protected PortalCache $cache
    ) {}

    /**
     * Get menu by slug or identifier with its full item tree.
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $channelId = (int) (function_exists('core') ? (core()->getCurrentChannel()?->id ?? 1) : 1);

        $data = $this->cache->remember('menu_detail', ['slug' => $slug, 'channel_id' => $channelId], function () use ($slug, $channelId) {
            $menu = $this->menuRepository
                ->getModel()
                ->where('slug', $slug)
                ->where('channel_id', $channelId)
                ->where('status', 1)
                ->with(['items.translations', 'items.children.translations'])
                ->first();

            if (! $menu) {
                return null;
            }

            return (new PortalMenuResource($menu))->resolve();
        });

        if (! $data) {
            return response()->json([
                'message' => 'Menu not found.',
            ], 404);
        }

        return response()->json([
            'data' => $data,
        ]);
    }
}
