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
     * Get all slot assignments for a given channel, theme and optional locale, keyed by slot_id.
     */
    public function getAssignments(int $channelId, string $themeCode = 'default', ?string $localeCode = null): Collection
    {
        // 1. Fetch channel-wide assignments without specific locale
        $defaultAssignments = $this->findWhere([
            'channel_id' => $channelId,
            'theme_code' => $themeCode,
            'locale_code' => null,
        ])->keyBy('slot_id');

        // If no unlocalized records exist, check the channel's default locale
        if ($defaultAssignments->isEmpty()) {
            $defaultLocaleCode = core()->getCurrentChannel()?->default_locale?->code;

            if ($defaultLocaleCode && $defaultLocaleCode !== $localeCode) {
                $defaultAssignments = $this->findWhere([
                    'channel_id' => $channelId,
                    'theme_code' => $themeCode,
                    'locale_code' => $defaultLocaleCode,
                ])->keyBy('slot_id');
            }
        }

        if (! $localeCode) {
            return $defaultAssignments;
        }

        // 2. Fetch specific locale assignments
        $localeAssignments = $this->findWhere([
            'channel_id' => $channelId,
            'theme_code' => $themeCode,
            'locale_code' => $localeCode,
        ])->keyBy('slot_id');

        // 3. Specific locale assignments override defaults
        return $defaultAssignments->replace($localeAssignments);
    }

    /**
     * Find single assignment for slot, with locale fallback.
     */
    public function findForSlot(string $slotId, int $channelId, string $themeCode = 'default', ?string $localeCode = null): ?PortalMenuSlotAssignment
    {
        if ($localeCode) {
            $assignment = $this->findOneWhere([
                'slot_id' => $slotId,
                'channel_id' => $channelId,
                'theme_code' => $themeCode,
                'locale_code' => $localeCode,
            ]);

            if ($assignment) {
                return $assignment;
            }
        }

        $fallback = $this->findOneWhere([
            'slot_id' => $slotId,
            'channel_id' => $channelId,
            'theme_code' => $themeCode,
            'locale_code' => null,
        ]);

        if ($fallback) {
            return $fallback;
        }

        $defaultLocaleCode = core()->getCurrentChannel()?->default_locale?->code;

        if ($defaultLocaleCode && $defaultLocaleCode !== $localeCode) {
            return $this->findOneWhere([
                'slot_id' => $slotId,
                'channel_id' => $channelId,
                'theme_code' => $themeCode,
                'locale_code' => $defaultLocaleCode,
            ]);
        }

        return null;
    }

    /**
     * Save or update slot assignments in bulk.
     *
     * @param  array<string, mixed>  $slotsData  [slot_id => ['portal_menu_id' => ..., 'title' => ..., 'status' => ...]]
     */
    public function saveAssignments(array $slotsData, int $channelId, string $themeCode = 'default', ?string $localeCode = null): void
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
                    'locale_code' => $localeCode,
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
