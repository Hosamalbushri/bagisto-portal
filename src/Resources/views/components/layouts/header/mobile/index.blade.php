{!! view_render_event('bagisto.portal.layout.header.mobile.before') !!}

@php
    $channel = core()->getCurrentChannel();
    $channelName = $channel->name ?? config('app.name');
    $assignedMenu = portal_menu_slot('header_mobile') ?: portal_menu_slot('header_main');
    $hasAssignedMenuItems = $assignedMenu && $assignedMenu->rootItems && $assignedMenu->rootItems->isNotEmpty();

    $isShopMode = function_exists('portal_shop_enabled') && portal_shop_enabled();
    $showCompare = $isShopMode && (bool) core()->getConfigData('catalog.products.settings.compare_option');
    $showMiniCart = $isShopMode && (bool) core()->getConfigData('sales.checkout.shopping_cart.cart_page');
    $showWishlist = $isShopMode && (bool) core()->getConfigData('customer.settings.wishlist.wishlist_option');

    $searchRoute = ($isShopMode && Route::has('shop.search.index'))
        ? route('shop.search.index')
        : (Route::has('portal.search.index') ? route('portal.search.index') : null);

    $hasCustomerGuard = array_key_exists('customer', config('auth.guards', []));
    $isCustomerAuth = $hasCustomerGuard && auth()->guard('customer')->check();
    $portalGuard = \Platform\Portal\Helpers\PortalAuth::guard();
    $hasPortalGuard = array_key_exists($portalGuard, config('auth.guards', []));
    $isPortalAuth = \Platform\Portal\Helpers\PortalAuth::isEnabled() && $hasPortalGuard && auth()->guard($portalGuard)->check();
    $currentUser = $isCustomerAuth ? auth()->guard('customer')->user() : ($isPortalAuth ? auth()->guard($portalGuard)->user() : null);

    $loginUrl = Route::has('customer.session.index')
        ? route('customer.session.index')
        : (Route::has('portal.auth.login') ? route('portal.auth.login') : null);
    $registerUrl = Route::has('customer.register.index')
        ? route('customer.register.index')
        : (Route::has('portal.auth.register') ? route('portal.auth.register') : null);

    $channelLocales = is_countable($channel->locales ?? null) ? $channel->locales : (method_exists($channel, 'locales') ? $channel->locales() : collect());
    $channelCurrencies = is_countable($channel->currencies ?? null) ? $channel->currencies : (method_exists($channel, 'currencies') ? $channel->currencies() : collect());
    $localesCount = count($channelLocales);
    $currenciesCount = count($channelCurrencies);
@endphp

<div class="flex items-center justify-between px-4 py-3 shadow-xs lg:hidden bg-white dark:bg-zinc-900 border-b border-[var(--border-subtle)]">
    <!-- Left Navigation: Hamburger & Logo -->
    <div class="flex items-center gap-x-2.5">
        {!! view_render_event('bagisto.portal.layout.header.mobile.drawer.before') !!}

        <button
            type="button"
            class="flex items-center justify-center w-9 h-9 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all cursor-pointer focus:outline-none"
            @click="$refs.mobileMenuDrawer.open()"
            aria-label="Open navigation menu"
        >
            <span class="icon-hamburger text-lg"></span>
        </button>

        {!! view_render_event('bagisto.portal.layout.header.mobile.drawer.after') !!}

        <a
            href="{{ portal_home_url() }}"
            class="flex max-h-[30px] items-center gap-2 focus:outline-none"
            aria-label="{{ $channelName }}"
        >
            @if ($logo = $channel->logo_url)
                <img class="block h-7 w-auto" src="{{ $logo }}" alt="{{ $channelName }}" />
            @else
                <span class="text-base font-bold text-[var(--text-primary)] font-cairo">
                    {{ $channelName }}
                </span>
            @endif
        </a>
    </div>

    <!-- Right Navigation: Unified Action Icons (Search, Dark Mode, Shop Actions, User) -->
    <div class="flex items-center gap-x-2">
        <!-- Search Icon with Dropdown Popover -->
        @if ($searchRoute)
            <x-portal::dropdown
                position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'right' : 'left' }}"
                ::close-on-click="false"
            >
                <x-slot:toggle>
                    <button
                        type="button"
                        class="flex items-center justify-center w-9 h-9 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all cursor-pointer focus:outline-none"
                        aria-label="@lang('portal::app.components.layouts.header.desktop.bottom.search')"
                    >
                        <span class="icon-search text-lg"></span>
                    </button>
                </x-slot>

                <x-slot:content class="!p-2.5 min-w-[280px] max-w-[calc(100vw-32px)]">
                    <form action="{{ $searchRoute }}" method="GET" class="relative flex items-center" role="search">
                        <label for="mobile-dropdown-search" class="sr-only">
                            @lang('portal::app.components.layouts.header.desktop.bottom.search')
                        </label>

                        <div class="icon-search pointer-events-none absolute top-2.5 flex items-center text-base text-gray-400 ltr:left-3 rtl:right-3"></div>

                        <input
                            type="text"
                            id="mobile-dropdown-search"
                            name="query"
                            value="{{ request('query') }}"
                            class="block w-full rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/90 py-2 ltr:pl-9 ltr:pr-16 rtl:pr-9 rtl:pl-16 text-xs font-medium text-gray-900 dark:text-gray-100 placeholder-zinc-400 hover:border-gray-400 focus:border-navyBlue focus:outline-none transition-colors"
                            placeholder="@lang('portal::app.components.layouts.header.desktop.bottom.search-text')"
                            autofocus
                        >

                        <button
                            type="submit"
                            class="absolute ltr:right-1 rtl:left-1 px-2.5 py-1 rounded-md bg-navyBlue text-white text-[11px] font-semibold hover:bg-navyBlue/90 transition-colors cursor-pointer"
                        >
                            @lang('portal::app.components.layouts.header.desktop.bottom.search')
                        </button>
                    </form>
                </x-slot>
            </x-portal::dropdown>
        @endif

        <!-- Dark Mode Switcher -->
        <v-dark>
            <button
                type="button"
                class="flex items-center justify-center w-9 h-9 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all cursor-pointer focus:outline-none"
                aria-label="Toggle theme"
            >
                <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark' }} text-lg"></span>
            </button>
        </v-dark>

        <!-- Compare Icon (Shop) -->
        @if ($showCompare && Route::has('shop.compare.index'))
            <a
                href="{{ route('shop.compare.index') }}"
                class="flex items-center justify-center w-9 h-9 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all cursor-pointer focus:outline-none"
                aria-label="@lang('portal::app.components.layouts.header.mobile.compare', [], false) ?: 'Compare'"
            >
                <span class="icon-compare text-lg"></span>
            </a>
        @endif

        <!-- Mini Cart (Shop) -->
        @if ($showMiniCart)
            @if (view()->exists('shop::checkout.cart.mini-cart'))
                <div class="flex items-center justify-center w-9 h-9 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all">
                    @include('shop::checkout.cart.mini-cart')
                </div>
            @elseif (Route::has('shop.checkout.cart.index'))
                <a
                    href="{{ route('shop.checkout.cart.index') }}"
                    class="flex items-center justify-center w-9 h-9 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all cursor-pointer focus:outline-none"
                    aria-label="Shopping Cart"
                >
                    <span class="icon-cart text-lg"></span>
                </a>
            @endif
        @endif

        <!-- User Profile Drawer Trigger -->
        <button
            type="button"
            class="flex items-center justify-center w-9 h-9 rounded-xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] bg-zinc-100/80 hover:bg-zinc-200/90 dark:bg-zinc-800/80 dark:hover:bg-zinc-700/90 border border-zinc-200/60 dark:border-zinc-700/60 transition-all cursor-pointer focus:outline-none"
            @click="$refs.mobileMenuDrawer.open()"
            aria-label="User Account"
        >
            <span class="icon-users text-lg"></span>
        </button>
    </div>
</div>

    <!-- Mobile Navigation & User Drawer -->
    <x-portal::drawer
        position="left"
        width="320px"
        ref="mobileMenuDrawer"
    >
        <x-slot:header>
            <div class="flex items-center justify-between w-full">
                <a href="{{ portal_home_url() }}" class="flex items-center gap-2">
                    @if ($logo = $channel->logo_url)
                        <img src="{{ $logo }}" class="h-7 w-auto" alt="{{ $channelName }}" />
                    @else
                        <span class="text-base font-bold text-[var(--text-primary)] font-cairo">
                            {{ $channelName }}
                        </span>
                    @endif
                </a>
            </div>
        </x-slot>

        <x-slot:content class="p-0 flex flex-col justify-between h-full divide-y divide-zinc-200 dark:divide-zinc-800">
            <div class="p-4 space-y-4 overflow-y-auto">
                <!-- User Guest / Profile Section in Drawer -->
                @if ($currentUser)
                    <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-[var(--border-subtle)]">
                        <p class="text-base font-bold text-[var(--text-primary)]">
                            @lang('portal::app.components.layouts.header.mobile.welcome')،
                            {{ $currentUser->name ?? $currentUser->first_name ?? 'User' }}
                        </p>
                        <p class="text-xs text-[var(--text-muted)] truncate mt-0.5">
                            {{ $currentUser->email }}
                        </p>

                        <div class="mt-3 grid gap-1 border-t border-[var(--border-subtle)] pt-2 text-xs font-medium">
                            @if (Route::has('customer.account.profile.index'))
                                <a href="{{ route('customer.account.profile.index') }}" class="py-1 text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                                    @lang('portal::app.components.layouts.header.mobile.profile')
                                </a>
                            @endif

                            @if ($isShopMode && Route::has('customer.account.orders.index'))
                                <a href="{{ route('customer.account.orders.index') }}" class="py-1 text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                                    @lang('portal::app.components.layouts.header.desktop.bottom.orders')
                                </a>
                            @endif

                            @if ($isShopMode && $showWishlist && Route::has('customer.account.wishlist.index'))
                                <a href="{{ route('customer.account.wishlist.index') }}" class="py-1 text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                                    @lang('portal::app.components.layouts.header.desktop.bottom.wishlist')
                                </a>
                            @endif

                            @if ($isCustomerAuth && Route::has('customer.session.destroy'))
                                <form method="POST" action="{{ route('customer.session.destroy') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="py-1 text-red-600 dark:text-red-400 cursor-pointer">
                                        @lang('portal::app.components.layouts.header.mobile.logout')
                                    </button>
                                </form>
                            @elseif ($isPortalAuth && Route::has('portal.auth.logout'))
                                <form method="POST" action="{{ route('portal.auth.logout') }}">
                                    @csrf
                                    <button type="submit" class="py-1 text-red-600 dark:text-red-400 cursor-pointer">
                                        @lang('portal::app.auth.logout.title')
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-[var(--border-subtle)]">
                        <p class="text-base font-bold text-[var(--text-primary)]">
                            @lang('portal::app.components.layouts.header.mobile.welcome-guest')
                        </p>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">
                            @lang('portal::app.components.layouts.header.mobile.dropdown-text')
                        </p>

                        <div class="flex gap-2 mt-3">
                            @if ($loginUrl)
                                <a href="{{ $loginUrl }}" class="flex-1 py-2 text-center text-xs font-semibold text-white bg-navyBlue rounded-lg">
                                    @lang('portal::app.components.layouts.header.mobile.sign-in')
                                </a>
                            @endif

                            @if ($registerUrl)
                                <a href="{{ $registerUrl }}" class="flex-1 py-2 text-center text-xs font-semibold border border-zinc-300 dark:border-zinc-700 rounded-lg">
                                    @lang('portal::app.components.layouts.header.mobile.sign-up')
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Navigation Links (Portal Menu Slot System) -->
                <nav class="grid gap-1 pt-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--text-muted)] px-2 mb-1">
                        @lang('portal::app.components.layouts.header.desktop.bottom.categories')
                    </span>

                    <x-portal::menu-slot
                        id="header_mobile"
                        location="header"
                        direction="column"
                        class="space-y-1"
                    >
                        <x-portal::menu-slot
                            id="header_main"
                            location="header"
                            direction="column"
                            class="space-y-1"
                        >
                            <a href="{{ portal_home_url() }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ (request()->routeIs('portal.home') || request()->is('/')) ? '!text-[var(--brand-primary)] font-bold bg-[var(--brand-primary-light,#eef2ff)] dark:bg-[var(--brand-primary)]/15 border-s-4 border-[var(--brand-primary)] shadow-xs' : 'text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800' }}">
                                <span>@lang('portal::app.home.nav.home')</span>
                                @if (request()->routeIs('portal.home') || request()->is('/'))
                                    <span class="w-2 h-2 rounded-full bg-[var(--brand-primary)] shadow-xs"></span>
                                @endif
                            </a>
                            <a href="{{ portal_home_url() }}#services" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                <span>@lang('portal::app.home.nav.services')</span>
                            </a>
                            <a href="{{ portal_home_url() }}#contact" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                <span>@lang('portal::app.home.nav.contact-us')</span>
                            </a>
                        </x-portal::menu-slot>
                    </x-portal::menu-slot>
                </nav>

                <!-- Currencies & Languages Pickers in Drawer -->
                @if ($localesCount > 1 || $currenciesCount > 1)
                    <div class="pt-3 border-t border-[var(--border-subtle)] space-y-3">
                        @if ($localesCount > 1)
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--text-muted)] px-2">
                                    @lang('portal::app.components.layouts.header.mobile.locales')
                                </span>
                                <div class="mt-1.5 grid grid-cols-2 gap-1">
                                    @foreach ($channelLocales as $locale)
                                        <a
                                            href="{{ route('portal.locale.switch', $locale->code) }}"
                                            class="flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs {{ app()->getLocale() == $locale->code ? 'font-bold bg-zinc-200 dark:bg-zinc-700 text-[var(--text-primary)]' : 'text-[var(--text-secondary)] hover:bg-zinc-100 dark:hover:bg-zinc-800' }}"
                                        >
                                            @if (! empty($locale->logo_url))
                                                <img src="{{ $locale->logo_url }}" class="h-3 w-3 rounded-full" alt="{{ $locale->name }}">
                                            @endif
                                            <span>{{ $locale->name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($currenciesCount > 1)
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--text-muted)] px-2">
                                    @lang('portal::app.components.layouts.header.mobile.currencies')
                                </span>
                                <div class="mt-1.5 grid grid-cols-2 gap-1">
                                    @foreach ($channelCurrencies as $currency)
                                        <a
                                            href="{{ route('portal.currency.switch', $currency->code) }}"
                                            class="flex items-center justify-center px-3 py-1.5 rounded-lg text-xs {{ core()->getCurrentCurrencyCode() == $currency->code ? 'font-bold bg-zinc-200 dark:bg-zinc-700 text-[var(--text-primary)]' : 'text-[var(--text-secondary)] hover:bg-zinc-100 dark:hover:bg-zinc-800' }}"
                                        >
                                            {{ $currency->code }} ({{ $currency->symbol }})
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Drawer Footer: Dark mode & Channel info -->
            <div class="p-4 bg-zinc-50 dark:bg-zinc-800/40 border-t border-[var(--border-subtle)] flex items-center justify-between">
                <span class="text-xs text-[var(--text-muted)]">
                    {{ $channelName }}
                </span>

                <v-dark>
                    <button
                        type="button"
                        class="flex items-center gap-1.5 text-xs text-[var(--text-secondary)] hover:text-[var(--text-primary)] focus:outline-none"
                    >
                        <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark' }} text-base"></span>
                        <span>{{ request()->cookie('portal_dark_mode') ? trans('portal::app.home.theme.light-mode') : trans('portal::app.home.theme.dark-mode') }}</span>
                    </button>
                </v-dark>
            </div>
        </x-slot>
    </x-portal::drawer>

{!! view_render_event('bagisto.portal.layout.header.mobile.after') !!}
