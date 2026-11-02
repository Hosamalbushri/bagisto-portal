<?php

namespace Platform\Portal\Repositories;

use Illuminate\Support\Facades\DB;
use Platform\Portal\Models\PortalMenuItem;
use Webkul\Core\Eloquent\Repository;

class PortalMenuItemRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PortalMenuItem::class;
    }

    /**
     * Save the entire menu items tree with nested children and translations.
     */
    public function saveTree(int $menuId, array $items, ?string $localeCode = null): array
    {
        return DB::transaction(function () use ($menuId, $items, $localeCode) {
            $persistedIds = [];

            $this->processItems($menuId, $items, null, $persistedIds, $localeCode);

            // Remove any items that were deleted from the tree
            $this->model
                ->where('portal_menu_id', $menuId)
                ->whereNotIn('id', $persistedIds)
                ->delete();

            PortalMenuRepository::clearCache();

            return $persistedIds;
        });
    }

    /**
     * Recursively process menu items.
     */
    protected function processItems(int $menuId, array $items, ?int $parentId, array &$persistedIds, ?string $localeCode = null): void
    {
        foreach ($items as $index => $itemData) {
            $itemId = ! empty($itemData['id']) ? (int) $itemData['id'] : null;

            $attributes = [
                'portal_menu_id' => $menuId,
                'parent_id' => $parentId,
                'type' => $itemData['type'] ?? 'custom',
                'portal_page_id' => ! empty($itemData['portal_page_id']) ? (int) $itemData['portal_page_id'] : null,
                'url' => $this->sanitizeUrl($itemData['url'] ?? null),
                'target' => $itemData['target'] ?? '_self',
                'icon' => $itemData['icon'] ?? null,
                'sort_order' => $index,
                'status' => isset($itemData['status']) ? (bool) $itemData['status'] : true,
            ];

            if ($itemId) {
                $item = $this->model->where('portal_menu_id', $menuId)->find($itemId);
                if ($item) {
                    $item->update($attributes);
                } else {
                    $item = $this->model->create($attributes);
                }
            } else {
                $item = $this->model->create($attributes);
            }

            $persistedIds[] = $item->id;

            // Handle translations
            if (! empty($itemData['translations']) && is_array($itemData['translations'])) {
                foreach ($itemData['translations'] as $locale => $translationData) {
                    $title = is_array($translationData) ? ($translationData['title'] ?? '') : (string) $translationData;

                    if (filled($title)) {
                        $item->translations()->updateOrCreate(
                            ['locale' => $locale],
                            ['title' => $title]
                        );
                    }
                }
            }

            // Also support direct title for current locale
            if (! empty($itemData['title']) && ! empty($localeCode)) {
                $item->translations()->updateOrCreate(
                    ['locale' => $localeCode],
                    ['title' => $itemData['title']]
                );
            }

            // Recurse for nested children
            if (! empty($itemData['children']) && is_array($itemData['children'])) {
                $this->processItems($menuId, $itemData['children'], $item->id, $persistedIds, $localeCode);
            }
        }
    }

    /**
     * Sanitize custom menu item URL to prevent XSS.
     */
    public function sanitizeUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $trimmed = trim($url);

        if ($trimmed === '') {
            return '';
        }

        // Remove all control characters, null bytes, and invisible whitespace
        $cleaned = preg_replace('/[\x00-\x20]+/', '', $trimmed);

        // Strict allowlist: exclusively allow URLs starting with /, #, http://, https://, mailto:, or tel:
        if (! preg_match('/^(\/|#|https?:\/\/|mailto:|tel:)/i', $cleaned)) {
            return '#';
        }

        return $trimmed;
    }
}
