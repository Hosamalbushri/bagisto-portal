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
     * Get active menu with full recursive items tree loaded.
     */
    public function getActiveTree(string $slug, ?int $channelId = null): ?PortalMenuContract
    {
        return $this->model
            ->where('slug', $slug)
            ->forChannel($channelId)
            ->where('status', 1)
            ->with([
                'rootItems' => function ($query) {
                    $query->where('status', 1)->orderBy('sort_order', 'asc');
                },
                'rootItems.translations',
                'rootItems.page.translations',
                'rootItems.children' => function ($query) {
                    $query->where('status', 1)->orderBy('sort_order', 'asc');
                },
                'rootItems.children.translations',
                'rootItems.children.page.translations',
                'rootItems.children.children' => function ($query) {
                    $query->where('status', 1)->orderBy('sort_order', 'asc');
                },
                'rootItems.children.children.translations',
                'rootItems.children.children.page.translations',
            ])
            ->first();
    }
}
