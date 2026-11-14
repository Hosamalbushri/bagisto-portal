@php
    $headerSettings = portal_layout_settings('header');
    $isSticky = (bool) data_get($headerSettings, 'controls.sticky', true);
    $showDarkMode = (bool) data_get($headerSettings, 'controls.show_dark_mode', true);
    $showAuth = (bool) data_get($headerSettings, 'controls.show_auth', true);

    $assignedMenu = portal_menu_slot('header_main');
    $hasAssignedMenuItems = $assignedMenu && $assignedMenu->rootItems && $assignedMenu->rootItems->isNotEmpty();
    $channelName = core()->getCurrentChannel()->name ?? config('app.name');
@endphp

{!! view_render_event('bagisto.portal.layout.header.desktop.bottom.before') !!}

<div
    class="portal-header-bottom relative z-50 backdrop-blur-md border-b border-[var(--border-subtle)] shadow-[0_1px_12px_rgba(0,0,0,0.03)] dark:shadow-[0_4px_20px_rgba(0,0,0,0.3)] transition-all duration-300"
    style="background-color: var(--header-bg, var(--bg-surface)); color: var(--header-text, var(--text-primary)); min-height: var(--header-height, 4rem);"
>
    <div class="portal-container mx-auto flex items-center justify-between gap-4" style="max-width: var(--container-max-width, 1280px); min-height: var(--header-height, 4rem); padding-inline: var(--page-padding-x, 1rem);">
        <!-- Left: Brand Logo & Monogram -->
        <div class="flex items-center gap-3 sm:gap-4 shrink-0 min-w-0">
            <a href="{{ portal_home_url() }}" class="flex items-center gap-3 group focus:outline-none select-none shrink-0" aria-label="{{ $channelName }}">
                @if ($logo = core()->getCurrentChannel()->logo_url)
                    <img id="logo-image" src="{{ $logo }}" class="h-8 w-auto transition-transform group-hover:scale-105" alt="{{ $channelName }}" />
                @else
                    <div class="w-9 h-9 rounded-[var(--btn-radius,0.5rem)] bg-[var(--bg-surface)] border border-[var(--border-subtle)] flex items-center justify-center p-1.5 shadow-2xs group-hover:border-[var(--brand-primary)] transition-colors duration-200 shrink-0">
                        <svg viewBox="0 0 64 64" fill="none" class="w-full h-full" aria-hidden="true">
                            <path d="M18 44V20H32C36.4183 20 40 23.5817 40 28C40 32.4183 36.4183 36 32 36H24" stroke="#1c66f6" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M30 36L44 44" stroke="#fe8312" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="44" cy="24" r="4.5" fill="#fe8312" />
                        </svg>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-base sm:text-lg font-black tracking-tight text-[var(--text-primary)] leading-none font-cairo">
                            {{ $channelName }}
                        </span>
                        <span class="text-[9px] font-mono uppercase tracking-wider text-[var(--text-muted)] leading-tight mt-1">
                            Software Systems
                        </span>
                    </div>
                @endif
            </a>
        </div>

        <!-- Center: Floating Capsule Navigation Bar -->
        <nav class="hidden md:flex items-center gap-1 px-3 py-1.5 rounded-full bg-[var(--bg-surface)]/80 backdrop-blur-sm border border-[var(--border-subtle)] shadow-xs" aria-label="Main Navigation">
            @if ($hasAssignedMenuItems)
                <x-portal::menu
                    :menu="$assignedMenu"
                    class="flex items-center"
                    list-class="flex items-center gap-1 text-sm"
                    item-class="!px-3.5 !py-1.5 !text-sm font-medium !text-[var(--text-secondary)] hover:!text-[var(--text-primary)] transition-colors !rounded-full hover:!bg-[var(--bg-surface-elevated)]"
                    active-class="!bg-[var(--brand-primary-light)] !text-[var(--brand-primary)] !font-semibold"
                />
            @else
                <div class="flex items-center gap-1">
                    <a
                        href="{{ portal_home_url() }}"
                        class="px-3.5 py-1.5 text-sm font-medium rounded-full transition-colors {{ request()->routeIs('portal.home') ? 'bg-[var(--brand-primary-light)] text-[var(--brand-primary)] font-semibold' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)]' }}"
                    >
                        @lang('portal::app.home.nav.home')
                    </a>
                    <a
                        href="#services"
                        class="px-3.5 py-1.5 text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors rounded-full hover:bg-[var(--bg-surface-elevated)]"
                    >
                        @lang('portal::app.home.nav.services')
                    </a>
                    <a
                        href="#contact"
                        class="px-3.5 py-1.5 text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors rounded-full hover:bg-[var(--bg-surface-elevated)]"
                    >
                        @lang('portal::app.home.nav.contact-us')
                    </a>
                </div>
            @endif
        </nav>

        <!-- Search Bar Container (Desktop) -->
        @php
            $searchAction = (function_exists('portal_shop_enabled') && portal_shop_enabled() && Route::has('shop.search.index'))
                ? route('shop.search.index')
                : (Route::has('portal.search.index') ? route('portal.search.index') : null);
        @endphp

        @if ($searchAction)
            <div class="hidden lg:block relative w-48 xl:w-60">
                <form action="{{ $searchAction }}" method="GET" class="relative flex items-center">
                    <input
                        type="text"
                        name="query"
                        value="{{ request('query') }}"
                        placeholder="@lang('portal::app.components.layouts.header.desktop.bottom.search-text')"
                        class="w-full py-1.5 px-3 ltr:pl-8 rtl:pr-8 text-xs rounded-full border border-[var(--border-subtle)] bg-[var(--bg-surface-elevated)] text-[var(--text-primary)] placeholder-[var(--text-muted)] focus:outline-none focus:border-[var(--brand-primary)] transition-colors"
                        aria-label="@lang('portal::app.components.layouts.header.desktop.bottom.search-text')"
                    >
                    <span class="icon-search absolute ltr:left-2.5 rtl:right-2.5 text-xs text-[var(--text-muted)] pointer-events-none"></span>
                </form>
            </div>
        @endif

        <!-- Right Side: Utility Controls (Theme + CTA + Shop / Mini-Cart) -->
        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
            <!-- Dark Mode Switcher -->
            @if ($showDarkMode)
                <v-dark>
                    <button
                        type="button"
                        class="flex items-center justify-center w-9 h-9 rounded-full border border-[var(--border-subtle)] bg-[var(--bg-surface)]/80 text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:border-[var(--brand-primary)]/40 hover:bg-[var(--bg-surface-elevated)] transition-all focus:outline-none shadow-2xs"
                        title="{{ request()->cookie('portal_dark_mode') ? trans('portal::app.home.theme.light-mode') : trans('portal::app.home.theme.dark-mode') }}"
                        aria-label="Toggle theme"
                    >
                        <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark text-[var(--text-secondary)]' }} text-lg"></span>
                    </button>
                </v-dark>
            @endif

            <!-- Direct Consultation Primary CTA Button -->
            <a
                href="#contact"
                class="btn-primary !px-4 !py-2 !text-xs hidden lg:inline-flex items-center gap-1.5 shadow-sm hover:shadow-[var(--shadow-glow)] transition-all"
                style="border-radius: var(--btn-radius, 0.5rem);"
            >
                <span>@lang('portal::app.sections.hero_scaffold.default_primary_cta')</span>
                <span class="icon-arrow-right rtl:rotate-180 text-xs"></span>
            </a>

            <!-- Optional Storefront Authentication -->
            @if ($showAuth && \Platform\Portal\Helpers\PortalAuth::isEnabled() && Route::has('portal.auth.login'))
                @php
                    $portalGuard = \Platform\Portal\Helpers\PortalAuth::guard();
                @endphp

                @if (auth()->guard($portalGuard)->check())
                    <x-portal::dropdown position="bottom-right">
                        <x-slot:toggle>
                            <button
                                type="button"
                                class="flex items-center gap-1.5 h-9 px-3 text-xs font-semibold text-[var(--text-primary)] bg-[var(--bg-surface)] hover:bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] rounded-full transition-all shadow-2xs focus:outline-none"
                            >
                                <span class="icon-avatar text-base"></span>
                                <span class="truncate max-w-[100px]">{{ auth()->guard($portalGuard)->user()->name ?? auth()->guard($portalGuard)->user()->first_name ?? 'User' }}</span>
                                <span class="icon-arrow-down text-xs opacity-70"></span>
                            </button>
                        </x-slot>

                        <x-slot:content class="portal-card !p-1.5 min-w-[140px] shadow-[var(--shadow-card)] border border-[var(--border-subtle)] bg-[var(--bg-surface)]" style="border-radius: var(--card-radius, 0.75rem);">
                            @if (Route::has('portal.auth.logout'))
                                <form method="POST" action="{{ route('portal.auth.logout') }}">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full flex items-center px-3.5 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors cursor-pointer text-start"
                                    >
                                        @lang('portal::app.auth.logout.title')
                                    </button>
                                </form>
                            @endif
                        </x-slot:content>
                    </x-portal::dropdown>
                @else
                    <div class="flex items-center gap-1 ltr:ml-1 rtl:mr-1">
                        @if (Route::has('portal.auth.login'))
                            <a
                                href="{{ route('portal.auth.login') }}"
                                class="px-3 py-1.5 text-xs font-semibold text-[var(--text-secondary)] hover:text-[var(--brand-primary)] rounded-full hover:bg-[var(--bg-surface-elevated)] transition-colors"
                            >
                                @lang('portal::app.auth.login.title')
                            </a>
                        @endif

                        @if (Route::has('portal.auth.register'))
                            <a
                                href="{{ route('portal.auth.register') }}"
                                class="btn-primary !px-3.5 !py-1.5 !text-xs font-semibold shadow-xs"
                                style="border-radius: var(--btn-radius, 0.5rem);"
                            >
                                @lang('portal::app.auth.register.title')
                            </a>
                        @endif
                    </div>
                @endif
            @endif

            <!-- Seamless Bagisto E-Commerce / Shop Integration -->
            @if (function_exists('portal_shop_enabled') && portal_shop_enabled())
                <!-- Mini-Cart Drawer Trigger -->
                @if (view()->exists('shop::checkout.cart.mini-cart'))
                    <div class="flex items-center">
                        @include('shop::checkout.cart.mini-cart')
                    </div>
                @elseif (Route::has('shop.checkout.cart.index'))
                    <a
                        href="{{ route('shop.checkout.cart.index') }}"
                        class="relative flex items-center justify-center w-9 h-9 rounded-full border border-[var(--border-subtle)] bg-[var(--bg-surface)]/80 text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:border-[var(--brand-primary)]/40 hover:bg-[var(--bg-surface-elevated)] transition-all focus:outline-none shadow-2xs"
                        aria-label="Shopping Cart"
                    >
                        <span class="icon-cart text-lg"></span>
                    </a>
                @endif

                <!-- Customer Account Navigation -->
                @if (auth()->guard('customer')->check())
                    <x-portal::dropdown position="bottom-right">
                        <x-slot:toggle>
                            <button
                                type="button"
                                class="flex items-center gap-1.5 h-9 px-3 text-xs font-semibold text-[var(--text-primary)] bg-[var(--bg-surface)] hover:bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] rounded-full transition-all shadow-2xs focus:outline-none"
                            >
                                <span class="icon-avatar text-base"></span>
                                <span class="truncate max-w-[100px]">{{ auth()->guard('customer')->user()->first_name ?? 'Account' }}</span>
                                <span class="icon-arrow-down text-xs opacity-70"></span>
                            </button>
                        </x-slot>

                        <x-slot:content class="portal-card !p-1.5 min-w-[160px] shadow-[var(--shadow-card)] border border-[var(--border-subtle)] bg-[var(--bg-surface)]" style="border-radius: var(--card-radius, 0.75rem);">
                            @if (Route::has('customer.account.profile.index'))
                                <a
                                    href="{{ route('customer.account.profile.index') }}"
                                    class="flex items-center px-3.5 py-2 text-xs font-medium text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] rounded-lg transition-colors"
                                >
                                    @lang('shop::app.components.layouts.header.desktop.top.profile')
                                </a>
                            @endif

                            @if (Route::has('customer.account.orders.index'))
                                <a
                                    href="{{ route('customer.account.orders.index') }}"
                                    class="flex items-center px-3.5 py-2 text-xs font-medium text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] rounded-lg transition-colors"
                                >
                                    @lang('shop::app.components.layouts.header.desktop.top.orders')
                                </a>
                            @endif

                            @if (Route::has('customer.account.wishlist.index'))
                                <a
                                    href="{{ route('customer.account.wishlist.index') }}"
                                    class="flex items-center px-3.5 py-2 text-xs font-medium text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] rounded-lg transition-colors"
                                >
                                    @lang('shop::app.components.layouts.header.desktop.top.wishlist')
                                </a>
                            @endif

                            @if (Route::has('customer.session.destroy'))
                                <form method="POST" action="{{ route('customer.session.destroy') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="w-full flex items-center px-3.5 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors cursor-pointer text-start"
                                    >
                                        @lang('shop::app.components.layouts.header.desktop.top.logout')
                                    </button>
                                </form>
                            @endif
                        </x-slot:content>
                    </x-portal::dropdown>
                @elseif (Route::has('customer.session.index'))
                    <a
                        href="{{ route('customer.session.index') }}"
                        class="px-3 py-1.5 text-xs font-semibold text-[var(--text-secondary)] hover:text-[var(--brand-primary)] rounded-full hover:bg-[var(--bg-surface-elevated)] transition-colors"
                    >
                        @lang('shop::app.components.layouts.header.desktop.top.sign-in')
                    </a>
                @endif
            @endif
        </div>
    </div>
</div>

{!! view_render_event('bagisto.portal.layout.header.desktop.bottom.after') !!}
