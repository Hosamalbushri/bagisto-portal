<?php

namespace Platform\Portal\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Platform\Portal\Http\Requests\ContactRequest;
use Platform\Portal\Mail\ContactUs;
use Platform\Portal\Repositories\PortalPageRepository;
use Platform\Portal\Repositories\PortalPageSectionRepository;
use Webkul\Core\Http\Middleware\SecureHeaders;
use Webkul\Theme\Repositories\SectionRepository;
use Webkul\Theme\ThemeCatalog;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SectionRepository $sectionRepository,
        protected PortalPageRepository $portalPageRepository,
        protected PortalPageSectionRepository $portalPageSectionRepository
    ) {}

    /**
     * Loads the home page for the storefront.
     */
    public function index(): View
    {
        $channel = core()->getCurrentChannel();

        $homePortalPage = $this->portalPageRepository->findHomePage($channel->id);

        if ($homePortalPage && $homePortalPage->status) {
            $sections = $this->portalPageSectionRepository->getRenderableSections($homePortalPage->id);

            return view('portal::page.show', [
                'page' => $homePortalPage,
                'sections' => $sections,
            ]);
        }

        $sections = $this->sectionRepository->getRenderable(
            $channel->id,
            $channel->theme ?? config('themes.shop-default', 'default')
        );

        return view('portal::home.index', compact('sections'));
    }

    /**
     * Render a channel's home page in the requested theme from unpublished section edits, for the appearance area.
     */
    public function preview(): View
    {
        abort_unless(bouncer()->hasPermission('appearance.sections'), 403);

        $channel = core()->getAllChannels()->firstWhere('code', request('channel'))
            ?? core()->getCurrentChannel();

        $themeCode = $this->previewedTheme($channel->theme);

        request()->attributes->set(SecureHeaders::FRAMABLE, true);

        request()->attributes->set(SectionRepository::PREVIEWING, true);

        $previewed = clone $channel;

        $previewed->theme = $themeCode;

        core()->setCurrentChannel($previewed);

        themes()->set($themeCode);

        $sections = $this->sectionRepository->getDraftedForPreview(
            $channel->id,
            $themeCode,
            app()->getLocale()
        );

        return view('portal::home.index', compact('sections') + [
            'preview' => true,
            'previewTheme' => app(ThemeCatalog::class)->find($themeCode)['name'] ?? $themeCode,
        ]);
    }

    /**
     * Display the contact us page.
     */
    public function contactUs(): View
    {
        return view('portal::home.contact-us');
    }

    /**
     * Send the contact us mail.
     */
    public function sendContactUsMail(ContactRequest $contactRequest): RedirectResponse
    {
        try {
            Mail::queue(new ContactUs($contactRequest->only([
                'name',
                'email',
                'contact',
                'message',
            ])));

            session()->flash('success', trans('portal::app.home.thanks-for-contact'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            report($e);
        }

        return back();
    }

    /**
     * The theme to preview, which has to be installed when named, otherwise the one the channel runs.
     */
    protected function previewedTheme(?string $channelTheme): string
    {
        $requested = request('theme');

        if (filled($requested)) {
            abort_unless(
                is_string($requested)
                && app(ThemeCatalog::class)->isInstalled($requested),
                404
            );

            return $requested;
        }

        if (
            $channelTheme
            && app(ThemeCatalog::class)->isInstalled($channelTheme)
        ) {
            return $channelTheme;
        }

        return config('themes.shop-default', 'default');
    }
}
