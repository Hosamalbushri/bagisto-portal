<?php

namespace Platform\Portal\Services;

use Illuminate\Support\Collection;
use Platform\Portal\Contracts\PortalMenuItem as PortalMenuItemContract;

class PortalMenuTreeFilter
{
    /**
     * Recursively filter a menu item tree bottom-up without mutating persisted models.
     *
     * Rules:
     * - Inactive items (status = false) are omitted.
     * - Page items whose referenced page is missing or unpublished are omitted if they have
     *   no visible children, or preserved as non-navigable groups when they have visible children.
     * - URL-only ('custom') and 'route' items are preserved when active.
     */
    public function filterTree(Collection $items, ?string $locale = null): Collection
    {
        return $items
            ->sortBy('sort_order')
            ->map(fn ($item) => $this->filterItem($item, $locale))
            ->filter()
            ->values();
    }

    /**
     * Filter a single menu item and its loaded descendants without mutating the source instance.
     */
    protected function filterItem(PortalMenuItemContract $item, ?string $locale = null): ?PortalMenuItemContract
    {
        if (! (bool) $item->status) {
            return null;
        }

        if ($locale !== null && method_exists($item, 'getTitle') && ! filled($item->getTitle($locale))) {
            return null;
        }

        $loadedChildren = $item->relationLoaded('children')
            ? $item->children
            : collect();

        $visibleChildren = $this->filterTree($loadedChildren, $locale);

        $isNavigable = method_exists($item, 'isNavigable')
            ? $item->isNavigable()
            : ($item->type !== 'page' || ($item->page !== null && (bool) $item->page->status));

        if (! $isNavigable && $visibleChildren->isEmpty()) {
            return null;
        }

        $cloned = clone $item;
        $cloned->setRelation('children', $visibleChildren);

        return $cloned;
    }
}
