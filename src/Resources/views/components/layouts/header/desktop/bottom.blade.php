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

    $logoutRoute = ($isShopMode && Route::has('shop.customer.session.destroy'))
        ? route('shop.customer.session.destroy')
        : (Route::has('portal.auth.logout') ? route('portal.auth.logout') : null);

    $showCompare = $isShopMode && (bool) core()->getConfigData('catalog.products.settings.compare_option');
    $showMiniCart = $isShopMode && (bool) core()->getConfigData('sales.checkout.shopping_cart.cart_page');

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
            [
                'id' => 'services',
                'name' => trans('portal::app.home.nav.services'),
                'url' => portal_home_url() . '#services',
                'is_active' => false,
                'children' => [],
            ],
            [
                'id' => 'contact',
                'name' => trans('portal::app.home.nav.contact-us'),
                'url' => Route::has('portal.contact_us') ? route('portal.contact_us') : portal_home_url() . '#contact-us',
                'is_active' => request()->routeIs('portal.contact_us'),
                'children' => [],
            ],
        ];
    }
@endphp

<div class="max-w-[var(--container-max-width,1280px)] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16 gap-6">
        {{-- Right Side (Start in RTL): Brand Logo & Multi-Level Navigation --}}
        <div class="flex items-center gap-8">
            {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.logo.before') !!}

            <a
                href="{{ portal_home_url() }}"
                aria-label="{{ $channelName }}"
                class="flex items-center gap-2.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-primary)] rounded-lg py-1 shrink-0"
            >
                @if ($logo = $channel->logo_url)
                    <img
                        src="{{ $logo }}"
                        alt="{{ $channelName }}"
                        class="h-7 w-auto max-h-[32px]"
                    >
                @else
                    <div class="w-8 h-8 rounded-lg bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] flex items-center justify-center p-1 shadow-2xs">
                        <svg viewBox="0 0 64 64" fill="none" class="w-full h-full" aria-hidden="true">
                            <path d="M18 44V20H32C36.4183 20 40 23.5817 40 28C40 32.4183 36.4183 36 32 36H24" stroke="#1c66f6" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M30 36L44 44" stroke="#fe8312" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="44" cy="24" r="4.5" fill="#fe8312" />
                        </svg>
                    </div>

                    <span class="text-base font-black tracking-tight text-[var(--text-primary)] font-cairo">
                        {{ $channelName }}
                    </span>
                @endif
            </a>

            {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.logo.after') !!}

            {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.category.before') !!}

            {{-- Slot declaration for MenuSlotScanner --}}
            {{-- @portalMenuSlot('header_main', 'Main Navigation', 'header') --}}

            {{-- Bagisto Vue 3 Inline Component for Multi-Level Navigation --}}
            <v-desktop-category :initial-categories='@json($navigationItems)'>
                {{-- Shimmer Skeleton before Vue Mounts --}}
                <div class="flex items-center gap-5">
                    <span class="w-16 h-5 rounded bg-[var(--bg-surface-muted)] animate-pulse"></span>
                    <span class="w-20 h-5 rounded bg-[var(--bg-surface-muted)] animate-pulse"></span>
                    <span class="w-16 h-5 rounded bg-[var(--bg-surface-muted)] animate-pulse"></span>
                    <span class="w-14 h-5 rounded bg-[var(--bg-surface-muted)] animate-pulse"></span>
                </div>
            </v-desktop-category>

            {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.category.after') !!}
        </div>

        {{-- Left Side: Search, Theme Toggle, Customer Dropdown & Actions --}}
        <div class="flex items-center gap-2">
            {{-- Search Trigger --}}
            @if ($searchRoute)
                <form action="{{ $searchRoute }}" method="GET" class="flex items-center" role="search">
                    <div class="relative">
                        <input
                            type="text"
                            name="query"
                            value="{{ request('query') }}"
                            placeholder="@lang('portal::app.components.layouts.header.desktop.bottom.search-text')"
                            class="w-44 xl:w-56 ps-9 pe-3 py-1.5 rounded-lg text-[13px] bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] text-[var(--text-primary)] placeholder-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)] transition-colors"
                        >
                        <span class="icon-search text-base text-[var(--text-muted)] absolute top-2 start-3 pointer-events-none"></span>
                    </div>
                </form>
            @endif

            {{-- Dark Mode Switcher --}}
            <v-dark></v-dark>

            {{-- Compare (Shop Mode Only) --}}
            @if ($showCompare && Route::has('shop.compare.index'))
                <a
                    href="{{ route('shop.compare.index') }}"
                    class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] transition-colors"
                    aria-label="Compare"
                >
                    <span class="icon-compare text-lg"></span>
                </a>
            @endif

            {{-- Mini Cart (Shop Mode Only) --}}
            @if ($showMiniCart)
                @if (view()->exists('shop::checkout.cart.mini-cart'))
                    <div class="flex items-center justify-center">
                        @include('shop::checkout.cart.mini-cart')
                    </div>
                @elseif (Route::has('shop.checkout.cart.index'))
                    <a
                        href="{{ route('shop.checkout.cart.index') }}"
                        class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] transition-colors"
                        aria-label="Cart"
                    >
                        <span class="icon-cart text-lg"></span>
                    </a>
                @endif
            @endif

            {{-- Customer / Account Dropdown --}}
            <x-portal::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'rtl' ? 'left' : 'right' }}">
                <x-slot:toggle>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[13px] font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] transition-colors focus:outline-none cursor-pointer"
                        aria-label="@lang('portal::app.components.layouts.header.desktop.bottom.profile')"
                    >
                        <span class="icon-users text-lg"></span>
                        <span class="max-sm:hidden">@lang('portal::app.components.layouts.header.desktop.bottom.profile')</span>
                    </button>
                </x-slot:toggle>

                @if (! $currentUser)
                    <x-slot:content>
                        <div class="p-4 w-64 bg-[var(--bg-surface)] border border-[var(--border-subtle)] rounded-xl shadow-lg">
                            <p class="text-sm font-semibold text-[var(--text-primary)]">
                                @lang('portal::app.components.layouts.header.desktop.bottom.welcome-guest')
                            </p>
                            <p class="text-xs text-[var(--text-muted)] mt-1">
                                @lang('portal::app.components.layouts.header.desktop.bottom.dropdown-text')
                            </p>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                @if ($loginRoute)
                                    <a
                                        href="{{ $loginRoute }}"
                                        class="py-2 px-3 rounded-lg text-xs font-semibold bg-[var(--brand-primary)] text-white text-center hover:bg-[var(--brand-primary-hover)] transition-colors"
                                    >
                                        @lang('portal::app.components.layouts.header.desktop.bottom.sign-in')
                                    </a>
                                @endif
                                @if ($registerRoute)
                                    <a
                                        href="{{ $registerRoute }}"
                                        class="py-2 px-3 rounded-lg text-xs font-semibold border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] text-center hover:bg-[var(--bg-surface-elevated)] transition-colors"
                                    >
                                        @lang('portal::app.components.layouts.header.desktop.bottom.sign-up')
                                    </a>
                                @endif
                            </div>
                        </div>
                    </x-slot:content>
                @else
                    <x-slot:content class="!p-1.5">
                        <div class="w-52 space-y-0.5 bg-[var(--bg-surface)] border border-[var(--border-subtle)] rounded-xl shadow-lg p-2">
                            <div class="px-3 py-2 border-b border-[var(--border-subtle)] mb-1">
                                <p class="text-xs font-bold text-[var(--text-primary)] truncate">
                                    {{ $currentUser->name ?? $currentUser->first_name ?? trans('portal::app.components.layouts.header.desktop.bottom.default-user') }}
                                </p>
                                <p class="text-[11px] text-[var(--text-muted)] truncate">
                                    {{ $currentUser->email }}
                                </p>
                            </div>

                            @if ($profileRoute)
                                <a
                                    href="{{ $profileRoute }}"
                                    class="block px-3 py-2 rounded-lg text-[13px] text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] transition-colors"
                                >
                                    @lang('portal::app.components.layouts.header.desktop.bottom.profile')
                                </a>
                            @endif

                            @if ($ordersRoute)
                                <a
                                    href="{{ $ordersRoute }}"
                                    class="block px-3 py-2 rounded-lg text-[13px] text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] transition-colors"
                                >
                                    @lang('portal::app.components.layouts.header.desktop.bottom.orders', [], false) ?: 'Orders'
                                </a>
                            @endif

                            @if ($logoutRoute)
                                <form action="{{ $logoutRoute }}" method="POST" class="pt-1 border-t border-[var(--border-subtle)]">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full text-start px-3 py-2 rounded-lg text-[13px] text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors cursor-pointer"
                                    >
                                        @lang('portal::app.components.layouts.header.desktop.bottom.logout')
                                    </button>
                                </form>
                            @endif
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
            <span class="w-16 h-5 rounded bg-[var(--bg-surface-muted)] animate-pulse"></span>
            <span class="w-20 h-5 rounded bg-[var(--bg-surface-muted)] animate-pulse"></span>
            <span class="w-16 h-5 rounded bg-[var(--bg-surface-muted)] animate-pulse"></span>
            <span class="w-14 h-5 rounded bg-[var(--bg-surface-muted)] animate-pulse"></span>
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
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-[14px] font-medium transition-colors select-none"
                            :class="(category.is_active || isActiveUrl(category.url))
                                ? '!text-[var(--brand-primary)] font-bold bg-[var(--brand-primary-light,#eef2ff)] shadow-2xs'
                                : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)]'"
                            :aria-expanded="category.children && category.children.length ? (openCategoryId === category.id ? 'true' : 'false') : undefined"
                            @click="category.children && category.children.length ? toggleCategory($event, category.id) : null"
                        >
                            <span v-text="category.name"></span>

                            <svg
                                v-if="category.children && category.children.length"
                                class="w-3.5 h-3.5 text-[var(--text-muted)] transition-transform duration-150"
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
                        class="absolute top-full mt-2 start-0 w-[720px] max-w-[92vw] bg-[var(--bg-surface)] rounded-xl shadow-xl border border-[var(--border-subtle)] overflow-hidden z-50 transition-all duration-150 backdrop-blur-md"
                    >
                        <div class="grid grid-cols-12">
                            {{-- Level 2: Category Switcher Rail --}}
                            <div class="col-span-4 bg-[var(--bg-surface-elevated)] border-e border-[var(--border-subtle)] p-2.5">
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
                                                ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-bold shadow-2xs border border-[var(--border-subtle)]'
                                                : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface)]/60 border border-transparent'"
                                        >
                                            <span v-text="secondLevel.name"></span>

                                            <span
                                                v-if="secondLevel.children && secondLevel.children.length"
                                                class="icon-arrow-left rtl:icon-arrow-left ltr:icon-arrow-right text-xs text-[var(--text-muted)]"
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
                                <div class="pb-2 mb-3 border-b border-[var(--border-subtle)] flex items-center justify-between">
                                    <span
                                        class="text-[11px] font-semibold tracking-wider text-[var(--text-muted)] uppercase"
                                        v-text="getActiveSecondLevel(category).name"
                                    ></span>
                                    <a
                                        :href="sanitizeUrl(getActiveSecondLevel(category).url)"
                                        class="text-[12px] font-medium text-[var(--brand-primary)] hover:text-[var(--brand-primary-hover)] transition-colors"
                                    >
                                        @lang('portal::app.components.layouts.header.mobile.view-all', ['title' => '']) &larr;
                                    </a>
                                </div>

                                <div
                                    class="grid grid-cols-2 gap-3"
                                    v-if="getActiveSecondLevel(category).children && getActiveSecondLevel(category).children.length"
                                >
                                    <div
                                        v-for="thirdLevel in getActiveSecondLevel(category).children"
                                        :key="thirdLevel.id"
                                        class="p-2.5 rounded-xl hover:bg-[var(--bg-surface-elevated)] transition-colors"
                                    >
                                        <a
                                            :href="sanitizeUrl(thirdLevel.url)"
                                            class="text-[13px] font-semibold text-[var(--text-primary)] hover:text-[var(--brand-primary)] transition-colors block"
                                            v-text="thirdLevel.name"
                                        ></a>

                                        <p
                                            v-if="thirdLevel.description"
                                            class="text-[12px] text-[var(--text-secondary)] mt-0.5 leading-snug"
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
                                                    class="text-[11px] font-medium text-[var(--text-secondary)] hover:text-[var(--brand-primary)] bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] px-2 py-0.5 rounded transition-colors"
                                                    v-text="fourthLevel.name"
                                                ></a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                {{-- If level 2 has no subchildren, show direct link --}}
                                <div v-else class="py-6 text-center text-xs text-[var(--text-muted)]">
                                    <a
                                        :href="sanitizeUrl(getActiveSecondLevel(category).url)"
                                        class="inline-flex items-center gap-1 text-[13px] font-medium text-[var(--brand-primary)] hover:underline"
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
