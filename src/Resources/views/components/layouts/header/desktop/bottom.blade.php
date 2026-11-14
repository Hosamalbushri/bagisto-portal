{!! view_render_event('bagisto.portal.layout.header.desktop.bottom.before') !!}

@php
    $channel = core()->getCurrentChannel();
    $channelName = $channel->name ?? config('app.name');
    $assignedMenu = portal_menu_slot('header_main');
    $hasAssignedMenuItems = $assignedMenu && $assignedMenu->rootItems && $assignedMenu->rootItems->isNotEmpty();

    $isShopMode = function_exists('portal_shop_enabled') && portal_shop_enabled();
    $searchRoute = ($isShopMode && Route::has('shop.search.index'))
        ? route('shop.search.index')
        : (Route::has('portal.search.index') ? route('portal.search.index') : null);

    $showCompare = $isShopMode && (bool) core()->getConfigData('catalog.products.settings.compare_option');
    $showMiniCart = $isShopMode && (bool) core()->getConfigData('sales.checkout.shopping_cart.cart_page');
    $showWishlist = $isShopMode && (bool) core()->getConfigData('customer.settings.wishlist.wishlist_option');
@endphp

<div class="flex min-h-[78px] w-full items-center justify-between border-b border-[var(--border-subtle)] px-[60px] max-1180:px-8 bg-white dark:bg-zinc-900 transition-colors">
    <!-- Left Navigation Section: Logo + Categories / Menus -->
    <div class="flex items-center gap-x-10 max-[1180px]:gap-x-5">
        {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.logo.before') !!}

        <!-- Logo -->
        <a
            href="{{ portal_home_url() }}"
            class="flex items-center gap-3 group focus:outline-none shrink-0"
            aria-label="{{ $channelName }}"
        >
            @if ($logo = $channel->logo_url)
                <img id="logo-image" src="{{ $logo }}" class="h-8 w-auto max-h-[36px]" alt="{{ $channelName }}" />
            @else
                <div class="w-9 h-9 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)] flex items-center justify-center p-1.5 shadow-2xs group-hover:border-[var(--brand-primary)] transition-colors shrink-0">
                    <svg viewBox="0 0 64 64" fill="none" class="w-full h-full" aria-hidden="true">
                        <path d="M18 44V20H32C36.4183 20 40 23.5817 40 28C40 32.4183 36.4183 36 32 36H24" stroke="#1c66f6" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M30 36L44 44" stroke="#fe8312" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="44" cy="24" r="4.5" fill="#fe8312" />
                    </svg>
                </div>

                <span class="text-lg font-black tracking-tight text-[var(--text-primary)] font-cairo">
                    {{ $channelName }}
                </span>
            @endif
        </a>

        {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.logo.after') !!}

        {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.category.before') !!}

        <!-- Navigation Items (Preserving Webkul Shop layout & hover styling) -->
        <nav class="flex items-center" aria-label="Main Navigation">
            @if ($hasAssignedMenuItems)
                @foreach ($assignedMenu->rootItems as $item)
                    @if (filled($item->getTitle()))
                        @php
                            $children = $item->children ? $item->children->where('status', 1)->filter(fn ($c) => filled($c->getTitle())) : collect();
                            $hasChildren = $children->isNotEmpty();
                            $active = $item->isActive();
                        @endphp

                        <div class="group relative flex h-[77px] items-center border-b-4 border-transparent hover:border-b-4 hover:border-navyBlue dark:hover:border-blue-500 transition-all">
                            <a
                                href="{{ $item->getUrl() }}"
                                target="{{ $item->target ?: '_self' }}"
                                @if ($item->target === '_blank') rel="noopener noreferrer" @endif
                                class="inline-block px-4 font-semibold text-sm uppercase text-[var(--text-primary)] hover:text-navyBlue dark:hover:text-blue-400 transition-colors {{ $active ? '!text-[var(--brand-primary)]' : '' }}"
                            >
                                <span class="flex items-center gap-1.5">
                                    @if (! empty($item->icon))
                                        <i class="{{ $item->icon }} text-xs"></i>
                                    @endif
                                    {{ $item->getTitle() }}
                                    @if ($hasChildren)
                                        <span class="icon-arrow-down text-xs opacity-70 transition-transform group-hover:rotate-180"></span>
                                    @endif
                                </span>
                            </a>

                            @if ($hasChildren)
                                <div class="pointer-events-none absolute top-[77px] z-50 min-w-[220px] max-w-[500px] translate-y-1 rounded-b-xl border border-t-0 border-[var(--border-subtle)] bg-white dark:bg-zinc-900 p-4 opacity-0 shadow-xl transition-all duration-200 group-hover:pointer-events-auto group-hover:translate-y-0 group-hover:opacity-100 ltr:left-0 rtl:right-0">
                                    <div class="grid gap-1">
                                        @foreach ($children as $child)
                                            <a
                                                href="{{ $child->getUrl() }}"
                                                target="{{ $child->target ?: '_self' }}"
                                                class="block px-3 py-2 text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors"
                                            >
                                                {{ $child->getTitle() }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            @else
                <div class="flex items-center">
                    <div class="group relative flex h-[77px] items-center border-b-4 border-transparent hover:border-b-4 hover:border-navyBlue dark:hover:border-blue-500">
                        <a href="{{ portal_home_url() }}" class="inline-block px-4 font-semibold text-sm uppercase text-[var(--text-primary)] hover:text-navyBlue">
                            @lang('portal::app.home.nav.home')
                        </a>
                    </div>
                    <div class="group relative flex h-[77px] items-center border-b-4 border-transparent hover:border-b-4 hover:border-navyBlue dark:hover:border-blue-500">
                        <a href="#services" class="inline-block px-4 font-semibold text-sm uppercase text-[var(--text-primary)] hover:text-navyBlue">
                            @lang('portal::app.home.nav.services')
                        </a>
                    </div>
                    <div class="group relative flex h-[77px] items-center border-b-4 border-transparent hover:border-b-4 hover:border-navyBlue dark:hover:border-blue-500">
                        <a href="#contact" class="inline-block px-4 font-semibold text-sm uppercase text-[var(--text-primary)] hover:text-navyBlue">
                            @lang('portal::app.home.nav.contact-us')
                        </a>
                    </div>
                </div>
            @endif
        </nav>

        {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.category.after') !!}
    </div>

    <!-- Right Navigation Section: Unified Action Icons (Search, Dark Mode, Profile, Shop Actions) -->
    <div class="flex items-center gap-2.5">
        {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.search_bar.before') !!}

        <!-- Search Icon with Dropdown Popover -->
        @if ($searchRoute)
            <x-portal::dropdown
                position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'right' : 'left' }}"
                ::close-on-click="false"
            >
                <x-slot:toggle>
                    <button
                        type="button"
                        class="flex items-center justify-center w-10 h-10 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all cursor-pointer focus:outline-none"
                        aria-label="@lang('portal::app.components.layouts.header.desktop.bottom.search')"
                    >
                        <span class="icon-search text-xl"></span>
                    </button>
                </x-slot>

                <x-slot:content class="!p-3 min-w-[340px] sm:min-w-[420px]">
                    <form action="{{ $searchRoute }}" method="GET" class="relative flex items-center" role="search">
                        <label for="desktop-search-input" class="sr-only">
                            @lang('portal::app.components.layouts.header.desktop.bottom.search')
                        </label>

                        <div class="icon-search pointer-events-none absolute top-3 flex items-center text-lg text-gray-400 ltr:left-3.5 rtl:right-3.5"></div>

                        <input
                            type="text"
                            id="desktop-search-input"
                            name="query"
                            value="{{ request('query') }}"
                            class="block w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/90 py-2.5 ltr:pl-10 ltr:pr-20 rtl:pr-10 rtl:pl-20 text-xs font-medium text-gray-900 dark:text-gray-100 placeholder-zinc-400 hover:border-gray-400 focus:border-navyBlue focus:outline-none transition-colors"
                            placeholder="@lang('portal::app.components.layouts.header.desktop.bottom.search-text')"
                            autofocus
                        >

                        <button
                            type="submit"
                            class="absolute ltr:right-1.5 rtl:left-1.5 px-3 py-1.5 rounded-lg bg-navyBlue text-white text-xs font-semibold hover:bg-navyBlue/90 transition-colors cursor-pointer"
                        >
                            @lang('portal::app.components.layouts.header.desktop.bottom.search')
                        </button>
                    </form>
                </x-slot>
            </x-portal::dropdown>
        @endif

        {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.search_bar.after') !!}

        <!-- Dark Mode Switcher -->
        <v-dark>
            <button
                type="button"
                class="flex items-center justify-center w-10 h-10 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all cursor-pointer focus:outline-none"
                title="{{ request()->cookie('portal_dark_mode') ? trans('portal::app.home.theme.light-mode') : trans('portal::app.home.theme.dark-mode') }}"
                aria-label="Toggle theme"
            >
                <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark' }} text-xl"></span>
            </button>
        </v-dark>

        <!-- Compare (Shop Mode Only) -->
        @if ($showCompare && Route::has('shop.compare.index'))
            <a
                href="{{ route('shop.compare.index') }}"
                class="flex items-center justify-center w-10 h-10 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all cursor-pointer focus:outline-none"
                aria-label="@lang('shop::app.components.layouts.header.desktop.bottom.compare')"
            >
                <span class="icon-compare text-xl" role="presentation"></span>
            </a>
        @endif

        <!-- Mini Cart (Shop Mode Only) -->
        @if ($showMiniCart)
            @if (view()->exists('shop::checkout.cart.mini-cart'))
                <div class="flex items-center justify-center w-10 h-10 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all">
                    @include('shop::checkout.cart.mini-cart')
                </div>
            @elseif (Route::has('shop.checkout.cart.index'))
                <a
                    href="{{ route('shop.checkout.cart.index') }}"
                    class="flex items-center justify-center w-10 h-10 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all cursor-pointer focus:outline-none"
                    aria-label="Shopping Cart"
                >
                    <span class="icon-cart text-xl"></span>
                </a>
            @endif
        @endif

        {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.profile.before') !!}

        <!-- User Account / Preferences Dropdown (Webkul Shop exact dropdown styled for Portal) -->
        @php
            $hasCustomerGuard = array_key_exists('customer', config('auth.guards', []));
            $isCustomerAuth = $hasCustomerGuard && auth()->guard('customer')->check();
            $portalGuard = \Platform\Portal\Helpers\PortalAuth::guard();
            $hasPortalGuard = array_key_exists($portalGuard, config('auth.guards', []));
            $isPortalAuth = \Platform\Portal\Helpers\PortalAuth::isEnabled() && $hasPortalGuard && auth()->guard($portalGuard)->check();
            $currentUser = $isCustomerAuth ? auth()->guard('customer')->user() : ($isPortalAuth ? auth()->guard($portalGuard)->user() : null);
        @endphp

        <x-portal::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'right' : 'left' }}">
            <x-slot:toggle>
                <button
                    type="button"
                    class="flex items-center justify-center w-10 h-10 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all cursor-pointer focus:outline-none"
                    aria-label="Account"
                >
                    <span class="icon-users text-xl"></span>
                </button>
            </x-slot>

                @if ($currentUser)
                    <!-- Authenticated Dropdown -->
                    <x-slot:content class="!p-0 min-w-[240px]">
                        <div class="grid gap-1.5 p-5 pb-3">
                            <p class="text-lg font-bold text-[var(--text-primary)]">
                                @lang('portal::app.components.layouts.header.desktop.bottom.welcome')،
                                {{ $currentUser->name ?? $currentUser->first_name ?? 'User' }}
                            </p>
                            <p class="text-xs text-[var(--text-muted)] truncate">
                                {{ $currentUser->email }}
                            </p>
                        </div>

                        <p class="w-full border-b border-zinc-200 dark:border-zinc-800"></p>

                        <div class="py-2 grid gap-0.5">
                            @if (Route::has('customer.account.profile.index'))
                                <a
                                    class="px-5 py-2 text-sm text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                    href="{{ route('customer.account.profile.index') }}"
                                >
                                    @lang('portal::app.components.layouts.header.desktop.bottom.profile')
                                </a>
                            @endif

                            @if ($isShopMode && Route::has('customer.account.orders.index'))
                                <a
                                    class="px-5 py-2 text-sm text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                    href="{{ route('customer.account.orders.index') }}"
                                >
                                    @lang('shop::app.components.layouts.header.desktop.bottom.orders')
                                </a>
                            @endif

                            @if ($isShopMode && $showWishlist && Route::has('customer.account.wishlist.index'))
                                <a
                                    class="px-5 py-2 text-sm text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
                                    href="{{ route('customer.account.wishlist.index') }}"
                                >
                                    @lang('shop::app.components.layouts.header.desktop.bottom.wishlist')
                                </a>
                            @endif

                            <!-- Logout -->
                            @if ($isCustomerAuth && Route::has('customer.session.destroy'))
                                <form method="POST" action="{{ route('customer.session.destroy') }}" id="customerLogoutForm">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="w-full text-start px-5 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors cursor-pointer"
                                    >
                                        @lang('portal::app.components.layouts.header.desktop.bottom.logout')
                                    </button>
                                </form>
                            @elseif ($isPortalAuth && Route::has('portal.auth.logout'))
                                <form method="POST" action="{{ route('portal.auth.logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full text-start px-5 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors cursor-pointer"
                                    >
                                        @lang('portal::app.components.layouts.header.desktop.bottom.logout')
                                    </button>
                                </form>
                            @endif
                        </div>
                    </x-slot>
                @else
                    <!-- Guest Dropdown (Portal Custom Texts) -->
                    <x-slot:content class="min-w-[280px] p-5">
                        <div class="grid gap-1">
                            <p class="text-lg font-bold text-[var(--text-primary)]">
                                @lang('portal::app.components.layouts.header.desktop.bottom.welcome-guest')
                            </p>
                            <p class="text-xs text-[var(--text-secondary)]">
                                @lang('portal::app.components.layouts.header.desktop.bottom.dropdown-text')
                            </p>
                        </div>

                        <p class="w-full my-3 border-b border-zinc-200 dark:border-zinc-800"></p>

                        @php
                            $loginUrl = Route::has('portal.auth.login')
                                ? route('portal.auth.login')
                                : (Route::has('customer.session.index') ? route('customer.session.index') : null);
                            $registerUrl = Route::has('portal.auth.register')
                                ? route('portal.auth.register')
                                : (Route::has('customer.register.index') ? route('customer.register.index') : null);
                        @endphp

                        <div class="flex gap-3 mt-3">
                            @if ($loginUrl)
                                <a
                                    href="{{ $loginUrl }}"
                                    class="flex-1 py-2 text-center text-sm font-semibold text-white bg-navyBlue hover:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-700 rounded-xl transition-colors"
                                >
                                    @lang('portal::app.components.layouts.header.desktop.bottom.sign-in')
                                </a>
                            @endif

                            @if ($registerUrl)
                                <a
                                    href="{{ $registerUrl }}"
                                    class="flex-1 py-2 text-center text-sm font-semibold border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-xl transition-colors text-[var(--text-primary)]"
                                >
                                    @lang('portal::app.components.layouts.header.desktop.bottom.sign-up')
                                </a>
                            @endif
                        </div>
                    </x-slot>
                @endif
            </x-portal::dropdown>

            {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.profile.after') !!}
        </div>
    </div>
</div>

{!! view_render_event('bagisto.portal.layout.header.desktop.bottom.after') !!}
