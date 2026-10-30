<?php

namespace Platform\Portal\Repositories;

use Illuminate\Support\Collection;
use Platform\Portal\Contracts\PortalMenuSlotAssignment;
use Webkul\Core\Eloquent\Repository;

class PortalMenuSlotAssignmentRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PortalMenuSlotAssignment::class;
    }

    /**
     * Get all slot assignments for a given channel and theme, keyed by slot_id.
     */
    public function getAssignments(int $channelId, string $themeCode = 'default'): Collection
    {
        return $this->findWhere([
            'channel_id' => $channelId,
            'theme_code' => $themeCode,
        ])->keyBy('slot_id');
    }

    /**
     * Find single assignment for slot.
     */
    public function findForSlot(string $slotId, int $channelId, string $themeCode = 'default'): ?PortalMenuSlotAssignment
    {
        return $this->findOneWhere([
            'slot_id' => $slotId,
            'channel_id' => $channelId,
            'theme_code' => $themeCode,
        ]);
    }

    /**
     * Save or update slot assignments in bulk.
     *
     * @param  array<string, mixed>  $slotsData  [slot_id => ['menu_id' => ..., 'title' => ..., 'status' => ...]]
     */
    public function saveAssignments(array $slotsData, int $channelId, string $themeCode = 'default'): void
    {
        foreach ($slotsData as $slotId => $data) {
            $menuId = ! empty($data['portal_menu_id']) ? (int) $data['portal_menu_id'] : null;
            $location = $data['location'] ?? 'footer';
            $title = $data['title'] ?? null;
            $status = isset($data['status']) ? (bool) $data['status'] : true;

            $this->updateOrCreate(
                [
                    'slot_id' => $slotId,
                    'channel_id' => $channelId,
                    'theme_code' => $themeCode,
                ],
                [
                    'location' => $location,
                    'portal_menu_id' => $menuId,
                    'title' => $title,
                    'status' => $status,
                ]
            );
        }
    }
}
