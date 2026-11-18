<?php

namespace Platform\Portal\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Platform\Portal\Helpers\PortalCache;
use Platform\Portal\Http\Resources\PortalPageListResource;
use Platform\Portal\Http\Resources\PortalPageResource;
use Platform\Portal\Repositories\PortalPageRepository;

class PageController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PortalPageRepository $pageRepository,
        protected PortalCache $cache
    ) {}

    /**
     * Get list of published portal pages.
     */
    public function index(Request $request): JsonResponse
    {
        $channelId = (int) (function_exists('core') ? (core()->getCurrentChannel()?->id ?? 1) : 1);

        $data = $this->cache->remember('pages_list', ['channel_id' => $channelId], function () use ($channelId) {
            $pages = $this->pageRepository
                ->getModel()
                ->where('channel_id', $channelId)
                ->where('status', 1)
                ->orderBy('id', 'desc')
                ->get();

            return PortalPageListResource::collection($pages)->resolve();
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Get published page details by slug.
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $channelId = (int) (function_exists('core') ? (core()->getCurrentChannel()?->id ?? 1) : 1);

        $data = $this->cache->remember('page_detail', ['slug' => $slug, 'channel_id' => $channelId], function () use ($slug, $channelId) {
            $page = $this->pageRepository->findBySlug($slug, $channelId);

            if (! $page) {
                return null;
            }

            $page->load(['sections.translations']);

            return (new PortalPageResource($page))->resolve();
        });

        if (! $data) {
            return response()->json([
                'message' => trans('portal::app.pages.not-found', [], app()->getLocale()) ?: 'Page not found.',
            ], 404);
        }

        return response()->json([
            'data' => $data,
        ]);
    }
}
