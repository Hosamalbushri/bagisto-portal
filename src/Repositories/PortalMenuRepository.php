<?php

namespace Platform\Portal\Repositories;

use Illuminate\Support\Facades\Cache;
use Platform\Portal\Contracts\PortalMenu as PortalMenuContract;
use Webkul\Core\Eloquent\Repository;

class PortalMenuRepository extends Repository
{
    /**
     * Cache version key for atomic menu invalidation.
     */
    public const CACHE_VERSION_KEY = 'portal_menus_cache_version';

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PortalMenuContract::class;
    }

    /**
     * Invalidate all cached menu trees.
     */
    public static function clearCache(): void
    {
        try {
            if (! Cache::has(self::CACHE_VERSION_KEY)) {
                Cache::forever(self::CACHE_VERSION_KEY, 1);
            }

            Cache::increment(self::CACHE_VERSION_KEY);
        } catch (\Throwable) {
            Cache::put(self::CACHE_VERSION_KEY, time(), 86400 * 30);
        }
    }

    /**
     * Create menu and invalidate cache.
     */
    public function create(array $data): mixed
    {
        $record = parent::create($data);
        self::clearCache();

        return $record;
    }

    /**
     * Update menu and invalidate cache.
     */
    public function update(array $data, $id, $attribute = 'id'): mixed
    {
        $record = parent::update($data, $id, $attribute);
        self::clearCache();

        return $record;
    }

    /**
     * Delete menu and invalidate cache.
     */
    public function delete($id): mixed
    {
        $result = parent::delete($id);
        self::clearCache();

        return $result;
    }

    /**
     * Get menu by slug and channel.
     */
    public function findBySlug(string $slug, ?int $channelId = null): ?PortalMenuContract
    {
        return $this->model
            ->where('slug', $slug)
            ->forChannel($channelId)
            ->first();
    }

    /**
     * Get active menu with full recursive items tree loaded for the specified locale.
     * Items without translations in the requested locale are excluded.
     * Results are cached for high-traffic scalability.
     */
    public function getActiveTree(string $slug, ?int $channelId = null, ?string $locale = null): ?PortalMenuContract
    {
        $locale = $locale ?: app()->getLocale();
        $channelKey = $channelId ?? 0;
        $version = Cache::get(self::CACHE_VERSION_KEY, 1);
        $cacheKey = "portal_menu_tree_v{$version}_{$slug}_{$channelKey}_{$locale}";

        return Cache::remember($cacheKey, 86400, function () use ($slug, $channelId, $locale) {
            $menu = $this->model
                ->where('slug', $slug)
                ->forChannel($channelId)
                ->where('status', 1)
                ->with([
                    'rootItems' => function ($query) use ($locale) {
                        $this->scopeTranslatedItems($query, $locale);
                    },
                    'rootItems.translations' => function ($query) use ($locale) {
                        $query->where('locale', $locale);
                    },
                    'rootItems.page.translations' => function ($query) use ($locale) {
                        $query->where('locale', $locale);
                    },
                    'rootItems.children' => function ($query) use ($locale) {
                        $this->scopeTranslatedItems($query, $locale);
                    },
                    'rootItems.children.translations' => function ($query) use ($locale) {
                        $query->where('locale', $locale);
                    },
                    'rootItems.children.page.translations' => function ($query) use ($locale) {
                        $query->where('locale', $locale);
                    },
                    'rootItems.children.children' => function ($query) use ($locale) {
                        $this->scopeTranslatedItems($query, $locale);
                    },
                    'rootItems.children.children.translations' => function ($query) use ($locale) {
                        $query->where('locale', $locale);
                    },
                    'rootItems.children.children.page.translations' => function ($query) use ($locale) {
                        $query->where('locale', $locale);
                    },
                ])
                ->first();

            if (! $menu) {
                return null;
            }

            // If the menu has no root items translated in the requested locale, do not return it
            if ($menu->rootItems->isEmpty()) {
                return null;
            }

            return $menu;
        });
    }

    /**
     * Scope query to active items that have a non-empty translation for the given locale.
     */
    protected function scopeTranslatedItems($query, string $locale): void
    {
        $query->where('status', 1)
            ->where(function ($q) use ($locale) {
                $q->whereHas('translations', function ($t) use ($locale) {
                    $t->where('locale', $locale)
                        ->whereNotNull('title')
                        ->where('title', '!=', '');
                })->orWhere(function ($q2) use ($locale) {
                    $q2->where('type', 'page')
                        ->whereHas('page.translations', function ($t) use ($locale) {
                            $t->where('locale', $locale)
                                ->whereNotNull('title')
                                ->where('title', '!=', '');
                        });
                });
            })
            ->orderBy('sort_order', 'asc');
    }
}
