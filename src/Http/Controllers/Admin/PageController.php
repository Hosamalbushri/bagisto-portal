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
        $channels = $this->channelRepository->all();

        return view('portal::admin.pages.create', compact('channels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): RedirectResponse
    {
        $locale = core()->getRequestedLocaleCode();

        $this->validate(request(), [
            'slug' => ['required', 'alpha_dash'],
            'channel_id' => ['required', 'exists:channels,id'],
            'title' => ['required', 'string', 'max:255'],
        ]);

        $data = [
            'slug' => request('slug'),
            'channel_id' => (int) request('channel_id'),
            'status' => request()->boolean('status', true) ? 1 : 0,
            $locale => [
                'title' => request('title'),
                'meta_title' => request('meta_title'),
                'meta_description' => request('meta_description'),
                'meta_keywords' => request('meta_keywords'),
            ],
        ];

        $page = $this->portalPageRepository->create($data);

        session()->flash('success', trans('portal::app.admin.pages.create-success'));

        return redirect()->route('admin.portal.pages.customize', $page->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $page = $this->portalPageRepository->findOrFail($id);
        $channels = $this->channelRepository->all();

        return view('portal::admin.pages.edit', compact('page', 'channels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): RedirectResponse
    {
        $locale = core()->getRequestedLocaleCode();

        $this->validate(request(), [
            'slug' => ['required', 'alpha_dash'],
            'channel_id' => ['required', 'exists:channels,id'],
            'title' => ['required', 'string', 'max:255'],
        ]);

        $data = [
            'slug' => request('slug'),
            'channel_id' => (int) request('channel_id'),
            'status' => request()->boolean('status', true) ? 1 : 0,
            $locale => [
                'title' => request('title'),
                'meta_title' => request('meta_title'),
                'meta_description' => request('meta_description'),
                'meta_keywords' => request('meta_keywords'),
            ],
        ];

        $this->portalPageRepository->update($data, $id);

        session()->flash('success', trans('portal::app.admin.pages.update-success'));

        return redirect()->route('admin.portal.pages.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(int $id): JsonResponse
    {
        $this->portalPageRepository->delete($id);

        return new JsonResponse([
            'message' => trans('portal::app.admin.pages.delete-success'),
        ]);
    }
}
