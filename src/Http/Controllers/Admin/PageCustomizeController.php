<?php

namespace Platform\Portal\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Platform\Portal\Http\Controllers\Controller;
use Platform\Portal\Repositories\PortalPageRepository;
use Platform\Portal\Repositories\PortalPageSectionRepository;
use Platform\Portal\Sections\SectionTypeRegistry;

class PageCustomizeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PortalPageRepository $portalPageRepository,
        protected PortalPageSectionRepository $portalPageSectionRepository,
        protected SectionTypeRegistry $sectionTypeRegistry
    ) {}

    /**
     * Display the visual customizer for a specific page using the standard appearance section editor.
     */
    public function index(int $id): View
    {
        abort_unless(bouncer()->hasPermission('pages.customize'), 403);

        $page = $this->portalPageRepository->findOrFail($id);

        $channel = core()->getAllChannels()->firstWhere('code', request('channel'))
            ?? core()->getAllChannels()->firstWhere('id', $page->channel_id)
            ?? core()->getCurrentChannel();

        $locale = core()->getRequestedLocaleCode();

        $sections = $this->editableSections($page->id);

        $sectionTypes = $this->sectionTypeRegistry->all()
            ->map(fn ($type) => $type->toArray())
            ->values()
            ->all();

        $channels = core()->getAllChannels()->map(fn ($ch) => [
            'id' => $ch->id,
            'name' => core()->getChannelName($ch),
            'url' => route('admin.portal.pages.customize', [
                'id' => $page->id,
                'channel' => $ch->code,
            ]),
        ])->values()->all();

        $locales = $channel->locales->sortBy('name')->map(fn ($loc) => [
            'code' => $loc->code,
            'name' => $loc->name,
            'url' => route('admin.portal.pages.customize', [
                'id' => $page->id,
                'channel' => $channel->code,
                'locale' => $loc->code,
            ]),
        ])->values()->all();

        $previewUrl = route('portal.page.preview', [
            'slug' => $page->slug,
            'channel' => $channel->code,
            'locale' => $locale,
        ]);

        $reorderUrl = route('admin.portal.pages.sections.reorder', $page->id);
        $storeUrl = route('admin.portal.pages.sections.store', $page->id);
        $publishUrl = route('admin.portal.pages.publish', $page->id);
        $discardUrl = route('admin.portal.pages.discard', $page->id);

        $urls = [
            'duplicate' => route('admin.portal.pages.sections.duplicate', ['id' => $page->id, 'sectionId' => '__ID__']),
            'status' => route('admin.portal.pages.sections.status', ['id' => $page->id, 'sectionId' => '__ID__']),
            'fields' => route('admin.portal.pages.sections.fields', ['id' => $page->id, 'sectionId' => '__ID__']),
            'draft' => route('admin.portal.pages.sections.draft', ['id' => $page->id, 'sectionId' => '__ID__']),
            'media' => route('admin.portal.pages.sections.media', ['id' => $page->id, 'sectionId' => '__ID__']),
            'delete' => route('admin.portal.pages.sections.delete', ['id' => $page->id, 'sectionId' => '__ID__']),
        ];

        return view('portal::admin.pages.customize', [
            'page' => $page,
            'sections' => $sections,
            'sectionTypes' => $sectionTypes,
            'themeName' => $page->title,
            'channels' => $channels,
            'channelId' => $channel->id,
            'locales' => $locales,
            'localeCode' => $locale,
            'previewUrl' => $previewUrl,
            'reorderUrl' => $reorderUrl,
            'storeUrl' => $storeUrl,
            'publishUrl' => $publishUrl,
            'discardUrl' => $discardUrl,
            'urls' => $urls,
        ]);
    }

    /**
     * Store a newly created section on the page.
     */
    public function store(int $id): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.customize'), 403);

        $this->portalPageRepository->findOrFail($id);

        $this->validate(request(), [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
        ]);

        $type = request('type');
        $name = request('name');

        $typeInstance = $this->sectionTypeRegistry->get($type);
        abort_if(! $typeInstance, 422, trans('portal::app.admin.pages.customize.invalid-type'));

        $locale = core()->getRequestedLocaleCode();
        $maxSort = $this->portalPageSectionRepository->findWhere(['portal_page_id' => $id])->max('sort_order') ?? 0;

        $section = $this->portalPageSectionRepository->create([
            'portal_page_id' => $id,
            'type' => $type,
            'name' => $name,
            'sort_order' => $maxSort + 1,
            'status' => 0,
            'draft_status' => true,
            $locale => [
                'options' => $typeInstance->getDefaultOptions(),
                'draft_options' => $typeInstance->getDefaultOptions(),
            ],
        ]);

        return new JsonResponse([
            'section' => $this->sectionRow($section->refresh()),
            'message' => trans('admin::app.appearance.sections.create-success'),
        ]);
    }

    /**
     * Fetch field schema and current values for a section drawer.
     */
    public function fields(int $id, int $sectionId): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.customize'), 403);

        $section = $this->portalPageSectionRepository->findOrFail($sectionId);
        abort_if($section->portal_page_id !== $id, 404);

        $locale = core()->getRequestedLocaleCode();
        $translation = $section->translate($locale, true);
        $type = $section->getTypeInstance();

        $options = $translation?->draft_options ?? $translation?->options ?? [];

        return new JsonResponse([
            'schema' => $type?->getFields() ?? [],
            'options' => ($type?->prepareForEditor($options) ?? $options) ?: (object) [],
        ]);
    }

    /**
     * Store unpublished edits for a section.
     */
    public function saveDraft(int $id, int $sectionId): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.customize'), 403);

        $this->validate(request(), [
            'options' => ['required', 'array'],
        ]);

        $section = $this->portalPageSectionRepository->findOrFail($sectionId);
        abort_if($section->portal_page_id !== $id, 404);

        $locale = core()->getRequestedLocaleCode();
        $section = $this->portalPageSectionRepository->saveDraft($sectionId, $locale, request()->input('options'));

        return new JsonResponse([
            'has_draft' => $this->portalPageSectionRepository->hasDraft($section),
        ]);
    }

    /**
     * Turn a section on or off in draft mode.
     */
    public function status(int $id, int $sectionId): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.customize'), 403);

        $section = $this->portalPageSectionRepository->findOrFail($sectionId);
        abort_if($section->portal_page_id !== $id, 404);

        $status = request()->boolean('status');
        $section = $this->portalPageSectionRepository->saveStatusDraft($sectionId, $status);

        return new JsonResponse([
            'status' => $status,
            'has_draft' => $this->portalPageSectionRepository->hasDraft($section),
        ]);
    }

    /**
     * Duplicate an existing section.
     */
    public function duplicate(int $id, int $sectionId): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.customize'), 403);

        $section = $this->portalPageSectionRepository->findOrFail($sectionId);
        abort_if($section->portal_page_id !== $id, 404);

        $duplicate = $this->portalPageSectionRepository->duplicate($sectionId);

        return new JsonResponse([
            'section' => $this->sectionRow($duplicate),
            'message' => trans('admin::app.appearance.sections.create-success'),
        ]);
    }

    /**
     * Reorder sections on the page.
     */
    public function reorder(int $id): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.customize'), 403);

        $this->validate(request(), [
            'sections' => ['required', 'array', 'min:1'],
            'sections.*' => ['required', 'integer'],
        ]);

        $sectionIds = request()->input('sections');
        $this->portalPageSectionRepository->saveOrderDraft($sectionIds);

        $sections = $this->portalPageSectionRepository->findWhereIn('id', $sectionIds);

        return new JsonResponse([
            'pending' => $sections->mapWithKeys(fn ($section) => [
                $section->id => $this->portalPageSectionRepository->hasDraft($section),
            ]),
        ]);
    }

    /**
     * Store uploaded media file for a section.
     */
    public function uploadMedia(int $id, int $sectionId): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.customize'), 403);

        $this->validate(request(), [
            'file' => ['required', 'mimes:bmp,jpeg,jpg,png,webp,mp4,webm,ogg', 'max:51200'],
        ]);

        $section = $this->portalPageSectionRepository->findOrFail($sectionId);
        abort_if($section->portal_page_id !== $id, 404);

        $file = request()->file('file');
        $path = $file->store('portal/sections/'.$sectionId, 'public');

        return new JsonResponse([
            'path' => 'storage/'.$path,
            'url' => asset('storage/'.$path),
        ]);
    }

    /**
     * Delete section from the page.
     */
    public function destroy(int $id, int $sectionId): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.customize'), 403);

        $section = $this->portalPageSectionRepository->findOrFail($sectionId);
        abort_if($section->portal_page_id !== $id, 404);

        $this->portalPageSectionRepository->delete($sectionId);

        return new JsonResponse([
            'message' => trans('admin::app.appearance.sections.delete-success'),
        ]);
    }

    /**
     * Publish all drafts for the page.
     */
    public function publish(int $id): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.customize'), 403);

        $this->portalPageSectionRepository->publish($id);

        return new JsonResponse([
            'published' => count($this->editableSections($id)),
            'sections' => $this->editableSections($id),
            'message' => trans('admin::app.appearance.sections.update-success'),
        ]);
    }

    /**
     * Discard all drafts for the page.
     */
    public function discard(int $id): JsonResponse
    {
        abort_unless(bouncer()->hasPermission('pages.customize'), 403);

        $this->portalPageSectionRepository->discardDrafts($id);

        return new JsonResponse([
            'discarded' => count($this->editableSections($id)),
            'sections' => $this->editableSections($id),
            'message' => trans('admin::app.appearance.sections.index.discarded'),
        ]);
    }

    /**
     * Fetch sections formatted for the editor.
     */
    protected function editableSections(int $pageId): array
    {
        return $this->portalPageSectionRepository
            ->findWhere(['portal_page_id' => $pageId])
            ->sortBy(fn ($s) => $s->draft_sort_order ?? $s->sort_order)
            ->map(fn ($s) => $this->sectionRow($s))
            ->values()
            ->toArray();
    }

    /**
     * Format a single section row for the editor.
     */
    protected function sectionRow($section): array
    {
        return [
            'id' => $section->id,
            'name' => $section->name,
            'type' => $section->type,
            'status' => (bool) ($section->draft_status ?? $section->status),
            'has_draft' => $this->portalPageSectionRepository->hasDraft($section),
            'is_pinned' => false,
        ];
    }
}
