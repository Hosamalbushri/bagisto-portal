<?php

namespace Platform\Portal\Http\Controllers;

use Illuminate\View\View;
use Webkul\CMS\Repositories\PageRepository;

class PageController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PageRepository $pageRepository
    ) {}

    /**
     * Loads the CMS page for the storefront.
     */
    public function view(string $slug): View
    {
        $page = $this->pageRepository
            ->whereHas('channels', function ($query) {
                $query->where('id', core()->getCurrentChannel()->id);
            })
            ->whereTranslation('url_key', $slug)
            ->first();

        abort_if(! $page, 404);

        return view('portal::cms.page', compact('page'));
    }
}
