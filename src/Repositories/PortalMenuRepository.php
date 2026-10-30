<?php

namespace Platform\Portal\Repositories;

use Platform\Portal\Contracts\PortalMenu as PortalMenuContract;
use Platform\Portal\Models\PortalMenu;
use Webkul\Core\Eloquent\Repository;

class PortalMenuRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PortalMenu::class;
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
     */
    public function getActiveTree(string $slug, ?int $channelId = null, ?string $locale = null): ?PortalMenuContract
    {
        $locale = $locale ?: app()->getLocale();

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
