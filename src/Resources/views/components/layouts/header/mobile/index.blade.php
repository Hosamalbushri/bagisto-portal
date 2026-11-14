{!! view_render_event('bagisto.portal.layout.header.mobile.before') !!}

@php
    $channel = core()->getCurrentChannel();
    $channelName = $channel?->name ?? config('app.name');
    $assignedMenu = portal_menu_slot('header_mobile') ?: portal_menu_slot('header_main');
    $hasAssignedMenuItems = $assignedMenu && $assignedMenu->rootItems && $assignedMenu->rootItems->isNotEmpty();

    $searchRoute = (function_exists('portal_shop_enabled') && portal_shop_enabled() && Route::has('shop.search.index'))
        ? route('shop.search.index')
        : (Route::has('portal.search.index') ? route('portal.search.index') : null);

    $hasCustomerGuard = array_key_exists('customer', config('auth.guards', []));
    $isCustomerAuth = $hasCustomerGuard && auth()->guard('customer')->check();
    $portalGuard = \Platform\Portal\Helpers\PortalAuth::guard();
    $hasPortalGuard = array_key_exists($portalGuard, config('auth.guards', []));
    $isPortalAuth = \Platform\Portal\Helpers\PortalAuth::isEnabled() && $hasPortalGuard && auth()->guard($portalGuard)->check();
    $currentUser = $isCustomerAuth ? auth()->guard('customer')->user() : ($isPortalAuth ? auth()->guard($portalGuard)->user() : null);

    $loginUrl = Route::has('portal.auth.login')
        ? route('portal.auth.login')
        : (Route::has('customer.session.index') ? route('customer.session.index') : null);
    $registerUrl = Route::has('portal.auth.register')
        ? route('portal.auth.register')
        : (Route::has('customer.register.index') ? route('customer.register.index') : null);

    $hasLocales = $channel && isset($channel->locales) && count($channel->locales) > 1;
    $hasCurrencies = $channel && isset($channel->currencies) && count($channel->currencies) > 1;
@endphp

<div class="md:hidden">
    <!-- Mobile Top Navigation Bar -->
    <div
        class="portal-header-mobile flex items-center justify-between px-4 py-3 border-b border-[var(--border-subtle)] bg-[var(--header-bg,var(--bg-surface))] text-[var(--header-text,var(--text-primary))]"
    >
        <div class="flex items-center gap-3">
            <button
                type="button"
                class="flex items-center justify-center w-9 h-9 rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] transition-colors focus:outline-none cursor-pointer"
                @click="$refs.mobileMenuDrawer.open()"
                aria-label="Open navigation menu"
            >
                <span class="icon-hamburger text-lg"></span>
            </button>

            <a href="{{ portal_home_url() }}" class="flex items-center gap-2 focus:outline-none" aria-label="{{ $channelName }}">
                @if ($logo = $channel?->logo_url)
                    <img src="{{ $logo }}" class="h-7 w-auto max-h-[30px]" alt="{{ $channelName }}" />
                @else
                    <span class="text-base font-bold text-[var(--text-primary)] font-cairo">
                        {{ $channelName }}
                    </span>
                @endif
            </a>
        </div>

        <div class="flex items-center gap-2">
            <!-- Mobile Dark Mode Button -->
            <v-dark>
                <button
                    type="button"
                    class="flex items-center justify-center w-8 h-8 rounded-full border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] focus:outline-none cursor-pointer"
                    aria-label="Toggle theme"
                >
                    <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark text-[var(--text-secondary)]' }} text-base"></span>
                </button>
            </v-dark>

            <!-- Optional Mini-Cart (Shop) -->
            @if (function_exists('portal_shop_enabled') && portal_shop_enabled())
                @if (view()->exists('shop::checkout.cart.mini-cart'))
                    <div class="flex items-center">
                        @include('shop::checkout.cart.mini-cart')
                    </div>
                @elseif (Route::has('shop.checkout.cart.index'))
                    <a
                        href="{{ route('shop.checkout.cart.index') }}"
                        class="flex items-center justify-center w-8 h-8 rounded-full border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] focus:outline-none"
                        aria-label="Shopping Cart"
                    >
                        <span class="icon-cart text-base"></span>
                    </a>
                @endif
            @endif
        </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <x-portal::drawer
        position="left"
        width="300px"
        ref="mobileMenuDrawer"
    >
        <x-slot:header>
            <div class="flex items-center justify-between w-full">
                <a href="{{ portal_home_url() }}" class="flex items-center gap-2">
                    @if ($logo = $channel?->logo_url)
                        <img src="{{ $logo }}" class="h-7 w-auto max-h-[30px]" alt="{{ $channelName }}" />
                    @else
                        <span class="text-base font-bold text-[var(--text-primary)] font-cairo">
                            {{ $channelName }}
                        </span>
                    @endif
                </a>
            </div>
        </x-slot>

        <x-slot:content class="p-4 space-y-4">
            <!-- Search Bar in Mobile Drawer -->
            @if ($searchRoute)
                <div class="relative w-full">
                    <form action="{{ $searchRoute }}" method="GET" class="relative flex items-center">
                        <input
                            type="text"
                            name="query"
                            value="{{ request('query') }}"
                            placeholder="@lang('portal::app.components.layouts.header.desktop.bottom.search-text')"
                            class="w-full py-2 px-3.5 ltr:pl-9 rtl:pr-9 text-xs rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface-elevated)] text-[var(--text-primary)] placeholder-[var(--text-muted)] focus:outline-none focus:border-[var(--brand-primary)] transition-colors"
                        >
                        <span class="icon-search absolute ltr:left-3 rtl:right-3 text-sm text-[var(--text-muted)] pointer-events-none"></span>
                    </form>
                </div>
            @endif

            <!-- User Authentication Section in Mobile Drawer -->
            @if ($currentUser)
                <div class="p-3 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)]">
                    <div class="flex items-center gap-2.5">
                        <span class="icon-avatar text-xl text-[var(--brand-primary)]"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-[var(--text-primary)] truncate">
                                {{ $currentUser->name ?? $currentUser->first_name ?? 'User' }}
                            </p>
                            <p class="text-[11px] text-[var(--text-muted)] truncate">
                                {{ $currentUser->email }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-2.5 pt-2 border-t border-[var(--border-subtle)] flex items-center justify-between text-xs">
                        @if (Route::has('customer.account.profile.index'))
                            <a href="{{ route('customer.account.profile.index') }}" class="font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)]">
                                @lang('shop::app.components.layouts.header.desktop.top.profile')
                            </a>
                        @else
                            <span></span>
                        @endif

                        @if ($isCustomerAuth && Route::has('customer.session.destroy'))
                            <form method="POST" action="{{ route('customer.session.destroy') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 dark:text-red-400 cursor-pointer">
                                    @lang('shop::app.components.layouts.header.desktop.top.logout')
                                </button>
                            </form>
                        @elseif ($isPortalAuth && Route::has('portal.auth.logout'))
                            <form method="POST" action="{{ route('portal.auth.logout') }}">
                                @csrf
                                <button type="submit" class="font-medium text-red-600 dark:text-red-400 cursor-pointer">
                                    @lang('portal::app.auth.logout.title')
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2">
                    @if ($loginUrl)
                        <a
                            href="{{ $loginUrl }}"
                            class="flex items-center justify-center px-4 py-2 rounded-xl text-xs font-semibold text-[var(--text-primary)] bg-[var(--bg-surface-elevated)] hover:bg-[var(--bg-surface-muted)] border border-[var(--border-subtle)] transition-colors"
                        >
                            @lang('portal::app.auth.login.title')
                        </a>
                    @endif

                    @if ($registerUrl)
                        <a
                            href="{{ $registerUrl }}"
                            class="btn-primary flex items-center justify-center px-4 py-2 text-xs font-semibold shadow-xs"
                            style="border-radius: var(--btn-radius, 0.75rem);"
                        >
                            @lang('portal::app.auth.register.title')
                        </a>
                    @endif
                </div>
            @endif

            <!-- Mobile Navigation Links -->
            <nav class="grid gap-1 border-t border-[var(--border-subtle)] pt-3">
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
                                    class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $item->isActive() ? 'bg-[var(--brand-primary-light)] text-[var(--brand-primary)] font-semibold' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)]' }}"
                                >
                                    <span>{{ $item->getTitle() }}</span>
                                    @if ($hasChildren)
                                        <span class="icon-arrow-down text-xs opacity-60"></span>
                                    @endif
                                </a>

                                @if ($hasChildren)
                                    <div class="ltr:pl-4 rtl:pr-4 py-1 space-y-0.5 border-s border-[var(--border-subtle)] ltr:ml-3 rtl:mr-3">
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
                    <a
                        href="{{ portal_home_url() }}"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('portal.home') ? 'bg-[var(--brand-primary-light)] text-[var(--brand-primary)] font-semibold' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)]' }} transition-colors"
                    >
                        @lang('portal::app.home.nav.home')
                    </a>
                    <a
                        href="#services"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] transition-colors"
                    >
                        @lang('portal::app.home.nav.services')
                    </a>
                    <a
                        href="#contact"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] transition-colors"
                    >
                        @lang('portal::app.home.nav.contact-us')
                    </a>
                @endif
            </nav>

            <!-- Language & Currency Selection in Drawer -->
            @if ($hasLocales || $hasCurrencies)
                <div class="border-t border-[var(--border-subtle)] pt-3 space-y-3">
                    @if ($hasLocales)
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--text-muted)] px-1">
                                @lang('portal::app.home.nav.languages')
                            </span>
                            <div class="mt-1.5 grid grid-cols-2 gap-1.5">
                                @foreach ($channel->locales as $locale)
                                    <a
                                        href="{{ route('portal.locale.switch', $locale->code) }}"
                                        class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs transition-colors {{ app()->getLocale() == $locale->code ? 'font-bold text-[var(--brand-primary)] bg-[var(--brand-primary-light)]' : 'text-[var(--text-secondary)] bg-[var(--bg-surface-elevated)] hover:text-[var(--text-primary)]' }}"
                                    >
                                        @if (! empty($locale->logo_url))
                                            <img src="{{ $locale->logo_url }}" class="h-3.5 w-3.5 rounded-full" alt="{{ $locale->name }}">
                                        @endif
                                        <span>{{ $locale->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($hasCurrencies)
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-[var(--text-muted)] px-1">
                                العملات
                            </span>
                            <div class="mt-1.5 grid grid-cols-2 gap-1.5">
                                @foreach ($channel->currencies as $currency)
                                    <a
                                        href="{{ route('portal.currency.switch', $currency->code) }}"
                                        class="flex items-center justify-center px-3 py-2 rounded-xl text-xs transition-colors {{ core()->getCurrentCurrencyCode() == $currency->code ? 'font-bold text-[var(--brand-primary)] bg-[var(--brand-primary-light)]' : 'text-[var(--text-secondary)] bg-[var(--bg-surface-elevated)] hover:text-[var(--text-primary)]' }}"
                                    >
                                        {{ $currency->code }} ({{ $currency->symbol }})
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Dark Mode Switcher in Drawer -->
            <div class="border-t border-[var(--border-subtle)] pt-3">
                <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)]">
                    <span class="text-xs font-medium text-[var(--text-secondary)]">
                        @lang('portal::app.home.theme.dark-mode')
                    </span>

                    <v-dark>
                        <button
                            type="button"
                            class="flex items-center justify-center w-8 h-8 rounded-full border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-all focus:outline-none cursor-pointer"
                            title="{{ request()->cookie('portal_dark_mode') ? trans('portal::app.home.theme.light-mode') : trans('portal::app.home.theme.dark-mode') }}"
                            aria-label="Toggle theme"
                        >
                            <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark text-[var(--text-secondary)]' }} text-base"></span>
                        </button>
                    </v-dark>
                </div>
            </div>
        </x-slot>
    </x-portal::drawer>
</div>

{!! view_render_event('bagisto.portal.layout.header.mobile.after') !!}
