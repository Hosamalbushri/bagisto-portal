{!! view_render_event('bagisto.portal.layout.header.desktop.bottom.before') !!}

@php
    $channel = core()->getCurrentChannel();
    $channelName = $channel->name ?? config('app.name');

    $isShopMode = function_exists('portal_shop_enabled') && portal_shop_enabled();
    $searchRoute = ($isShopMode && Route::has('shop.search.index'))
        ? route('shop.search.index')
        : (Route::has('portal.search.index') ? route('portal.search.index') : null);

    $loginRoute = ($isShopMode && Route::has('shop.customer.session.index'))
        ? route('shop.customer.session.index')
        : (Route::has('portal.auth.login') ? route('portal.auth.login') : null);

    $registerRoute = ($isShopMode && Route::has('shop.customers.register.index'))
        ? route('shop.customers.register.index')
        : (Route::has('portal.auth.register') ? route('portal.auth.register') : null);

    $profileRoute = ($isShopMode && Route::has('shop.customers.account.profile.index'))
        ? route('shop.customers.account.profile.index')
        : (Route::has('customer.account.profile.index') ? route('customer.account.profile.index') : null);

    $ordersRoute = ($isShopMode && Route::has('shop.customers.account.orders.index'))
        ? route('shop.customers.account.orders.index')
        : (Route::has('customer.account.orders.index') ? route('customer.account.orders.index') : null);

    $hasCustomerGuard = array_key_exists('customer', config('auth.guards', []));
    $isCustomerAuth = $hasCustomerGuard && auth()->guard('customer')->check();
    $portalGuard = \Platform\Portal\Helpers\PortalAuth::guard();
    $hasPortalGuard = array_key_exists($portalGuard, config('auth.guards', []));
    $isPortalAuth = \Platform\Portal\Helpers\PortalAuth::isEnabled() && $hasPortalGuard && auth()->guard($portalGuard)->check();
    $currentUser = $isCustomerAuth ? auth()->guard('customer')->user() : ($isPortalAuth ? auth()->guard($portalGuard)->user() : null);

    // Build hierarchical items from assigned menu slot 'header_main' or fallback
    $assignedMenu = portal_menu_slot('header_main');
    $navigationItems = [];

    if ($assignedMenu && $assignedMenu->rootItems && $assignedMenu->rootItems->isNotEmpty()) {
        $buildTree = function ($items) use (&$buildTree) {
            $result = [];
            foreach ($items as $item) {
                if (! $item->status) continue;
                $activeChildren = $item->children ? $item->children->where('status', 1) : collect();
                $result[] = [
                    'id' => $item->id,
                    'name' => $item->getTitle(),
                    'url' => $item->getUrl() ?: '#',
                    'description' => null,
                    'is_active' => $item->isActive(),
                    'children' => $activeChildren->isNotEmpty() ? $buildTree($activeChildren) : [],
                ];
            }
            return $result;
        };

        $navigationItems = $buildTree($assignedMenu->rootItems);
    }

    if (empty($navigationItems) && ! ($isShopMode && Route::has('shop.api.categories.tree'))) {
        $navigationItems = [
            [
                'id' => 'home',
                'name' => trans('portal::app.home.nav.home'),
                'url' => portal_home_url(),
                'is_active' => request()->routeIs('portal.home') || request()->is('/'),
                'children' => [],
            ],
        ];
    }
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16 gap-6">
        {{-- Right Side: Brand Logo & Multi-Level Vue Navigation --}}
        <div class="flex items-center gap-8">
            {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.logo.before') !!}

            <a
                href="{{ portal_home_url() }}"
                aria-label="@lang('portal::app.components.layouts.header.desktop.bottom.bagisto')"
                class="flex items-center gap-2.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 rounded-lg py-1"
            >
                <img
                    src="{{ core()->getCurrentChannel()->logo_url ?? (function_exists('bagisto_asset') ? bagisto_asset('images/logo.svg') : (function_exists('portal_asset') ? portal_asset('images/logo.svg') : '')) }}"
                    width="131"
                    height="29"
                    alt="{{ config('app.name') }}"
                    class="h-7 w-auto"
                >
            </a>

            {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.logo.after') !!}

            {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.category.before') !!}

            {{-- Slot declaration for MenuSlotScanner --}}
            {{-- @portalMenuSlot('header_main', 'Main Navigation', 'header') --}}

            {{-- Bagisto Vue 3 Inline Component for Multi-Level Navigation --}}
            <v-desktop-category :initial-categories='@json($navigationItems)'>
                {{-- Shimmer Skeleton before Vue Mounts --}}
                <div class="flex items-center gap-5">
                    <span class="w-16 h-5 rounded bg-[var(--bg-surface-muted,theme(colors.slate.100))] dark:bg-[var(--bg-surface-muted,theme(colors.slate.800))] animate-pulse"></span>
                    <span class="w-20 h-5 rounded bg-[var(--bg-surface-muted,theme(colors.slate.100))] dark:bg-[var(--bg-surface-muted,theme(colors.slate.800))] animate-pulse"></span>
                    <span class="w-16 h-5 rounded bg-[var(--bg-surface-muted,theme(colors.slate.100))] dark:bg-[var(--bg-surface-muted,theme(colors.slate.800))] animate-pulse"></span>
                    <span class="w-14 h-5 rounded bg-[var(--bg-surface-muted,theme(colors.slate.100))] dark:bg-[var(--bg-surface-muted,theme(colors.slate.800))] animate-pulse"></span>
                </div>
            </v-desktop-category>

            {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.category.after') !!}
        </div>

        {{-- Left Side: Search, Locale/Customer Dropdowns & Actions --}}
        <div class="flex items-center gap-2">
            {{-- Search Trigger --}}
            <form action="{{ $searchRoute ?? portal_home_url() }}" class="flex items-center">
                <div class="relative">
                    <input
                        type="text"
                        name="query"
                        value="{{ request('query') }}"
                        placeholder="@lang('portal::app.components.layouts.header.desktop.bottom.search-text')"
                        class="w-48 xl:w-64 ps-9 pe-3 py-1.5 rounded-lg text-[13px] bg-[var(--bg-surface-elevated,theme(colors.slate.100))] dark:bg-[var(--bg-surface-elevated,theme(colors.slate.800))] border border-[var(--border-subtle,theme(colors.slate.200))] dark:border-[var(--border-subtle,theme(colors.slate.700))] text-[var(--text-primary,theme(colors.slate.800))] dark:text-[var(--text-primary,theme(colors.slate.200))] focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary,theme(colors.slate.900))]"
                    >
                    <span class="icon-search text-base text-[var(--text-muted,theme(colors.slate.400))] absolute top-2 start-3 pointer-events-none"></span>
                </div>
            </form>

            {{-- Bagisto Customer Dropdown (<x-portal::dropdown>) --}}
            <x-portal::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'rtl' ? 'left' : 'right' }}">
                <x-slot:toggle>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[13px] font-medium text-[var(--text-secondary,theme(colors.slate.700))] dark:text-[var(--text-secondary,theme(colors.slate.200))] hover:bg-[var(--bg-surface-elevated,theme(colors.slate.100))] dark:hover:bg-[var(--bg-surface-elevated,theme(colors.slate.800))] transition-colors"
                        aria-label="@lang('portal::app.components.layouts.header.desktop.bottom.profile')"
                    >
                        <span class="icon-users text-lg"></span>
                        <span>@lang('portal::app.components.layouts.header.desktop.bottom.profile')</span>
                    </button>
                </x-slot:toggle>

                @if (! $currentUser)
                    <x-slot:content>
                        <div class="p-4 w-64 bg-[var(--bg-surface,#ffffff)] dark:bg-[var(--bg-surface,#111624)] rounded-xl border border-[var(--border-subtle,theme(colors.slate.200))] dark:border-[var(--border-subtle,theme(colors.slate.800))]">
                            <p class="text-sm font-semibold text-[var(--text-primary,theme(colors.slate.900))] dark:text-white">
                                @lang('portal::app.components.layouts.header.desktop.bottom.welcome-guest')
                            </p>
                            <p class="text-xs text-[var(--text-muted,theme(colors.slate.500))] mt-1">
                                @lang('portal::app.components.layouts.header.desktop.bottom.dropdown-text')
                            </p>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <a
                                    href="{{ $loginRoute ?? '#' }}"
                                    class="py-2 px-3 rounded-lg text-xs font-medium bg-[var(--brand-primary,theme(colors.slate.900))] text-white text-center hover:opacity-90 transition-opacity"
                                >
                                    @lang('portal::app.components.layouts.header.desktop.bottom.sign-in')
                                </a>
                                <a
                                    href="{{ $registerRoute ?? '#' }}"
                                    class="py-2 px-3 rounded-lg text-xs font-medium border border-[var(--border-subtle,theme(colors.slate.200))] text-[var(--text-secondary,theme(colors.slate.700))] text-center hover:bg-[var(--bg-surface-elevated,theme(colors.slate.50))] transition-colors"
                                >
                                    @lang('portal::app.components.layouts.header.desktop.bottom.sign-up')
                                </a>
                            </div>
                        </div>
                    </x-slot:content>
                @else
                    <x-slot:content class="!p-1.5">
                        <div class="w-52 space-y-0.5 bg-[var(--bg-surface,#ffffff)] dark:bg-[var(--bg-surface,#111624)] rounded-xl border border-[var(--border-subtle,theme(colors.slate.200))] dark:border-[var(--border-subtle,theme(colors.slate.800))] p-1">
                            <a
                                href="{{ $profileRoute ?? '#' }}"
                                class="block px-3 py-2 rounded-lg text-[13px] text-[var(--text-secondary,theme(colors.slate.700))] hover:bg-[var(--bg-surface-elevated,theme(colors.slate.100))] transition-colors"
                            >
                                @lang('portal::app.components.layouts.header.desktop.bottom.profile')
                            </a>
                            <a
                                href="{{ $ordersRoute ?? '#' }}"
                                class="block px-3 py-2 rounded-lg text-[13px] text-[var(--text-secondary,theme(colors.slate.700))] hover:bg-[var(--bg-surface-elevated,theme(colors.slate.100))] transition-colors"
                            >
                                @lang('portal::app.components.layouts.header.desktop.bottom.orders')
                            </a>
                        </div>
                    </x-slot:content>
                @endif
            </x-portal::dropdown>
        </div>
    </div>
</div>

{!! view_render_event('bagisto.portal.layout.header.desktop.bottom.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-desktop-category-template"
    >
        <div
            class="flex items-center gap-5"
            v-if="isLoading"
        >
            <span class="w-16 h-5 rounded bg-slate-100 dark:bg-slate-800 animate-pulse"></span>
            <span class="w-20 h-5 rounded bg-slate-100 dark:bg-slate-800 animate-pulse"></span>
            <span class="w-16 h-5 rounded bg-slate-100 dark:bg-slate-800 animate-pulse"></span>
        </div>

        <nav
            v-else
            aria-label="@lang('portal::app.components.layouts.header.desktop.bottom.categories')"
            @keydown.escape="closeAll()"
        >
            <ul role="list" class="flex items-center gap-1">
                {{-- Level 1 Item --}}
                <li
                    v-for="(category, index) in categories"
                    :key="category.id"
                    class="relative"
                    @mouseenter="onMouseEnter(category.id)"
                    @mouseleave="onMouseLeave()"
                >
                    {{-- Level 1 Trigger / Link --}}
                    <div class="flex items-center">
                        <a
                            :href="sanitizeUrl(category.url)"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-[14px] font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/70 dark:hover:bg-slate-800/60 transition-colors"
                            :class="{ '!text-[var(--brand-primary)] font-bold': category.is_active || isActiveUrl(category.url) }"
                            :aria-expanded="category.children && category.children.length ? (openCategoryId === category.id ? 'true' : 'false') : undefined"
                            @click="category.children && category.children.length ? toggleCategory($event, category.id) : null"
                        >
                            <span v-text="category.name"></span>

                            <svg
                                v-if="category.children && category.children.length"
                                class="w-3.5 h-3.5 text-slate-400 transition-transform duration-150"
                                :class="{ 'rotate-180': openCategoryId === category.id }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.75"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </a>
                    </div>

                    {{-- Multi-Level Split-Pane Panel (Level 2 Rail + Level 3 Grid + Level 4 Links) --}}
                    <div
                        v-if="category.children && category.children.length"
                        v-show="openCategoryId === category.id"
                        class="absolute top-full mt-2 start-0 w-[720px] max-w-[92vw] bg-[var(--bg-surface,#ffffff)] dark:bg-[var(--bg-surface,#111624)] rounded-xl shadow-[0_16px_40px_-12px_rgba(15,23,42,0.12)] border border-[var(--border-subtle,theme(colors.slate.200))]/90 dark:border-[var(--border-subtle,theme(colors.slate.800))] overflow-hidden z-50 transition-all duration-150"
                    >
                        <div class="grid grid-cols-12">
                            {{-- Level 2: Category Switcher Rail --}}
                            <div class="col-span-4 bg-[var(--bg-surface-elevated,theme(colors.slate.50))]/80 dark:bg-[var(--bg-surface-elevated,theme(colors.slate.900))]/50 border-e border-[var(--border-subtle,theme(colors.slate.200))]/70 dark:border-[var(--border-subtle,theme(colors.slate.800))] p-2.5">
                                <ul role="list" class="space-y-0.5">
                                    <li
                                        v-for="secondLevel in category.children"
                                        :key="secondLevel.id"
                                    >
                                        <a
                                            :href="sanitizeUrl(secondLevel.url)"
                                            @mouseenter="activeSecondLevelId = secondLevel.id"
                                            @focus="activeSecondLevelId = secondLevel.id"
                                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-[13px] font-medium transition-colors"
                                            :class="getActiveSecondLevel(category).id === secondLevel.id
                                                ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-2xs border border-slate-200/60 dark:border-slate-700/60'
                                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 hover:bg-slate-100/80 border border-transparent'"
                                        >
                                            <span v-text="secondLevel.name"></span>

                                            <span
                                                v-if="secondLevel.children && secondLevel.children.length"
                                                class="icon-arrow-left rtl:icon-arrow-left ltr:icon-arrow-right text-xs text-slate-400"
                                            ></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            {{-- Level 3 & Level 4 Content Area --}}
                            <div
                                class="col-span-8 p-5"
                                v-if="getActiveSecondLevel(category)"
                            >
                                <div class="pb-2 mb-3 border-b border-[var(--border-subtle,theme(colors.slate.100))] dark:border-[var(--border-subtle,theme(colors.slate.800))] flex items-center justify-between">
                                    <span
                                        class="text-[11px] font-semibold tracking-wider text-[var(--text-muted,theme(colors.slate.400))] uppercase"
                                        v-text="getActiveSecondLevel(category).name"
                                    ></span>
                                    <a
                                        :href="sanitizeUrl(getActiveSecondLevel(category).url)"
                                        class="text-[12px] font-medium text-[var(--text-secondary,theme(colors.slate.600))] hover:text-[var(--text-primary,theme(colors.slate.900))] dark:text-slate-400 dark:hover:text-white"
                                    >
                                        عرض الكل &larr;
                                    </a>
                                </div>

                                <div class="grid grid-cols-2 gap-3" v-if="getActiveSecondLevel(category).children && getActiveSecondLevel(category).children.length">
                                    <div
                                        v-for="thirdLevel in getActiveSecondLevel(category).children"
                                        :key="thirdLevel.id"
                                        class="p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                                    >
                                        <a
                                            :href="sanitizeUrl(thirdLevel.url)"
                                            class="text-[13px] font-semibold text-slate-900 dark:text-white hover:text-[var(--brand-primary,theme(colors.blue.600))] transition-colors block"
                                            v-text="thirdLevel.name"
                                        ></a>

                                        <p
                                            v-if="thirdLevel.description"
                                            class="text-[12px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug"
                                            v-text="thirdLevel.description"
                                        ></p>

                                        {{-- Optional Level 4 Links --}}
                                        <ul
                                            v-if="thirdLevel.children && thirdLevel.children.length"
                                            role="list"
                                            class="flex flex-wrap items-center gap-1.5 mt-2"
                                        >
                                            <li
                                                v-for="fourthLevel in thirdLevel.children"
                                                :key="fourthLevel.id"
                                            >
                                                <a
                                                    :href="sanitizeUrl(fourthLevel.url)"
                                                    class="text-[11px] font-medium text-slate-500 hover:text-[var(--brand-primary,theme(colors.blue.600))] bg-slate-100/80 dark:bg-slate-800 px-2 py-0.5 rounded transition-colors"
                                                    v-text="fourthLevel.name"
                                                ></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                {{-- If level 2 has no subchildren, show direct link --}}
                                <div v-else class="py-6 text-center text-xs text-[var(--text-muted,theme(colors.slate.400))]">
                                    <a
                                        :href="sanitizeUrl(getActiveSecondLevel(category).url)"
                                        class="inline-flex items-center gap-1 text-[13px] font-medium text-[var(--brand-primary,theme(colors.blue.600))] hover:underline"
                                    >
                                        <span v-text="getActiveSecondLevel(category).name"></span> &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </nav>
    </script>

    <script type="module">
        app.component('v-desktop-category', {
            template: '#v-desktop-category-template',

            props: {
                initialCategories: {
                    type: Array,
                    default: () => [],
                },
            },

            data() {
                return {
                    isLoading: ! (this.initialCategories && this.initialCategories.length),
                    categories: (this.initialCategories && this.initialCategories.length) ? this.initialCategories : [],
                    openCategoryId: null,
                    activeSecondLevelId: null,
                    hoverOpenTimer: null,
                    hoverCloseTimer: null,
                };
            },

            mounted() {
                if (! this.categories || ! this.categories.length) {
                    this.getCategories();
                }

                document.addEventListener('click', this.handleOutsideClick);
            },

            beforeUnmount() {
                document.removeEventListener('click', this.handleOutsideClick);
            },

            methods: {
                getCategories() {
                    const url = "{{ ($isShopMode && Route::has('shop.api.categories.tree')) ? route('shop.api.categories.tree') : '' }}";
                    if (! url) {
                        this.isLoading = false;
                        return;
                    }

                    this.$axios.get(url)
                        .then(response => {
                            this.isLoading = false;
                            this.categories = response.data.data;
                        })
                        .catch(error => {
                            this.isLoading = false;
                            console.error(error);
                        });
                },

                sanitizeUrl(url) {
                    if (! url || typeof url !== 'string') {
                        return '#';
                    }

                    const trimmed = url.trim();

                    return /^(javascript|data|vbscript):/i.test(trimmed) ? '#' : trimmed;
                },

                isActiveUrl(url) {
                    if (! url || url === '#' || url.startsWith('javascript:')) return false;
                    try {
                        const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
                        const targetUrl = new URL(url, window.location.origin);
                        const targetPath = targetUrl.pathname.replace(/\/+$/, '') || '/';
                        return currentPath === targetPath;
                    } catch (e) {
                        return false;
                    }
                },

                onMouseEnter(categoryId) {
                    if (this.hoverCloseTimer) {
                        clearTimeout(this.hoverCloseTimer);
                        this.hoverCloseTimer = null;
                    }

                    if (this.openCategoryId === categoryId) {
                        return;
                    }

                    if (this.hoverOpenTimer) {
                        clearTimeout(this.hoverOpenTimer);
                    }

                    // NN/g 180ms Hover Intent Debounce
                    this.hoverOpenTimer = setTimeout(() => {
                        this.openCategoryId = categoryId;
                        this.activeSecondLevelId = null;
                    }, 180);
                },

                onMouseLeave() {
                    if (this.hoverOpenTimer) {
                        clearTimeout(this.hoverOpenTimer);
                        this.hoverOpenTimer = null;
                    }

                    // NN/g 250ms Grace Buffer for Diagonal Cursor Trajectory
                    this.hoverCloseTimer = setTimeout(() => {
                        this.openCategoryId = null;
                    }, 250);
                },

                toggleCategory(event, categoryId) {
                    event.preventDefault();
                    event.stopPropagation();

                    this.openCategoryId = this.openCategoryId === categoryId ? null : categoryId;
                },

                getActiveSecondLevel(category) {
                    if (! category.children || ! category.children.length) {
                        return null;
                    }

                    return category.children.find(c => c.id === this.activeSecondLevelId) || category.children[0];
                },

                closeAll() {
                    this.openCategoryId = null;
                },

                handleOutsideClick(event) {
                    if (this.$el && ! this.$el.contains(event.target)) {
                        this.closeAll();
                    }
                },
            },
        });
    </script>
@endPushOnce
