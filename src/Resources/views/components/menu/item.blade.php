@props([
    'item',
    'depth' => 0,
    'itemClass' => '',
    'activeClass' => 'text-[var(--brand-primary)] font-semibold',
    'dropdownClass' => '',
])

@php
    $children = $item->children ? $item->children->where('status', 1)->filter(fn ($c) => filled($c->getTitle())) : collect();
    $hasChildren = $children->isNotEmpty();
    $active = $item->isActive();
    $title = $item->getTitle();
    $url = $item->getUrl();
@endphp

<li class="relative group {{ $depth > 0 ? 'w-full' : '' }}">
    <div class="flex items-center justify-between {{ $depth > 0 ? 'w-full' : '' }}">
        <a
            href="{{ $url }}"
            target="{{ $item->target ?: '_self' }}"
            @if ($item->target === '_blank') rel="noopener noreferrer" @endif
            class="flex items-center gap-1.5 transition-colors duration-150 {{ $depth === 0 ? 'py-2 px-1 text-[var(--text-secondary)] hover:text-[var(--brand-primary)] text-sm font-medium' : 'px-4 py-2 text-sm text-[var(--text-secondary)] hover:bg-[var(--bg-surface-muted)] hover:text-[var(--brand-primary)] w-full' }} {{ $active ? $activeClass : '' }} {{ $itemClass }}"
        >
            @if (! empty($item->icon))
                <i class="{{ $item->icon }} text-xs"></i>
            @endif

            <span>{{ $title }}</span>

            @if ($hasChildren)
                @if ($depth === 0)
                    <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:rotate-180 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                @else
                    <svg class="w-3.5 h-3.5 ms-auto rtl:rotate-180 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                @endif
            @endif
        </a>
    </div>

    @if ($hasChildren)
        <ul
            class="hidden group-hover:block {{ $depth === 0 ? 'absolute start-0 top-full mt-1 min-w-[200px] bg-[var(--bg-surface)] rounded-xl shadow-[var(--shadow-card)] border border-[var(--border-subtle)] py-1.5 z-50' : 'absolute start-full top-0 min-w-[200px] bg-[var(--bg-surface)] rounded-xl shadow-[var(--shadow-card)] border border-[var(--border-subtle)] py-1.5 z-50' }} {{ $dropdownClass }}"
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
