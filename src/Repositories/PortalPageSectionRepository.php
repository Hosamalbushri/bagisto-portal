<?php

namespace Platform\Portal\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Platform\Portal\Contracts\PortalPageSection as PortalPageSectionContract;
use Platform\Portal\Models\PortalPageSection;
use Webkul\Core\Eloquent\Repository;

class PortalPageSectionRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PortalPageSection::class;
    }

    /**
     * Get active published sections for page.
     */
    public function getRenderableSections(int $pageId): Collection
    {
        return $this->model
            ->where('portal_page_id', $pageId)
            ->where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Get drafted sections for preview in customizer.
     */
    public function getDraftSections(int $pageId, string $locale): Collection
    {
        $sections = $this->model
            ->where('portal_page_id', $pageId)
            ->get();

        return $sections->map(function ($section) use ($locale) {
            $trans = $section->translate($locale);

            if ($trans && ! is_null($trans->draft_options)) {
                $section->options = $trans->draft_options;
            }

            if (! is_null($section->draft_status)) {
                $section->status = $section->draft_status;
            }

            if (! is_null($section->draft_sort_order)) {
                $section->sort_order = $section->draft_sort_order;
            }

            return $section;
        })
            ->filter(fn ($s) => (bool) $s->status)
            ->sortBy('sort_order')
            ->values();
    }

    /**
     * Check if a section has unpublished draft edits.
     */
    public function hasDraft(PortalPageSectionContract $section): bool
    {
        return ! is_null($section->draft_status)
            || ! is_null($section->draft_sort_order)
            || $section->translations->contains(fn ($trans) => ! is_null($trans->draft_options));
    }

    /**
     * Save draft options for a section.
     */
    public function saveDraft(int $id, string $locale, array $options): PortalPageSectionContract
    {
        $section = $this->findOrFail($id);

        $type = $section->getTypeInstance();
        if ($type) {
            $options = $type->sanitize($options);
        }

        $section->translateOrNew($locale)->draft_options = $options;
        $section->save();

        return $section->refresh();
    }

    /**
     * Save draft status for a section.
     */
    public function saveStatusDraft(int $id, bool $status): PortalPageSectionContract
    {
        $section = $this->findOrFail($id);

        $section->draft_status = $status === (bool) $section->status ? null : $status;
        $section->save();

        return $section->refresh();
    }

    /**
     * Save draft sort orders after reordering in editor.
     */
    public function saveOrderDraft(array $sectionIds): void
    {
        foreach (array_values($sectionIds) as $position => $id) {
            $section = $this->find($id);

            if (! $section) {
                continue;
            }

            $order = $position + 1;
            $section->draft_sort_order = $order === (int) $section->sort_order ? null : $order;
            $section->save();
        }
    }

    /**
     * Duplicate a section on the page.
     */
    public function duplicate(int $id): PortalPageSectionContract
    {
        $section = $this->findOrFail($id);
        $maxSort = $this->model->where('portal_page_id', $section->portal_page_id)->max('sort_order') ?? 0;

        $duplicate = $section->replicate();
        $duplicate->name = $section->name.' ('.trans('portal::app.admin.pages.customize.copy').')';
        $duplicate->sort_order = $maxSort + 1;
        $duplicate->draft_status = null;
        $duplicate->draft_sort_order = null;
        $duplicate->save();

        foreach ($section->translations as $trans) {
            $newTrans = $trans->replicate();
            $newTrans->portal_page_section_id = $duplicate->id;
            $newTrans->draft_options = null;
            $newTrans->save();
        }

        return $duplicate->refresh();
    }

    /**
     * Publish all drafts for a page.
     */
    public function publish(int $pageId): void
    {
        DB::transaction(function () use ($pageId) {
            $sections = $this->model->where('portal_page_id', $pageId)->get();

            foreach ($sections as $section) {
                if (! is_null($section->draft_status)) {
                    $section->status = $section->draft_status;
                    $section->draft_status = null;
                }

                if (! is_null($section->draft_sort_order)) {
                    $section->sort_order = $section->draft_sort_order;
                    $section->draft_sort_order = null;
                }

                $section->save();

                foreach ($section->translations as $trans) {
                    if (! is_null($trans->draft_options)) {
                        $trans->options = $trans->draft_options;
                        $trans->draft_options = null;
                        $trans->save();
                    }
                }
            }
        });
    }

    /**
     * Discard all drafts for a page.
     */
    public function discardDrafts(int $pageId): void
    {
        DB::transaction(function () use ($pageId) {
            $sections = $this->model->where('portal_page_id', $pageId)->get();

            foreach ($sections as $section) {
                $section->draft_status = null;
                $section->draft_sort_order = null;
                $section->save();

                foreach ($section->translations as $trans) {
                    $trans->draft_options = null;
                    $trans->save();
                }
            }
        });
    }
}
