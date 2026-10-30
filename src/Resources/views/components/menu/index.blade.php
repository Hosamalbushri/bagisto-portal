@props([
    'slug' => null,
    'menu' => null,
    'channelId' => null,
    'direction' => 'row',
    'class' => '',
    'listClass' => null,
    'itemClass' => '',
    'activeClass' => 'text-blue-600 font-semibold',
    'dropdownClass' => '',
])

@php
    $menuInstance = $menu;

    if (! $menuInstance && $slug) {
        $menuInstance = portal_menu($slug, $channelId);
    }

    if (! $listClass) {
        $listClass = $direction === 'column'
            ? 'flex flex-col space-y-2.5 text-sm'
            : 'flex items-center gap-6 text-sm';
    }
@endphp

@if ($menuInstance && $menuInstance->rootItems && $menuInstance->rootItems->isNotEmpty())
    <nav {{ $attributes->merge(['class' => 'portal-navigation ' . $class]) }} aria-label="{{ $menuInstance->name }}">
        <ul class="{{ $listClass }}">
            @foreach ($menuInstance->rootItems as $item)
                @if (filled($item->getTitle()))
                    <x-portal::menu.item
                        :item="$item"
                        :item-class="$itemClass"
                        :active-class="$activeClass"
                        :dropdown-class="$dropdownClass"
                        :depth="0"
                    />
                @endif
            @endforeach
        </ul>
    </nav>
@endif
