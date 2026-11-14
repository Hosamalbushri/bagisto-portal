@php
    $headerSettings = portal_layout_settings('header');
    $assignedMenu = portal_menu_slot('header_main');
    $hasAssignedMenuItems = $assignedMenu && $assignedMenu->rootItems && $assignedMenu->rootItems->isNotEmpty();
    $channelName = core()->getCurrentChannel()->name ?? config('app.name');
@endphp

{!! view_render_event('bagisto.portal.layout.header.mobile.before') !!}

<div class="md:hidden">
    <!-- Mobile Top Navigation Bar -->
    <div
        class="portal-header-mobile flex items-center justify-between px-4 py-3 border-b border-[var(--border-subtle)] bg-[var(--header-bg,var(--bg-surface))] text-[var(--header-text,var(--text-primary))]"
    >
        <div class="flex items-center gap-3">
            <button
                type="button"
                class="flex items-center justify-center w-9 h-9 rounded-[var(--btn-radius,0.5rem)] border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] transition-colors focus:outline-none"
                @click="$refs.mobileMenuDrawer.open()"
                aria-label="Open navigation menu"
            >
                <span class="icon-hamburger text-lg"></span>
            </button>

            <a href="{{ portal_home_url() }}" class="flex items-center gap-2 focus:outline-none" aria-label="{{ $channelName }}">
                @if ($logo = core()->getCurrentChannel()->logo_url)
                    <img src="{{ $logo }}" class="h-7 w-auto" alt="{{ $channelName }}" />
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
                    class="flex items-center justify-center w-8 h-8 rounded-full border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] focus:outline-none"
                    aria-label="Toggle theme"
                >
                    <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark text-[var(--text-secondary)]' }} text-base"></span>
                </button>
            </v-dark>

            <!-- Mobile Mini-Cart (when Shop mode enabled) -->
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
        width="290px"
        ref="mobileMenuDrawer"
    >
        <x-slot:header>
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)] flex items-center justify-center p-1 shadow-2xs">
                        <svg viewBox="0 0 64 64" fill="none" class="w-full h-full" aria-hidden="true">
                            <path d="M18 44V20H32C36.4183 20 40 23.5817 40 28C40 32.4183 36.4183 36 32 36H24" stroke="#1c66f6" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M30 36L44 44" stroke="#fe8312" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="44" cy="24" r="4.5" fill="#fe8312" />
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-base font-bold text-[var(--text-primary)] font-cairo leading-none">
                            {{ $channelName }}
                        </span>
                        <span class="text-[8px] font-mono uppercase tracking-wider text-[var(--text-muted)] mt-0.5">
                            Software Systems
                        </span>
                    </div>
                </span>
            </div>
        </x-slot>

        <x-slot:content class="p-4 space-y-4">
            <!-- Dark Mode Row in Drawer -->
            <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)]">
                <span class="text-sm font-medium text-[var(--text-secondary)]">
                    @lang('portal::app.home.theme.dark-mode')
                </span>

                <v-dark>
                    <button
                        type="button"
                        class="flex items-center justify-center w-9 h-9 rounded-full border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:border-[var(--brand-primary)]/40 hover:bg-[var(--bg-surface-elevated)] transition-all focus:outline-none"
                        title="{{ request()->cookie('portal_dark_mode') ? trans('portal::app.home.theme.light-mode') : trans('portal::app.home.theme.dark-mode') }}"
                        aria-label="Toggle theme"
                    >
                        <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark text-[var(--text-secondary)]' }} text-lg"></span>
                    </button>
                </v-dark>
            </div>

            @if (\Platform\Portal\Helpers\PortalAuth::isEnabled() && Route::has('portal.auth.login'))
                @php($portalGuard = \Platform\Portal\Helpers\PortalAuth::guard())

                @if (auth()->guard($portalGuard)->check())
                    <div class="px-4 py-3 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] flex items-center justify-between">
                        <span class="text-sm font-semibold text-[var(--text-primary)]">
                            {{ auth()->guard($portalGuard)->user()->name ?? auth()->guard($portalGuard)->user()->first_name ?? 'User' }}
                        </span>

                        @if (Route::has('portal.auth.logout'))
                            <form method="POST" action="{{ route('portal.auth.logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="text-xs font-semibold text-red-600 dark:text-red-400 cursor-pointer"
                                >
                                    @lang('portal::app.auth.logout.title')
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-2">
                        @if (Route::has('portal.auth.login'))
                            <a
                                href="{{ route('portal.auth.login') }}"
                                class="flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-[var(--text-primary)] bg-[var(--bg-surface-elevated)] hover:bg-[var(--bg-surface-muted)] border border-[var(--border-subtle)] transition-colors"
                            >
                                @lang('portal::app.auth.login.title')
                            </a>
                        @endif

                        @if (Route::has('portal.auth.register'))
                            <a
                                href="{{ route('portal.auth.register') }}"
                                class="btn-primary flex items-center justify-center px-4 py-2.5 text-sm font-semibold shadow-sm"
                                style="border-radius: var(--btn-radius, 0.5rem);"
                            >
                                @lang('portal::app.auth.register.title')
                            </a>
                        @endif
                    </div>
                @endif
            @endif

            <!-- Mobile Navigation Links -->
            <nav class="grid gap-1 border-t border-[var(--border-subtle)] pt-3">
                @if ($hasAssignedMenuItems)
                    <x-portal::menu-slot
                        id="header_mobile"
                        location="header"
                        direction="column"
                    />
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

                <!-- Language Switcher in Mobile Drawer -->
                @if (core()->getCurrentChannel()->locales->count() > 1)
                    <div class="mt-4 pt-4 border-t border-[var(--border-subtle)]">
                        <span class="text-xs font-semibold uppercase text-[var(--text-muted)] px-3">
                            @lang('portal::app.home.nav.languages')
                        </span>
                        <div class="mt-2 grid gap-1">
                            @foreach (core()->getCurrentChannel()->locales as $locale)
                                <a
                                    href="{{ route('portal.locale.switch', $locale->code) }}"
                                    class="flex items-center px-3.5 py-2 rounded-xl text-sm {{ app()->getLocale() == $locale->code ? 'font-bold text-[var(--brand-primary)] bg-[var(--brand-primary-light)]' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-muted)]' }}"
                                >
                                    {{ $locale->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Direct Consultation Button in Drawer -->
                <div class="mt-4 pt-3 border-t border-[var(--border-subtle)]">
                    <a
                        href="#contact"
                        class="btn-primary w-full flex items-center justify-center gap-2 !py-2.5 !text-sm"
                        style="border-radius: var(--btn-radius, 0.5rem);"
                    >
                        <span>@lang('portal::app.sections.hero_scaffold.default_primary_cta')</span>
                        <span class="icon-arrow-right rtl:rotate-180 text-xs"></span>
                    </a>
                </div>
            </nav>
        </x-slot>
    </x-portal::drawer>
</div>

{!! view_render_event('bagisto.portal.layout.header.mobile.after') !!}
