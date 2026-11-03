<?php

namespace Platform\Portal\Repositories;

use Platform\Portal\Contracts\PortalPage as PortalPageContract;
use Webkul\Core\Eloquent\Repository;

class PortalPageRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PortalPageContract::class;
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

    /**
     * Find active home page for given channel.
     */
    public function findHomePage(int $channelId): ?PortalPageContract
    {
        return $this->model
            ->where('channel_id', $channelId)
            ->where('is_home_page', 1)
            ->where('status', 1)
            ->first();
    }

    /**
     * Create page and ensure at most one home page per channel.
     */
    public function create(array $data)
    {
        if (empty($data['status'])) {
            $data['is_home_page'] = 0;
        }

        if (! empty($data['is_home_page']) && ! empty($data['channel_id'])) {
            $this->model
                ->where('channel_id', $data['channel_id'])
                ->update(['is_home_page' => 0]);
        }

        return parent::create($data);
    }

    /**
     * Update page and ensure at most one home page per channel.
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        if (isset($data['status']) && empty($data['status'])) {
            $data['is_home_page'] = 0;
        }

        if (! empty($data['is_home_page'])) {
            $page = $this->find($id);
            $channelId = $data['channel_id'] ?? $page?->channel_id;

            if ($channelId) {
                $this->model
                    ->where('channel_id', $channelId)
                    ->where('id', '!=', $id)
                    ->update(['is_home_page' => 0]);
            }
        }

        return parent::update($data, $id, $attribute);
    }
}
