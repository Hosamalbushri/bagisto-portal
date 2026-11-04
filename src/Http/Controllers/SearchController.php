<?php

namespace Platform\Portal\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Platform\Portal\Models\PortalPage;
use Webkul\CMS\Repositories\PageRepository as CmsPageRepository;

class SearchController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ?CmsPageRepository $cmsPageRepository = null
    ) {}

    /**
     * Search portal modular pages and CMS content.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = trim((string) $request->input('query', ''));

        $results = collect();

        if (strlen($query) >= 2) {
            $channel = core()->getCurrentChannel();

            // 1. Search Portal Modular Pages
            $portalPages = PortalPage::query()
                ->where('channel_id', $channel->id)
                ->where('status', 1)
                ->where(function ($builder) use ($query) {
                    $builder->where('slug', 'like', "%{$query}%")
                        ->orWhereHas('translations', function ($q) use ($query) {
                            $q->where('title', 'like', "%{$query}%")
                                ->orWhere('meta_title', 'like', "%{$query}%")
                                ->orWhere('meta_description', 'like', "%{$query}%")
                                ->orWhere('meta_keywords', 'like', "%{$query}%");
                        });
                })
                ->get()
                ->map(fn ($page) => [
                    'id' => 'portal_'.$page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'url' => route('portal.page.show', $page->slug),
                    'description' => $page->meta_description ?: strip_tags((string) $page->title),
                    'type' => 'portal',
                ]);

            // 2. Search CMS Pages (if available)
            $cmsPages = collect();

            if ($this->cmsPageRepository) {
                try {
                    $cmsPages = $this->cmsPageRepository->getModel()
                        ->whereHas('channels', fn ($q) => $q->where('channels.id', $channel->id))
                        ->where(function ($builder) use ($query) {
                            $builder->whereTranslationLike('page_title', "%{$query}%")
                                ->orWhereTranslationLike('url_key', "%{$query}%")
                                ->orWhereTranslationLike('html_content', "%{$query}%");
                        })
                        ->get()
                        ->map(fn ($page) => [
                            'id' => 'cms_'.$page->id,
                            'title' => $page->page_title,
                            'slug' => $page->url_key,
                            'url' => route('portal.page.show', $page->url_key),
                            'description' => $page->meta_description ?: Str::limit(strip_tags((string) $page->html_content), 160),
                            'type' => 'cms',
                        ]);
                } catch (\Throwable) {
                    $cmsPages = collect();
                }
            }

            $results = $portalPages->concat($cmsPages)->unique('url');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'query' => $query,
                'count' => $results->count(),
                'data' => $results->values(),
            ]);
        }

        $perPage = 10;
        $page = (int) $request->input('page', 1);
        $paginatedResults = new LengthAwarePaginator(
            $results->forPage($page, $perPage)->values(),
            $results->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('portal::search.index', [
            'query' => $query,
            'results' => $paginatedResults,
            'total' => $results->count(),
        ]);
    }
}
