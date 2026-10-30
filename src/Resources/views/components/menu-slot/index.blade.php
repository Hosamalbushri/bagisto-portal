@props([
    'id',
    'label' => null,
    'class' => '',
    'itemClass' => '',
    'direction' => null,
    'maxDepth' => 3,
])

@php
    $channelId = core()->getCurrentChannel()?->id;
    $themeCode = core()->getCurrentChannel()?->theme ?? config('themes.shop-default', 'default');

    $assignment = app(\Platform\Portal\Repositories\PortalMenuSlotAssignmentRepository::class)->findForSlot(
        $id,
        $channelId ?? 0,
        $themeCode,
        app()->getLocale()
    );

    $menu = null;

    if ($assignment && $assignment->status && $assignment->menu && $assignment->menu->status) {
        $menu = portal_menu($assignment->menu->slug, $channelId);
    }
@endphp

@if ($menu && $menu->rootItems && $menu->rootItems->isNotEmpty())
    <div>
        @if (! empty($assignment?->title))
            <p class="font-semibold text-[var(--text-primary)] mb-3 text-sm">
                {{ $assignment->title }}
            </p>
        @endif

        <x-portal::menu
            :menu="$menu"
            :class="$class"
            :item-class="$itemClass"
            :direction="$direction"
            :max-depth="$maxDepth"
            {{ $attributes }}
        />
    </div>
@endif
