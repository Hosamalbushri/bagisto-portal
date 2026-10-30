<?php

namespace Platform\Portal\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Platform\Portal\DataGrids\PortalPageDataGrid;
use Platform\Portal\Http\Controllers\Controller;
use Platform\Portal\Repositories\PortalPageRepository;
use Webkul\Core\Repositories\ChannelRepository;

class PageController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PortalPageRepository $portalPageRepository,
        protected ChannelRepository $channelRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.index'), 403);

        if (request()->ajax()) {
            return datagrid(PortalPageDataGrid::class)->process();
        }

        return view('portal::admin.pages.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        abort_unless(bouncer()->hasPermission('pages.create'), 403);

        $channels = $this->channelRepository->all();

        return view('portal::admin.pages.create', compact('channels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse|RedirectResponse
    {
        abort_unless(bouncer()->hasPermission('pages.create'), 403);

        $locale = request('locale', core()->getRequestedLocaleCode());

        $this->validate(request(), [
            'slug' => ['required', 'alpha_dash'],
            'channel_id' => ['required', 'exists:channels,id'],
        ]);

        $status = request()->has('status') ? (request()->boolean('status') ? 1 : 0) : 0;
        $isHomePage = request()->boolean('is_home_page') ? 1 : 0;

        if ($isHomePage && ! $status) {
            $errorMsg = trans('portal::app.admin.pages.home-page-must-be-active');

            if (request()->ajax() || request()->wantsJson()) {
                return new JsonResponse([
                    'message' => $errorMsg,
                    'errors' => ['status' => [$errorMsg]],
                ], 422);
            }

            session()->flash('error', $errorMsg);

            return redirect()->back()->withInput();
        }

        // Extract translatable attributes (supports both array per locale and flat inputs)
        $localeData = request($locale, []);
        $title = $localeData['title'] ?? request('title');
        $metaTitle = $localeData['meta_title'] ?? request('meta_title');
        $metaDescription = $localeData['meta_description'] ?? request('meta_description');
        $metaKeywords = $localeData['meta_keywords'] ?? request('meta_keywords');

        if (empty($title)) {
            $titleMsg = trans('validation.required', ['attribute' => trans('portal::app.admin.pages.create.page-title')]);

            if (request()->ajax() || request()->wantsJson()) {
                return new JsonResponse([
                    'message' => $titleMsg,
                    'errors' => [
                        $locale.'.title' => [$titleMsg],
                        'title' => [$titleMsg],
                    ],
                ], 422);
            }

            return redirect()->back()->withErrors(['title' => $titleMsg])->withInput();
        }

        $data = [
            'slug' => request('slug'),
            'channel_id' => (int) request('channel_id'),
            'status' => $status,
            'is_home_page' => ($isHomePage && $status) ? 1 : 0,
            $locale => [
                'title' => $title,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'meta_keywords' => $metaKeywords,
            ],
        ];

        // Capture any other locales present in request
        foreach (core()->getAllLocales() as $loc) {
            if ($loc->code !== $locale && request()->has($loc->code)) {
                $otherData = request($loc->code);
                if (! empty($otherData['title'])) {
                    $data[$loc->code] = $otherData;
                }
            }
        }

        $page = $this->portalPageRepository->create($data);

        $successMsg = trans('portal::app.admin.pages.create-success');

        if (request()->ajax() || request()->wantsJson()) {
            return new JsonResponse([
                'data' => $page,
                'message' => $successMsg,
                'redirect_url' => route('admin.portal.pages.customize', $page->id),
            ]);
        }

        session()->flash('success', $successMsg);

        return redirect()->route('admin.portal.pages.customize', $page->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        abort_unless(bouncer()->hasPermission('pages.edit'), 403);

        $page = $this->portalPageRepository->findOrFail($id);
        $channels = $this->channelRepository->all();

        return view('portal::admin.pages.edit', compact('page', 'channels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse|RedirectResponse
    {
        abort_unless(bouncer()->hasPermission('pages.edit'), 403);

        $locale = request('locale', core()->getRequestedLocaleCode());

        $this->validate(request(), [
            'slug' => ['required', 'alpha_dash'],
            'channel_id' => ['required', 'exists:channels,id'],
        ]);

        $status = request()->has('status') ? (request()->boolean('status') ? 1 : 0) : 0;
        $isHomePage = request()->boolean('is_home_page') ? 1 : 0;

        if ($isHomePage && ! $status) {
            $errorMsg = trans('portal::app.admin.pages.home-page-must-be-active');

            if (request()->ajax() || request()->wantsJson()) {
                return new JsonResponse([
                    'message' => $errorMsg,
                    'errors' => ['status' => [$errorMsg]],
                ], 422);
            }

            session()->flash('error', $errorMsg);

            return redirect()->back()->withInput();
        }

        $localeData = request($locale, []);
        $title = $localeData['title'] ?? request('title');
        $metaTitle = $localeData['meta_title'] ?? request('meta_title');
        $metaDescription = $localeData['meta_description'] ?? request('meta_description');
        $metaKeywords = $localeData['meta_keywords'] ?? request('meta_keywords');

        if (empty($title)) {
            $titleMsg = trans('validation.required', ['attribute' => trans('portal::app.admin.pages.create.page-title')]);

            if (request()->ajax() || request()->wantsJson()) {
                return new JsonResponse([
                    'message' => $titleMsg,
                    'errors' => [
                        $locale.'.title' => [$titleMsg],
                        'title' => [$titleMsg],
                    ],
                ], 422);
            }

            return redirect()->back()->withErrors(['title' => $titleMsg])->withInput();
        }

        $data = [
            'slug' => request('slug'),
            'channel_id' => (int) request('channel_id'),
            'status' => $status,
            'is_home_page' => ($isHomePage && $status) ? 1 : 0,
            $locale => [
                'title' => $title,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'meta_keywords' => $metaKeywords,
            ],
        ];

        foreach (core()->getAllLocales() as $loc) {
            if ($loc->code !== $locale && request()->has($loc->code)) {
                $otherData = request($loc->code);
                if (! empty($otherData['title'])) {
                    $data[$loc->code] = $otherData;
                }
            }
        }

        $page = $this->portalPageRepository->update($data, $id);

        $successMsg = trans('portal::app.admin.pages.update-success');

        if (request()->ajax() || request()->wantsJson()) {
            return new JsonResponse([
                'data' => $page,
                'message' => $successMsg,
                'redirect_url' => route('admin.portal.pages.index'),
            ]);
        }

        session()->flash('success', $successMsg);

        return redirect()->route('admin.portal.pages.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(int $id): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.delete'), 403);

        $this->portalPageRepository->delete($id);

        return new JsonResponse([
            'message' => trans('portal::app.admin.pages.delete-success'),
        ]);
    }

    /**
     * Mass update status of pages.
     */
    public function massUpdate(): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.edit'), 403);

        $indices = request('indices', []);
        $value = (int) request('value', 0);

        foreach ($indices as $pageId) {
            $page = $this->portalPageRepository->find($pageId);

            if (! $page) {
                continue;
            }

            $page->status = $value;

            if (! $value && $page->is_home_page) {
                $page->is_home_page = 0;
            }

            $page->save();
        }

        return new JsonResponse([
            'message' => trans('portal::app.admin.pages.mass-update-success'),
        ]);
    }

    /**
     * Mass delete pages.
     */
    public function massDelete(): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.delete'), 403);

        $indices = request('indices', []);

        foreach ($indices as $pageId) {
            $this->portalPageRepository->delete($pageId);
        }

        return new JsonResponse([
            'message' => trans('portal::app.admin.pages.mass-delete-success'),
        ]);
    }
}
