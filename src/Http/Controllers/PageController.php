<?php

namespace Platform\Portal\Http\Controllers;

use Illuminate\View\View;
use Platform\Portal\Repositories\PortalPageRepository;
use Platform\Portal\Repositories\PortalPageSectionRepository;
use Webkul\CMS\Repositories\PageRepository as CmsPageRepository;
use Webkul\Core\Http\Middleware\SecureHeaders;

class PageController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PortalPageRepository $portalPageRepository,
        protected PortalPageSectionRepository $portalPageSectionRepository,
        protected CmsPageRepository $cmsPageRepository
    ) {}

    /**
     * View a dynamic modular page or fallback to standard CMS page.
     */
    public function view(string $slug): View
    {
        $channel = core()->getCurrentChannel();

        $portalPage = $this->portalPageRepository->findBySlug($slug, $channel->id);

        if ($portalPage) {
            $sections = $this->portalPageSectionRepository->getRenderableSections($portalPage->id);

            return view('portal::page.show', [
                'page' => $portalPage,
                'sections' => $sections,
            ]);
        }

        $cmsPage = $this->cmsPageRepository
            ->whereHas('channels', function ($query) use ($channel) {
                $query->where('id', $channel->id);
            })
            ->whereTranslation('url_key', $slug)
            ->first();

        abort_if(! $cmsPage, 404);

        return view('portal::cms.page', [
            'page' => $cmsPage,
        ]);
    }

    /**
     * Preview a dynamic modular page with drafts for customizer.
     */
    public function preview(string $slug): View
    {
        abort_unless(
            bouncer()->hasPermission('portal.pages.edit') ||
            bouncer()->hasPermission('pages.edit') ||
            bouncer()->hasPermission('portal.pages.customize') ||
            bouncer()->hasPermission('pages.customize') ||
            request()->hasValidSignature(),
            403,
            'Unauthorized preview access.'
        );

        request()->attributes->set(SecureHeaders::FRAMABLE, true);

        $channel = core()->getAllChannels()->firstWhere('code', request('channel'))
            ?? core()->getCurrentChannel();

        $portalPage = $this->portalPageRepository->findBySlugForPreview($slug, $channel->id);

        abort_if(! $portalPage, 404);

        $locale = request('locale', app()->getLocale());

        $sections = $this->portalPageSectionRepository->getDraftSections($portalPage->id, $locale);

        return view('portal::page.show', [
            'page' => $portalPage,
            'sections' => $sections,
            'preview' => true,
        ]);
    }
}
