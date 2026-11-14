{!! view_render_event('bagisto.portal.layout.header.mobile.before') !!}

@php
    $channel = core()->getCurrentChannel();
    $channelName = $channel->name ?? config('app.name');

    $isShopMode = function_exists('portal_shop_enabled') && portal_shop_enabled();
    $categoryTreeUrl = ($isShopMode && Route::has('shop.api.categories.tree'))
        ? route('shop.api.categories.tree')
        : '';

    $loginRoute = ($isShopMode && Route::has('shop.customer.session.index'))
        ? route('shop.customer.session.index')
        : (Route::has('portal.auth.login') ? route('portal.auth.login') : null);

    $registerRoute = ($isShopMode && Route::has('shop.customers.register.index'))
        ? route('shop.customers.register.index')
        : (Route::has('portal.auth.register') ? route('portal.auth.register') : null);

    $profileRoute = ($isShopMode && Route::has('shop.customers.account.profile.index'))
        ? route('shop.customers.account.profile.index')
        : (Route::has('customer.account.profile.index') ? route('customer.account.profile.index') : null);

    $hasCustomerGuard = array_key_exists('customer', config('auth.guards', []));
    $isCustomerAuth = $hasCustomerGuard && auth()->guard('customer')->check();
    $portalGuard = \Platform\Portal\Helpers\PortalAuth::guard();
    $hasPortalGuard = array_key_exists($portalGuard, config('auth.guards', []));
    $isPortalAuth = \Platform\Portal\Helpers\PortalAuth::isEnabled() && $hasPortalGuard && auth()->guard($portalGuard)->check();
    $currentUser = $isCustomerAuth ? auth()->guard('customer')->user() : ($isPortalAuth ? auth()->guard($portalGuard)->user() : null);

    // Build hierarchical items from assigned menu slot 'header_mobile' or 'header_main'
    $assignedMenu = portal_menu_slot('header_mobile') ?: portal_menu_slot('header_main');
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

<div class="flex lg:hidden items-center justify-between h-16 px-4 bg-[var(--header-bg,var(--bg-surface))]/90 backdrop-blur-md border-b border-[var(--border-subtle)]">
    {{-- Left: Bagisto Drawer (<x-portal::drawer>) + Logo --}}
    <div class="flex items-center gap-3">
        {!! view_render_event('bagisto.portal.layout.header.mobile.drawer.before') !!}

        <x-portal::drawer
            position="{{ core()->getCurrentLocale()->direction === 'rtl' ? 'right' : 'left' }}"
            width="85%"
        >
            <x-slot:toggle>
                <button
                    type="button"
                    class="w-10 h-10 inline-flex items-center justify-center rounded-lg text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)] transition-colors focus:outline-none"
                    aria-label="@lang('portal::app.components.layouts.header.mobile.account')"
                >
                    <span class="icon-hamburger text-xl"></span>
                </button>
            </x-slot:toggle>

            <x-slot:header>
                <div class="flex items-center justify-between py-2">
                    <a href="{{ portal_home_url() }}" class="flex items-center gap-2">
                        @if ($logo = $channel->logo_url)
                            <img
                                src="{{ $logo }}"
                                alt="{{ $channelName }}"
                                class="h-6 w-auto"
                            >
                        @else
                            <span class="font-bold text-[var(--text-primary)] font-cairo text-base">
                                {{ $channelName }}
                            </span>
                        @endif
                    </a>
                </div>
            </x-slot:header>

            <x-slot:content class="!p-3 flex flex-col justify-between h-full divide-y divide-[var(--border-subtle)]">
                <div class="space-y-4 overflow-y-auto">
                    {{-- User Profile / Guest State in Mobile Drawer --}}
                    @if ($currentUser)
                        <div class="p-3 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)]">
                            <p class="text-sm font-bold text-[var(--text-primary)]">
                                @lang('portal::app.components.layouts.header.mobile.welcome', [
                                    'name' => $currentUser->name ?? $currentUser->first_name ?? trans('portal::app.components.layouts.header.mobile.default-user')
                                ])
                            </p>
                            <p class="text-xs text-[var(--text-muted)] truncate mt-0.5">
                                {{ $currentUser->email }}
                            </p>
                            @if ($profileRoute)
                                <div class="mt-2.5 pt-2 border-t border-[var(--border-subtle)]">
                                    <a href="{{ $profileRoute }}" class="text-xs font-medium text-[var(--brand-primary)] hover:underline">
                                        @lang('portal::app.components.layouts.header.mobile.profile') &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)]">
                            <p class="text-sm font-semibold text-[var(--text-primary)]">
                                @lang('portal::app.components.layouts.header.mobile.welcome-guest')
                            </p>
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                @if ($loginRoute)
                                    <a
                                        href="{{ $loginRoute }}"
                                        class="py-1.5 px-3 rounded-lg text-xs font-semibold bg-[var(--brand-primary)] text-white text-center hover:bg-[var(--brand-primary-hover)] transition-colors"
                                    >
                                        @lang('portal::app.components.layouts.header.mobile.sign-in')
                                    </a>
                                @endif
                                @if ($registerRoute)
                                    <a
                                        href="{{ $registerRoute }}"
                                        class="py-1.5 px-3 rounded-lg text-xs font-semibold border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] text-center hover:bg-[var(--bg-surface-muted)] transition-colors"
                                    >
                                        @lang('portal::app.components.layouts.header.mobile.sign-up')
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Slot declaration for MenuSlotScanner --}}
                    {{-- @portalMenuSlot('header_mobile', 'Mobile Navigation', 'header') --}}

                    {{-- Multi-Level Navigation Tree Accordion --}}
                    <v-mobile-category :initial-categories='@json($navigationItems)'></v-mobile-category>
                </div>
            </x-slot:content>
        </x-portal::drawer>

        {!! view_render_event('bagisto.portal.layout.header.mobile.drawer.after') !!}

        <a href="{{ portal_home_url() }}" class="font-semibold text-[15px] text-[var(--text-primary)] font-cairo">
            {{ $channelName }}
        </a>
    </div>

    {{-- Right: Dark Mode Toggle --}}
    <div class="flex items-center gap-1.5">
        <v-dark>
            <button
                type="button"
                class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)] transition-colors focus:outline-none cursor-pointer"
                aria-label="@lang('portal::app.components.layouts.header.mobile.theme-toggle')"
            >
                <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark' }} text-lg"></span>
            </button>
        </v-dark>
    </div>
</div>

{!! view_render_event('bagisto.portal.layout.header.mobile.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-mobile-category-template"
    >
        <nav aria-label="@lang('portal::app.components.layouts.header.mobile.categories', [], false) ?: 'Mobile Menu'">
            <ul role="list" class="space-y-1">
                <li
                    v-for="category in categories"
                    :key="category.id"
                    class="border-b border-[var(--border-subtle)] pb-1"
                >
                    <div class="flex items-center justify-between">
                        <a
                            :href="sanitizeUrl(category.url)"
                            class="flex-1 py-2.5 px-3 text-[14px] font-semibold text-[var(--text-primary)] hover:text-[var(--brand-primary)] transition-colors"
                            :class="{ '!text-[var(--brand-primary)] font-bold': category.is_active || isActiveUrl(category.url) }"
                            v-text="category.name"
                        ></a>

                        <button
                            v-if="category.children && category.children.length"
                            type="button"
                            @click="toggle(category.id)"
                            :aria-expanded="isOpen(category.id) ? 'true' : 'false'"
                            class="w-10 h-10 inline-flex items-center justify-center rounded-lg text-[var(--text-muted)] hover:bg-[var(--bg-surface-elevated)] transition-colors"
                        >
                            <span
                                class="icon-arrow-down text-sm transition-transform duration-150"
                                :class="{ 'rotate-180': isOpen(category.id) }"
                            ></span>
                        </button>
                    </div>

                    {{-- Level 2 Accordion --}}
                    <ul
                        v-if="category.children && category.children.length"
                        v-show="isOpen(category.id)"
                        role="list"
                        class="ps-3 pb-2 space-y-1"
                    >
                        <li
                            v-for="secondLevel in category.children"
                            :key="secondLevel.id"
                        >
                            <div class="flex items-center justify-between">
                                <a
                                    :href="sanitizeUrl(secondLevel.url)"
                                    class="flex-1 py-2 px-3 text-[13px] font-medium text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors"
                                    :class="{ '!text-[var(--brand-primary)] font-bold': secondLevel.is_active || isActiveUrl(secondLevel.url) }"
                                    v-text="secondLevel.name"
                                ></a>

                                <button
                                    v-if="secondLevel.children && secondLevel.children.length"
                                    type="button"
                                    @click="toggle(secondLevel.id)"
                                    :aria-expanded="isOpen(secondLevel.id) ? 'true' : 'false'"
                                    class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-[var(--text-muted)] hover:bg-[var(--bg-surface-elevated)] transition-colors"
                                >
                                    <span
                                        class="icon-arrow-down text-xs transition-transform duration-150"
                                        :class="{ 'rotate-180': isOpen(secondLevel.id) }"
                                    ></span>
                                </button>
                            </div>

                            {{-- Level 3 Accordion --}}
                            <ul
                                v-if="secondLevel.children && secondLevel.children.length"
                                v-show="isOpen(secondLevel.id)"
                                role="list"
                                class="ms-3 border-s border-[var(--border-subtle)] mt-0.5 space-y-0.5"
                            >
                                <li
                                    v-for="thirdLevel in secondLevel.children"
                                    :key="thirdLevel.id"
                                >
                                    <a
                                        :href="sanitizeUrl(thirdLevel.url)"
                                        class="block py-2 ps-4 pe-2 text-[13px] text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors"
                                        :class="{ '!text-[var(--brand-primary)] font-bold': thirdLevel.is_active || isActiveUrl(thirdLevel.url) }"
                                        v-text="thirdLevel.name"
                                    ></a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </script>

    <script type="module">
        app.component('v-mobile-category', {
            template: '#v-mobile-category-template',

            props: {
                initialCategories: {
                    type: Array,
                    default: () => [],
                },
            },

            data() {
                return {
                    categories: (this.initialCategories && this.initialCategories.length) ? this.initialCategories : [],
                    openIds: {},
                };
            },

            mounted() {
                if (! this.categories || ! this.categories.length) {
                    this.getCategories();
                }
            },

            methods: {
                getCategories() {
                    const url = "{{ ($isShopMode && Route::has('shop.api.categories.tree')) ? route('shop.api.categories.tree') : '' }}";
                    if (! url) return;

                    this.$axios.get(url)
                        .then(response => {
                            this.categories = response.data.data;
                        })
                        .catch(error => {
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

                toggle(id) {
                    this.openIds[id] = ! this.openIds[id];
                },

                isOpen(id) {
                    return Boolean(this.openIds[id]);
                },
            },
        });
    </script>
@endPushOnce
