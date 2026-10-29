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
        $this->validate(request(), [
            'slug' => ['required', 'alpha_dash'],
            'channel_id' => ['required', 'exists:channels,id'],
            core()->getRequestedLocaleCode().'.title' => ['required', 'string', 'max:255'],
        ]);

        $data = request()->all();
        $data['status'] = request()->has('status') ? 1 : 0;

        $page = $this->portalPageRepository->create($data);

        session()->flash('success', 'تم إنشاء الصفحة بنجاح.');

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
        $this->validate(request(), [
            'slug' => ['required', 'alpha_dash'],
            'channel_id' => ['required', 'exists:channels,id'],
            core()->getRequestedLocaleCode().'.title' => ['required', 'string', 'max:255'],
        ]);

        $data = request()->all();
        $data['status'] = request()->has('status') ? 1 : 0;

        $this->portalPageRepository->update($data, $id);

        session()->flash('success', 'تم تحديث الصفحة بنجاح.');

        return redirect()->route('admin.portal.pages.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(int $id): JsonResponse|RedirectResponse
    {
        $this->portalPageRepository->delete($id);

        if (request()->ajax()) {
            return new JsonResponse(['message' => 'تم حذف الصفحة بنجاح.']);
        }

        session()->flash('success', 'تم حذف الصفحة بنجاح.');

        return redirect()->route('admin.portal.pages.index');
    }
}
