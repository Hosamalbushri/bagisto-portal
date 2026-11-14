@props([
    'item',
    'depth' => 0,
    'direction' => 'row',
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
    $viewAllText = trans()->has('portal::app.components.layouts.header.mobile.view-all')
        ? trans('portal::app.components.layouts.header.mobile.view-all', ['title' => $title])
        : "عرض: {$title}";
@endphp

@if ($direction === 'column')
    {{-- ==================== MOBILE / VERTICAL ACCORDION TREE ==================== --}}
    @if ($hasChildren)
        <li class="w-full">
            <details class="group/menu-accordion w-full">
                <summary class="list-none flex items-center justify-between w-full px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 cursor-pointer select-none {{ $active ? 'text-[var(--brand-primary)] bg-zinc-100 dark:bg-zinc-800 font-semibold' : 'text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800/70' }} {{ $itemClass }}">
                    <div class="flex items-center gap-2.5 min-w-0">
                        @if (! empty($item->icon))
                            <i class="{{ $item->icon }} text-base text-zinc-400"></i>
                        @elseif ($depth === 0)
                            <span class="w-2 h-2 rounded-full bg-[var(--brand-primary)] shadow-xs"></span>
                        @else
                            <span class="w-1.5 h-1.5 rounded-full bg-zinc-300 dark:bg-zinc-600"></span>
                        @endif

                        <span class="truncate font-medium">{{ $title }}</span>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 border border-zinc-200/70 dark:border-zinc-700/70">
                            {{ $children->count() }}
                        </span>

                        <svg class="w-4 h-4 text-zinc-400 transition-transform duration-200 group-open/menu-accordion:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </summary>

                <div class="mt-1 ms-4 ps-3 border-s-2 border-indigo-200/80 dark:border-indigo-900/60 space-y-1 py-1">
                    @if ($url && $url !== '#')
                        <a
                            href="{{ $url }}"
                            target="{{ $item->target ?: '_self' }}"
                            @if ($item->target === '_blank') rel="noopener noreferrer" @endif
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold text-[var(--brand-primary)] hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                        >
                            <span>{{ $viewAllText }}</span>
                        </a>
                    @endif

                    <ul class="space-y-1">
                        @foreach ($children as $child)
                            <x-portal::menu.item
                                :item="$child"
                                :item-class="$itemClass"
                                :active-class="$activeClass"
                                :dropdown-class="$dropdownClass"
                                :direction="$direction"
                                :depth="$depth + 1"
                            />
                        @endforeach
                    </ul>
                </div>
            </details>
        </li>
    @else
        <li class="w-full">
            <a
                href="{{ $url }}"
                target="{{ $item->target ?: '_self' }}"
                @if ($item->target === '_blank') rel="noopener noreferrer" @endif
                class="flex items-center gap-2.5 w-full px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 {{ $active ? '!text-[var(--brand-primary)] font-bold bg-zinc-100 dark:bg-zinc-800' : 'text-zinc-700 dark:text-zinc-200 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800' }} {{ $itemClass }}"
            >
                @if (! empty($item->icon))
                    <i class="{{ $item->icon }} text-base text-zinc-400"></i>
                @elseif ($depth > 0)
                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-300 dark:bg-zinc-600"></span>
                @endif

                <span class="truncate">{{ $title }}</span>
            </a>
        </li>
    @endif

@else
    {{-- ==================== DESKTOP / HORIZONTAL MULTI-LEVEL DOCK ==================== --}}
    @if ($depth === 0)
        {{-- LEVEL 0: Header Root Bar Item --}}
        <li class="relative group/nav">
            <a
                href="{{ $url }}"
                target="{{ $item->target ?: '_self' }}"
                @if ($item->target === '_blank') rel="noopener noreferrer" @endif
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-medium transition-all duration-200 select-none {{ $active ? '!text-[var(--brand-primary)] font-bold bg-zinc-100/90 dark:bg-zinc-800/90 shadow-2xs' : 'text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/70 dark:hover:bg-zinc-800/70' }} {{ $itemClass }}"
            >
                @if (! empty($item->icon))
                    <i class="{{ $item->icon }} text-base opacity-75 group-hover/nav:opacity-100 group-hover/nav:text-[var(--brand-primary)] transition-colors"></i>
                @endif

                <span>{{ $title }}</span>

                @if ($hasChildren)
                    <span class="w-4 h-4 rounded-md flex items-center justify-center text-zinc-400 group-hover/nav:text-[var(--brand-primary)] group-hover/nav:rotate-180 transition-all duration-300 ease-out">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
            </a>

            @if ($hasChildren)
                {{-- Dropdown Container with Safe Hover Bridge --}}
                <div class="invisible opacity-0 pointer-events-none translate-y-2 group-hover/nav:visible group-hover/nav:opacity-100 group-hover/nav:pointer-events-auto group-hover/nav:translate-y-0 transition-all duration-200 ease-out absolute start-0 top-full pt-2 z-50 {{ $dropdownClass }}">
                    <div class="min-w-[240px] max-w-[280px] p-2 rounded-2xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-2xl shadow-zinc-950/10 dark:shadow-black/60 ring-1 ring-black/[0.04] dark:ring-white/[0.06]">
                        {{-- Top Micro Accent Line --}}
                        <div class="h-1 w-10 mx-auto rounded-full bg-gradient-to-r from-[var(--brand-primary)] to-indigo-500 mb-1.5 opacity-80"></div>

                        <ul class="space-y-1">
                            @foreach ($children as $child)
                                <x-portal::menu.item
                                    :item="$child"
                                    :item-class="$itemClass"
                                    :active-class="$activeClass"
                                    :dropdown-class="$dropdownClass"
                                    :direction="$direction"
                                    :depth="$depth + 1"
                                />
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </li>

    @else
        {{-- LEVEL 1+: Sub-Level Item in Floating Dock --}}
        <li class="relative group/sub w-full">
            <a
                href="{{ $url }}"
                target="{{ $item->target ?: '_self' }}"
                @if ($item->target === '_blank') rel="noopener noreferrer" @endif
                class="group/item flex items-center justify-between w-full px-3 py-2 rounded-xl text-sm font-medium transition-all duration-150 hover:translate-x-0.5 rtl:hover:-translate-x-0.5 {{ $active ? 'bg-zinc-100 dark:bg-zinc-800 text-[var(--brand-primary)] font-semibold' : 'text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100/80 dark:hover:bg-zinc-800/80' }}"
            >
                <div class="flex items-center gap-2.5 min-w-0">
                    @if (! empty($item->icon))
                        <span class="w-6 h-6 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 group-hover/item:bg-[var(--brand-primary)] group-hover/item:text-white transition-colors shrink-0">
                            <i class="{{ $item->icon }} text-xs"></i>
                        </span>
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-300 dark:bg-zinc-600 group-hover/item:bg-[var(--brand-primary)] group-hover/item:scale-125 transition-all shrink-0"></span>
                    @endif

                    <span class="truncate">{{ $title }}</span>
                </div>

                @if ($hasChildren)
                    <span class="w-5 h-5 rounded-md bg-zinc-100/80 dark:bg-zinc-800/80 flex items-center justify-center text-zinc-400 group-hover/item:bg-[var(--brand-primary-light,#eff6ff)] group-hover/item:text-[var(--brand-primary)] transition-all shrink-0">
                        <svg class="w-3.5 h-3.5 rtl:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
            </a>

            @if ($hasChildren)
                {{-- Nested Sub-Level Dock with Safe Hover Bridge --}}
                <div class="invisible opacity-0 pointer-events-none rtl:translate-x-2 ltr:-translate-x-2 group-hover/sub:visible group-hover/sub:opacity-100 group-hover/sub:pointer-events-auto group-hover/sub:translate-x-0 transition-all duration-200 ease-out absolute start-full top-0 ps-2 z-50 {{ $dropdownClass }}">
                    <div class="min-w-[220px] max-w-[260px] p-2 rounded-2xl bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl border border-zinc-200/80 dark:border-zinc-800/80 shadow-2xl shadow-zinc-950/15 dark:shadow-black/70 ring-1 ring-black/[0.04] dark:ring-white/[0.06]">
                        {{-- Parent Context Header --}}
                        <div class="px-3 py-1 mb-1.5 border-b border-zinc-100 dark:border-zinc-800 text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 truncate">
                            {{ $title }}
                        </div>

                        <ul class="space-y-1">
                            @foreach ($children as $child)
                                <x-portal::menu.item
                                    :item="$child"
                                    :item-class="$itemClass"
                                    :active-class="$activeClass"
                                    :dropdown-class="$dropdownClass"
                                    :direction="$direction"
                                    :depth="$depth + 1"
                                />
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </li>
    @endif
@endif
