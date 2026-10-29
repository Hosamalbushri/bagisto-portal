<?php

namespace Platform\Portal\Repositories;

use Platform\Portal\Contracts\PortalPage as PortalPageContract;
use Platform\Portal\Models\PortalPage;
use Webkul\Core\Eloquent\Repository;

class PortalPageRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PortalPage::class;
    }

    /**
     * Get active page by slug for given channel.
     */
    public function findBySlug(string $slug, int $channelId): ?PortalPageContract
    {
        return $this->model
            ->where('slug', $slug)
            ->where('channel_id', $channelId)
            ->where('status', 1)
            ->first();
    }

    /**
     * Get page for preview by slug for given channel.
     */
    public function findBySlugForPreview(string $slug, int $channelId): ?PortalPageContract
    {
        return $this->model
            ->where('slug', $slug)
            ->where('channel_id', $channelId)
            ->first();
    }
}
