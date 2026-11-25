<?php

namespace Platform\Portal\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Platform\Portal\DataGrids\PortalPageDataGrid;
use Platform\Portal\Http\Controllers\Controller;
use Platform\Portal\Repositories\PortalPageRepository;
use Platform\Portal\Rules\ValidJsonArrayRule;
use Platform\Portal\Services\Tokens\TokenSanitizer;
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
        abort_unless(bouncer()->hasPermission('portal.pages.index') || bouncer()->hasPermission('pages.index'), 403);

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
        abort_unless(bouncer()->hasPermission('portal.pages.create') || bouncer()->hasPermission('pages.create'), 403);

        $channels = $this->channelRepository->all();

        return view('portal::admin.pages.create', compact('channels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse|RedirectResponse
    {
        abort_unless(bouncer()->hasPermission('portal.pages.create') || bouncer()->hasPermission('pages.create'), 403);

        $this->validate(request(), $this->getPageValidationRules());

        $locale = request('locale', core()->getRequestedLocaleCode());
        ['data' => $data, 'error' => $errorResponse] = $this->preparePagePayload($locale);

        if ($errorResponse) {
            return $errorResponse;
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
        abort_unless(bouncer()->hasPermission('portal.pages.edit') || bouncer()->hasPermission('pages.edit'), 403);

        $page = $this->portalPageRepository->findOrFail($id);
        $channels = $this->channelRepository->all();

        return view('portal::admin.pages.edit', compact('page', 'channels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse|RedirectResponse
    {
        abort_unless(bouncer()->hasPermission('portal.pages.edit') || bouncer()->hasPermission('pages.edit'), 403);

        $this->validate(request(), $this->getPageValidationRules($id));

        $locale = request('locale', core()->getRequestedLocaleCode());
        ['data' => $data, 'error' => $errorResponse] = $this->preparePagePayload($locale);

        if ($errorResponse) {
            return $errorResponse;
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
     * Get the common validation rules for page creation and update.
     */
    protected function getPageValidationRules(?int $ignoreId = null): array
    {
        $channelId = request('channel_id');

        $uniqueSlugRule = Rule::unique('portal_pages', 'slug')
            ->where(fn ($query) => $query->where('channel_id', $channelId));

        if ($ignoreId !== null) {
            $uniqueSlugRule->ignore($ignoreId);
        }

        $rules = [
            'slug' => [
                'required',
                'alpha_dash',
                function ($attribute, $value, $fail) {
                    if (strtolower($value) === 'home') {
                        $fail(trans('portal::app.admin.pages.url-key-home-reserved'));
                    }
                },
                $uniqueSlugRule,
            ],
            'channel_id' => ['required', 'exists:channels,id'],
        ];

        if (request()->has('tokens')) {
            $rules['tokens'] = [new ValidJsonArrayRule];
        }

        return $rules;
    }

    /**
     * Validate and build the page data payload from the request.
     *
     * @return array{data: array, error: JsonResponse|RedirectResponse|null}
     */
    protected function preparePagePayload(string $locale): array
    {
        $status = request()->has('status') ? (request()->boolean('status') ? 1 : 0) : 0;
        $isHomePage = request()->boolean('is_home_page') ? 1 : 0;

        if ($isHomePage && ! $status) {
            $errorMsg = trans('portal::app.admin.pages.home-page-must-be-active');

            $errorResponse = (request()->ajax() || request()->wantsJson())
                ? new JsonResponse([
                    'message' => $errorMsg,
                    'errors' => ['status' => [$errorMsg]],
                ], 422)
                : redirect()->back()->withInput()->with('error', $errorMsg);

            return ['data' => [], 'error' => $errorResponse];
        }

        $localeData = request($locale, []);
        $title = $localeData['title'] ?? request('title');
        $metaTitle = $localeData['meta_title'] ?? request('meta_title');
        $metaDescription = $localeData['meta_description'] ?? request('meta_description');
        $metaKeywords = $localeData['meta_keywords'] ?? request('meta_keywords');

        if (empty($title)) {
            $titleMsg = trans('validation.required', ['attribute' => trans('portal::app.admin.pages.create.page-title')]);

            $errorResponse = (request()->ajax() || request()->wantsJson())
                ? new JsonResponse([
                    'message' => $titleMsg,
                    'errors' => [
                        $locale.'.title' => [$titleMsg],
                        'title' => [$titleMsg],
                    ],
                ], 422)
                : redirect()->back()->withErrors(['title' => $titleMsg])->withInput();

            return ['data' => [], 'error' => $errorResponse];
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

        if (request()->has('tokens')) {
            $rawTokens = request('tokens');
            if (is_string($rawTokens)) {
                $rawTokens = json_decode($rawTokens, true);
            }

            $normalized = app(TokenSanitizer::class)->normalizeTokens($rawTokens);
            $data['tokens'] = empty($normalized) ? null : $normalized;
        }

        return ['data' => $data, 'error' => null];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(int $id): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('portal.pages.delete') || bouncer()->hasPermission('pages.delete'), 403);

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
        abort_unless(bouncer()->hasPermission('portal.pages.edit') || bouncer()->hasPermission('pages.edit'), 403);

        $this->validate(request(), [
            'indices' => ['required', 'array', 'min:1'],
            'indices.*' => ['integer'],
            'value' => ['required', 'in:0,1'],
        ]);

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

            Event::dispatch('portal.page.updated', $page);

            \Illuminate\Support\Facades\Event::dispatch('portal.page.updated', $page);
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
        abort_unless(bouncer()->hasPermission('portal.pages.delete') || bouncer()->hasPermission('pages.delete'), 403);

        $this->validate(request(), [
            'indices' => ['required', 'array', 'min:1'],
            'indices.*' => ['integer'],
        ]);

        $indices = request('indices', []);

        foreach ($indices as $pageId) {
            $this->portalPageRepository->delete($pageId);
        }

        return new JsonResponse([
            'message' => trans('portal::app.admin.pages.mass-delete-success'),
        ]);
    }
}
