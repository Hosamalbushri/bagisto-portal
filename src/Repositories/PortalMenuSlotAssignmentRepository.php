<?php

namespace Platform\Portal\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Platform\Portal\Contracts\PortalMenuSlotAssignment as PortalMenuSlotAssignmentContract;
use Webkul\Core\Eloquent\Repository;

class PortalMenuSlotAssignmentRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return PortalMenuSlotAssignmentContract::class;
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
     * Cached with versioning for high concurrency.
     */
    public function findForSlot(string $slotId, int $channelId, string $themeCode = 'default', ?string $localeCode = null): ?PortalMenuSlotAssignmentContract
    {
        $version = Cache::get(PortalMenuRepository::CACHE_VERSION_KEY, 1);
        $cacheKey = "portal_slot_v{$version}_{$slotId}_{$channelId}_{$themeCode}_".($localeCode ?? 'none');

        return Cache::remember($cacheKey, now()->addDay(), function () use ($slotId, $channelId, $themeCode, $localeCode) {
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
                $assignment = $this->findOneWhere([
                    'slot_id' => $slotId,
                    'channel_id' => $channelId,
                    'theme_code' => $themeCode,
                    'locale_code' => $defaultLocaleCode,
                ]);

                if ($assignment) {
                    return $assignment;
                }
            }

            // Fallback Level 1: Theme fallback to 'default' if current theme is not 'default'
            if ($themeCode !== 'default') {
                $defaultThemeAssignment = $this->findOneWhere([
                    'slot_id' => $slotId,
                    'channel_id' => $channelId,
                    'theme_code' => 'default',
                    'status' => 1,
                ]);

                if ($defaultThemeAssignment && $defaultThemeAssignment->portal_menu_id) {
                    return $defaultThemeAssignment;
                }
            }

            // Fallback Level 2: Any active assignment on this channel for this slot
            $anyThemeAssignment = $this->findOneWhere([
                'slot_id' => $slotId,
                'channel_id' => $channelId,
                'status' => 1,
            ]);

            if ($anyThemeAssignment && $anyThemeAssignment->portal_menu_id) {
                return $anyThemeAssignment;
            }

            // Fallback Level 3: Fallback to default channel if not already default channel
            $defaultChannel = method_exists(core(), 'getDefaultChannel')
                ? core()->getDefaultChannel()
                : (method_exists(core(), 'getCurrentChannel') ? core()->getCurrentChannel() : null);
            $defaultChannelId = $defaultChannel?->id;

            if ($defaultChannelId && $channelId !== $defaultChannelId) {
                $defaultChannelAssignment = $this->findOneWhere([
                    'slot_id' => $slotId,
                    'channel_id' => $defaultChannelId,
                    'status' => 1,
                ]);

                if ($defaultChannelAssignment && $defaultChannelAssignment->portal_menu_id) {
                    return $defaultChannelAssignment;
                }
            }

            return null;
        });
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

        PortalMenuRepository::clearCache();
    }
}
