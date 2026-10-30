<?php

namespace Platform\Portal\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Platform\Portal\DataGrids\PortalMenuDataGrid;
use Platform\Portal\Http\Controllers\Controller;
use Platform\Portal\Repositories\PortalMenuItemRepository;
use Platform\Portal\Repositories\PortalMenuRepository;
use Platform\Portal\Repositories\PortalPageRepository;

class MenuController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PortalMenuRepository $portalMenuRepository,
        protected PortalMenuItemRepository $portalMenuItemRepository,
        protected PortalPageRepository $portalPageRepository
    ) {}

    /**
     * Display a listing of menus.
     */
    public function index(): View|JsonResponse
    {
        abort_unless(bouncer()->hasPermission('portal.menus.index') || bouncer()->hasPermission('portal_menus.index'), 403);

        if (request()->ajax()) {
            return app(PortalMenuDataGrid::class)->toJson();
        }

        return view('portal::admin.menus.index');
    }

    /**
     * Show the form for creating a new menu.
     */
    public function create(): View
    {
        abort_unless(bouncer()->hasPermission('portal.menus.create') || bouncer()->hasPermission('portal_menus.create'), 403);

        $channels = core()->getAllChannels();
        $locales = core()->getAllLocales();
        $currentLocale = core()->getRequestedLocale();

        $portalPages = $this->portalPageRepository->getModel()
            ->where('status', 1)
            ->with(['translations' => fn ($q) => $q->where('locale', $currentLocale->code)])
            ->get()
            ->map(function ($page) use ($currentLocale) {
                return [
                    'id' => $page->id,
                    'title' => $page->translate($currentLocale->code)?->title ?? $page->title ?? $page->slug,
                    'slug' => $page->slug,
                ];
            });

        $items = collect([]);

        return view('portal::admin.menus.create', compact('channels', 'locales', 'currentLocale', 'portalPages', 'items'));
    }

    /**
     * Store a newly created menu.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        abort_unless(bouncer()->hasPermission('portal.menus.create') || bouncer()->hasPermission('portal_menus.create'), 403);

        $channelId = $request->input('channel_id') ?: null;
        $localeCode = $request->input('locale', core()->getRequestedLocaleCode());

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('portal_menus', 'slug')
                    ->where(function ($query) use ($channelId) {
                        return $channelId
                            ? $query->where('channel_id', $channelId)
                            : $query->whereNull('channel_id');
                    }),
            ],
            'channel_id' => ['nullable', 'integer', 'exists:channels,id'],
            'status' => ['nullable', 'boolean'],
        ]);

        $slug = $request->input('slug') ?: Str::slug($request->input('name'));

        // Check slug uniqueness
        $exists = $this->portalMenuRepository->getModel()
            ->where('slug', $slug)
            ->where('channel_id', $channelId)
            ->exists();

        if ($exists) {
            $slug = $slug.'-'.Str::lower(Str::random(4));
        }

        $menu = $this->portalMenuRepository->create([
            'name' => $request->input('name'),
            'slug' => $slug,
            'channel_id' => $channelId,
            'status' => $request->boolean('status', true),
        ]);

        // Save items if provided
        $items = $request->input('items');

        if (! $items && $request->has('items_json')) {
            $rawItems = $request->input('items_json');
            $items = is_string($rawItems) ? json_decode($rawItems, true) : $rawItems;
        }

        if (! empty($items) && is_array($items)) {
            $this->portalMenuItemRepository->saveTree($menu->id, $items, $localeCode);
        }

        session()->flash('success', trans('portal::app.admin.menus.create-success'));

        if ($request->ajax()) {
            return response()->json([
                'message' => trans('portal::app.admin.menus.create-success'),
                'redirect' => route('admin.portal.menus.edit', $menu->id),
            ]);
        }

        return redirect()->route('admin.portal.menus.edit', $menu->id);
    }

    /**
     * Redirect builder to edit for unified Product-Family style interface.
     */
    public function builder(int $id): RedirectResponse
    {
        return redirect()->route('admin.portal.menus.edit', [
            'id' => $id,
            'locale' => request('locale', core()->getRequestedLocaleCode()),
        ]);
    }

    /**
     * Show the form for editing the specified menu.
     */
    public function edit(int $id): View
    {
        abort_unless(bouncer()->hasPermission('portal.menus.edit') || bouncer()->hasPermission('portal_menus.edit'), 403);

        $menu = $this->portalMenuRepository->findOrFail($id);

        $channel = $menu->channel_id
            ? core()->getAllChannels()->firstWhere('id', $menu->channel_id)
            : core()->getCurrentChannel();

        $locales = $channel ? $channel->locales : core()->getAllLocales();

        $currentLocale = core()->getRequestedLocale();

        if ($channel && ! $locales->contains('id', $currentLocale->id)) {
            $currentLocale = $channel->default_locale ?? $locales->first();
        }

        // Fetch pages with titles resolved for requested locale
        $portalPages = $this->portalPageRepository->getModel()
            ->when($menu->channel_id, fn ($q) => $q->where('channel_id', $menu->channel_id))
            ->where('status', 1)
            ->with(['translations' => fn ($q) => $q->where('locale', $currentLocale->code)])
            ->get()
            ->map(function ($page) use ($currentLocale) {
                return [
                    'id' => $page->id,
                    'title' => $page->translate($currentLocale->code)?->title ?? $page->title ?? $page->slug,
                    'slug' => $page->slug,
                ];
            });

        // Load root items with recursive children and translations
        $items = $menu->rootItems()
            ->with([
                'translations',
                'page.translations',
                'children.translations',
                'children.page.translations',
                'children.children.translations',
                'children.children.page.translations',
            ])
            ->get();

        $channels = core()->getAllChannels();

        return view('portal::admin.menus.edit', compact(
            'menu',
            'items',
            'portalPages',
            'channels',
            'locales',
            'currentLocale',
            'channel'
        ));
    }

    /**
     * Update menu general details and items.
     */
    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        abort_unless(bouncer()->hasPermission('portal.menus.edit') || bouncer()->hasPermission('portal_menus.edit'), 403);

        $menu = $this->portalMenuRepository->findOrFail($id);

        $channelId = $request->input('channel_id') ?: null;
        $localeCode = $request->input('locale', core()->getRequestedLocaleCode());

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('portal_menus', 'slug')
                    ->ignore($id)
                    ->where(function ($query) use ($channelId) {
                        return $channelId
                            ? $query->where('channel_id', $channelId)
                            : $query->whereNull('channel_id');
                    }),
            ],
            'channel_id' => ['nullable', 'integer', 'exists:channels,id'],
            'status' => ['nullable', 'boolean'],
        ]);

        $menu->update([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('slug')),
            'channel_id' => $channelId,
            'status' => $request->boolean('status', true),
        ]);

        // Save items if provided
        $items = $request->input('items');

        if (! $items && $request->has('items_json')) {
            $rawItems = $request->input('items_json');
            $items = is_string($rawItems) ? json_decode($rawItems, true) : $rawItems;
        }

        if (is_array($items)) {
            $this->portalMenuItemRepository->saveTree($menu->id, $items, $localeCode);
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => trans('portal::app.admin.menus.update-success'),
            ]);
        }

        session()->flash('success', trans('portal::app.admin.menus.update-success'));

        return redirect()->route('admin.portal.menus.edit', [
            'id' => $menu->id,
            'locale' => $localeCode,
        ]);
    }

    /**
     * Save the entire menu items tree via AJAX.
     */
    public function saveItems(Request $request, int $id): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('portal.menus.builder') || bouncer()->hasPermission('portal_menus.builder'), 403);

        $menu = $this->portalMenuRepository->findOrFail($id);

        $items = $request->input('items', []);
        $localeCode = $request->input('locale', core()->getRequestedLocaleCode());

        $this->portalMenuItemRepository->saveTree($menu->id, $items, $localeCode);

        return response()->json([
            'message' => trans('portal::app.admin.menus.items-saved-success'),
        ]);
    }

    /**
     * Remove the specified menu from storage.
     */
    public function destroy(int $id): JsonResponse|RedirectResponse
    {
        abort_unless(bouncer()->hasPermission('portal.menus.delete') || bouncer()->hasPermission('portal_menus.delete'), 403);

        $this->portalMenuRepository->delete($id);

        if (request()->ajax()) {
            return response()->json([
                'message' => trans('portal::app.admin.menus.delete-success'),
            ]);
        }

        session()->flash('success', trans('portal::app.admin.menus.delete-success'));

        return redirect()->route('admin.portal.menus.index');
    }
}
