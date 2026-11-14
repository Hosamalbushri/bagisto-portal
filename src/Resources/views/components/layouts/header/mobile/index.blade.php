{!! view_render_event('bagisto.portal.layout.header.mobile.before') !!}

@php
    $channel = core()->getCurrentChannel();
    $channelName = $channel->name ?? config('app.name');
    $assignedMenu = portal_menu_slot('header_mobile') ?: portal_menu_slot('header_main');
    $hasAssignedMenuItems = $assignedMenu && $assignedMenu->rootItems && $assignedMenu->rootItems->isNotEmpty();

    $showCompare = (bool) core()->getConfigData('catalog.products.settings.compare_option');
    $showMiniCart = (bool) core()->getConfigData('sales.checkout.shopping_cart.cart_page');
    $showWishlist = (bool) core()->getConfigData('customer.settings.wishlist.wishlist_option');

    $searchRoute = (function_exists('portal_shop_enabled') && portal_shop_enabled() && Route::has('shop.search.index'))
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
@endphp

<div class="flex flex-wrap gap-4 px-4 pt-4 pb-4 shadow-sm lg:hidden bg-white dark:bg-zinc-900 border-b border-[var(--border-subtle)]">
    <!-- Top Row: Hamburger + Logo + Right Utility Icons -->
    <div class="flex w-full items-center justify-between">
        <!-- Left Navigation: Hamburger & Logo -->
        <div class="flex items-center gap-x-3">
            {!! view_render_event('bagisto.portal.layout.header.mobile.drawer.before') !!}

            <button
                type="button"
                class="flex items-center justify-center cursor-pointer text-2xl text-[var(--text-primary)] focus:outline-none"
                @click="$refs.mobileMenuDrawer.open()"
                aria-label="Open navigation menu"
            >
                <span class="icon-hamburger text-2xl"></span>
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

        <!-- Right Navigation Icons -->
        <div class="flex items-center gap-x-4">
            <!-- Dark Mode Switcher -->
            <v-dark>
                <button
                    type="button"
                    class="text-2xl text-[var(--text-secondary)] hover:text-[var(--text-primary)] focus:outline-none"
                    aria-label="Toggle theme"
                >
                    <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark' }}"></span>
                </button>
            </v-dark>

            <!-- Compare Icon (Shop) -->
            @if ($showCompare && Route::has('shop.compare.index'))
                <a href="{{ route('shop.compare.index') }}" aria-label="@lang('shop::app.components.layouts.header.mobile.compare')">
                    <span class="text-2xl cursor-pointer icon-compare text-[var(--text-secondary)]"></span>
                </a>
            @endif

            <!-- Mini Cart (Shop) -->
            @if (function_exists('portal_shop_enabled') && portal_shop_enabled())
                @if (view()->exists('shop::checkout.cart.mini-cart'))
                    @include('shop::checkout.cart.mini-cart')
                @elseif (Route::has('shop.checkout.cart.index'))
                    <a href="{{ route('shop.checkout.cart.index') }}" aria-label="Shopping Cart">
                        <span class="text-2xl cursor-pointer icon-cart text-[var(--text-secondary)]"></span>
                    </a>
                @endif
            @endif

            <!-- User Profile Drawer Trigger -->
            <button
                type="button"
                class="text-2xl cursor-pointer icon-users text-[var(--text-secondary)] focus:outline-none"
                @click="$refs.mobileMenuDrawer.open()"
                aria-label="User Account"
            ></button>
        </div>
    </div>

    <!-- Bottom Row: Search Bar Full Width (Webkul Shop exact design) -->
    @if ($searchRoute)
        <div class="flex w-full items-center">
            <div class="relative w-full">
                <form action="{{ $searchRoute }}" method="GET" class="relative flex items-center" role="search">
                    <label for="mobile-search" class="sr-only">
                        @lang('portal::app.components.layouts.header.desktop.bottom.search')
                    </label>

                    <div class="icon-search pointer-events-none absolute top-3 flex items-center text-xl text-gray-400 ltr:left-3 rtl:right-3"></div>

                    <input
                        type="text"
                        id="mobile-search"
                        name="query"
                        value="{{ request('query') }}"
                        class="block w-full rounded-xl border border-transparent bg-zinc-100 dark:bg-zinc-800 py-3 px-11 text-xs font-medium text-gray-900 dark:text-gray-100 hover:border-gray-400 focus:border-gray-400 focus:outline-none max-md:rounded-lg transition-colors"
                        placeholder="@lang('portal::app.components.layouts.header.desktop.bottom.search-text')"
                        aria-label="@lang('portal::app.components.layouts.header.desktop.bottom.search-text')"
                    >
                </form>
            </div>
        </div>
    @endif

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
                        <p class="text-base font-dmserif text-[var(--text-primary)]">
                            @lang('shop::app.components.layouts.header.desktop.bottom.welcome')’
                            {{ $currentUser->name ?? $currentUser->first_name ?? 'User' }}
                        </p>
                        <p class="text-xs text-[var(--text-muted)] truncate mt-0.5">
                            {{ $currentUser->email }}
                        </p>

                        <div class="mt-3 grid gap-1 border-t border-[var(--border-subtle)] pt-2 text-xs font-medium">
                            @if (Route::has('customer.account.profile.index'))
                                <a href="{{ route('customer.account.profile.index') }}" class="py-1 text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                                    @lang('shop::app.components.layouts.header.desktop.bottom.profile')
                                </a>
                            @endif

                            @if (Route::has('customer.account.orders.index'))
                                <a href="{{ route('customer.account.orders.index') }}" class="py-1 text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                                    @lang('shop::app.components.layouts.header.desktop.bottom.orders')
                                </a>
                            @endif

                            @if ($isCustomerAuth && Route::has('customer.session.destroy'))
                                <form method="POST" action="{{ route('customer.session.destroy') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="py-1 text-red-600 dark:text-red-400 cursor-pointer">
                                        @lang('shop::app.components.layouts.header.desktop.bottom.logout')
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
                        <p class="text-base font-dmserif text-[var(--text-primary)]">
                            @lang('shop::app.components.layouts.header.desktop.bottom.welcome-guest')
                        </p>
                        <p class="text-xs text-[var(--text-muted)] mt-0.5">
                            @lang('shop::app.components.layouts.header.desktop.bottom.dropdown-text')
                        </p>

                        <div class="flex gap-2 mt-3">
                            @if ($loginUrl)
                                <a href="{{ $loginUrl }}" class="flex-1 py-2 text-center text-xs font-semibold text-white bg-navyBlue rounded-lg">
                                    @lang('shop::app.components.layouts.header.desktop.bottom.sign-in')
                                </a>
                            @endif

                            @if ($registerUrl)
                                <a href="{{ $registerUrl }}" class="flex-1 py-2 text-center text-xs font-semibold border border-zinc-300 dark:border-zinc-700 rounded-lg">
                                    @lang('shop::app.components.layouts.header.desktop.bottom.sign-up')
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Navigation Links -->
                <nav class="grid gap-1 pt-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--text-muted)] px-2 mb-1">
                        @lang('portal::app.home.nav.services')
                    </span>

                    @if ($hasAssignedMenuItems)
                        @foreach ($assignedMenu->rootItems as $item)
                            @if (filled($item->getTitle()))
                                @php
                                    $children = $item->children ? $item->children->where('status', 1)->filter(fn ($c) => filled($c->getTitle())) : collect();
                                    $hasChildren = $children->isNotEmpty();
                                @endphp

                                <div>
                                    <a
                                        href="{{ $item->getUrl() }}"
                                        target="{{ $item->target ?: '_self' }}"
                                        class="flex items-center justify-between px-3.5 py-2.5 rounded-lg text-sm font-medium text-[var(--text-primary)] hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors {{ $item->isActive() ? 'bg-zinc-100 dark:bg-zinc-800 font-bold text-[var(--brand-primary)]' : '' }}"
                                    >
                                        <span>{{ $item->getTitle() }}</span>
                                        @if ($hasChildren)
                                            <span class="icon-arrow-down text-xs opacity-60"></span>
                                        @endif
                                    </a>

                                    @if ($hasChildren)
                                        <div class="ltr:pl-4 rtl:pr-4 py-1 space-y-0.5 border-s border-zinc-200 dark:border-zinc-700 ltr:ml-3 rtl:mr-3">
                                            @foreach ($children as $child)
                                                <a
                                                    href="{{ $child->getUrl() }}"
                                                    target="{{ $child->target ?: '_self' }}"
                                                    class="block px-3 py-1.5 text-xs font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)]"
                                                >
                                                    {{ $child->getTitle() }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @else
                        <a href="{{ portal_home_url() }}" class="px-3.5 py-2.5 rounded-lg text-sm font-medium text-[var(--text-primary)] hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            @lang('portal::app.home.nav.home')
                        </a>
                        <a href="#services" class="px-3.5 py-2.5 rounded-lg text-sm font-medium text-[var(--text-primary)] hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            @lang('portal::app.home.nav.services')
                        </a>
                        <a href="#contact" class="px-3.5 py-2.5 rounded-lg text-sm font-medium text-[var(--text-primary)] hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            @lang('portal::app.home.nav.contact-us')
                        </a>
                    @endif
                </nav>

                <!-- Currencies & Languages Pickers in Drawer -->
                @if ($channel->locales->count() > 1 || $channel->currencies->count() > 1)
                    <div class="pt-3 border-t border-[var(--border-subtle)] space-y-3">
                        @if ($channel->locales->count() > 1)
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--text-muted)] px-2">
                                    @lang('portal::app.home.nav.languages')
                                </span>
                                <div class="mt-1.5 grid grid-cols-2 gap-1">
                                    @foreach ($channel->locales as $locale)
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

                        @if ($channel->currencies->count() > 1)
                            <div>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--text-muted)] px-2">
                                    العملات
                                </span>
                                <div class="mt-1.5 grid grid-cols-2 gap-1">
                                    @foreach ($channel->currencies as $currency)
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
</div>

{!! view_render_event('bagisto.portal.layout.header.mobile.after') !!}
