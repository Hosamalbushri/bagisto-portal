<?php

namespace Platform\Portal\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
     * Display the visual customizer for a specific page.
     */
    public function index(int $id): View
    {
        $page = $this->portalPageRepository->findOrFail($id);

        $locale = core()->getRequestedLocaleCode();

        $sections = $this->portalPageSectionRepository->getDraftSections($page->id, $locale);

        $sectionTypes = $this->sectionTypeRegistry->all();

        $previewUrl = route('portal.page.preview', $page->slug);

        return view('portal::admin.pages.customize', compact('page', 'sections', 'sectionTypes', 'previewUrl'));
    }

    /**
     * Store a newly created section on the page.
     */
    public function storeSection(int $id): JsonResponse|RedirectResponse
    {
        $this->validate(request(), [
            'type' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $type = request('type');
        $name = request('name');

        $typeInstance = $this->sectionTypeRegistry->get($type);

        abort_if(! $typeInstance, 422, 'نوع القسم غير مدعوم.');

        $locale = core()->getRequestedLocaleCode();

        $maxSort = $this->portalPageSectionRepository->findWhere(['portal_page_id' => $id])->max('sort_order') ?? 0;

        $section = $this->portalPageSectionRepository->create([
            'portal_page_id' => $id,
            'type' => $type,
            'name' => $name,
            'sort_order' => $maxSort + 1,
            'status' => 1,
            $locale => [
                'options' => $typeInstance->getDefaultOptions(),
                'draft_options' => $typeInstance->getDefaultOptions(),
            ],
        ]);

        if (request()->ajax()) {
            return new JsonResponse([
                'message' => 'تمت إضافة القسم بنجاح.',
                'section' => $section,
            ]);
        }

        session()->flash('success', 'تمت إضافة القسم بنجاح.');

        return redirect()->route('admin.portal.pages.customize', $id);
    }

    /**
     * Update section options and settings.
     */
    public function updateSection(int $id, int $sectionId): JsonResponse|RedirectResponse
    {
        $section = $this->portalPageSectionRepository->findOrFail($sectionId);

        abort_if($section->portal_page_id !== $id, 404);

        $locale = core()->getRequestedLocaleCode();

        $options = request('options', []);

        $typeInstance = $this->sectionTypeRegistry->get($section->type);

        if ($typeInstance) {
            $options = $typeInstance->sanitize($options);
        }

        $this->portalPageSectionRepository->saveDraft($sectionId, $locale, $options);

        if (request()->has('name')) {
            $section->name = request('name');
            $section->save();
        }

        if (request()->ajax()) {
            return new JsonResponse([
                'message' => 'تم حفظ تعديلات القسم في المسودة.',
                'section' => $section->fresh(),
            ]);
        }

        session()->flash('success', 'تم حفظ تعديلات القسم في المسودة.');

        return redirect()->route('admin.portal.pages.customize', $id);
    }

    /**
     * Reorder sections on the page.
     */
    public function reorderSections(int $id): JsonResponse
    {
        $this->validate(request(), [
            'orders' => ['required', 'array'],
        ]);

        $orders = request('orders');

        foreach ($orders as $index => $sectionId) {
            $this->portalPageSectionRepository->update([
                'draft_sort_order' => $index + 1,
            ], $sectionId);
        }

        return new JsonResponse([
            'message' => 'تم تحديث ترتيب الأقسام بنجاح.',
        ]);
    }

    /**
     * Delete section from the page.
     */
    public function deleteSection(int $id, int $sectionId): JsonResponse|RedirectResponse
    {
        $section = $this->portalPageSectionRepository->findOrFail($sectionId);

        abort_if($section->portal_page_id !== $id, 404);

        $this->portalPageSectionRepository->delete($sectionId);

        if (request()->ajax()) {
            return new JsonResponse([
                'message' => 'تم حذف القسم بنجاح.',
            ]);
        }

        session()->flash('success', 'تم حذف القسم بنجاح.');

        return redirect()->route('admin.portal.pages.customize', $id);
    }

    /**
     * Publish all drafts for the page.
     */
    public function publish(int $id): JsonResponse|RedirectResponse
    {
        $this->portalPageSectionRepository->publish($id);

        if (request()->ajax()) {
            return new JsonResponse([
                'message' => 'تم نشر جميع التعديلات بنجاح!',
            ]);
        }

        session()->flash('success', 'تم نشر جميع التعديلات بنجاح!');

        return redirect()->route('admin.portal.pages.customize', $id);
    }

    /**
     * Discard all drafts for the page.
     */
    public function discard(int $id): JsonResponse|RedirectResponse
    {
        $this->portalPageSectionRepository->discardDrafts($id);

        if (request()->ajax()) {
            return new JsonResponse([
                'message' => 'تم التراجع عن مسودات التعديلات.',
            ]);
        }

        session()->flash('success', 'تم التراجع عن مسودات التعديلات.');

        return redirect()->route('admin.portal.pages.customize', $id);
    }
}
