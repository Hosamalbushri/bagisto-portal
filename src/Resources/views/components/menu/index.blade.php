@props([
    'slug' => null,
    'menu' => null,
    'channelId' => null,
    'class' => '',
    'listClass' => 'flex items-center gap-6',
    'itemClass' => '',
    'activeClass' => 'text-blue-600 font-semibold',
    'dropdownClass' => '',
])

@php
    $menuInstance = $menu;

    if (! $menuInstance && $slug) {
        $menuInstance = portal_menu($slug, $channelId);
    }
@endphp

@if ($menuInstance && $menuInstance->rootItems && $menuInstance->rootItems->isNotEmpty())
    <nav {{ $attributes->merge(['class' => 'portal-navigation ' . $class]) }} aria-label="{{ $menuInstance->name }}">
        <ul class="{{ $listClass }}">
            @foreach ($menuInstance->rootItems as $item)
                <x-portal::menu.item
                    :item="$item"
                    :item-class="$itemClass"
                    :active-class="$activeClass"
                    :dropdown-class="$dropdownClass"
                    :depth="0"
                />
            @endforeach
        </ul>
    </nav>
@endif

