@props([
    'item',
    'depth' => 0,
    'itemClass' => '',
    'activeClass' => 'text-blue-600 font-semibold',
    'dropdownClass' => '',
])

@php
    $children = $item->children ? $item->children->where('status', 1)->filter(fn ($c) => filled($c->getTitle())) : collect();
    $hasChildren = $children->isNotEmpty();
    $active = $item->isActive();
    $title = $item->getTitle();
    $url = $item->getUrl();
@endphp

<li
    x-data="{ open: false }"
    @mouseenter="open = true"
    @mouseleave="open = false"
    class="relative group {{ $depth > 0 ? 'w-full' : '' }}"
>
    <div class="flex items-center justify-between {{ $depth > 0 ? 'w-full' : '' }}">
        <a
            href="{{ $url }}"
            target="{{ $item->target ?: '_self' }}"
            @if ($item->target === '_blank') rel="noopener noreferrer" @endif
            class="flex items-center gap-1.5 transition-colors duration-150 {{ $depth === 0 ? 'py-2 px-1 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 text-sm font-medium' : 'px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400 w-full' }} {{ $active ? $activeClass : '' }} {{ $itemClass }}"
        >
            @if (! empty($item->icon))
                <i class="{{ $item->icon }} text-xs"></i>
            @endif

            <span>{{ $title }}</span>

            @if ($hasChildren)
                @if ($depth === 0)
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                @else
                    <svg class="w-3.5 h-3.5 ms-auto rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                @endif
            @endif
        </a>
    </div>

    @if ($hasChildren)
        <ul
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-1"
            style="display: none;"
            class="{{ $depth === 0 ? 'absolute start-0 top-full mt-1 min-w-[200px] bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-100 dark:border-gray-800 py-1 z-50' : 'absolute start-full top-0 min-w-[200px] bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-100 dark:border-gray-800 py-1 z-50' }} {{ $dropdownClass }}"
        >
            @foreach ($children as $child)
                <x-portal::menu.item
                    :item="$child"
                    :item-class="$itemClass"
                    :active-class="$activeClass"
                    :dropdown-class="$dropdownClass"
                    :depth="$depth + 1"
                />
            @endforeach
        </ul>
    @endif
</li>

