@php
    $headerSettings = portal_layout_settings('header');
    $isAnnouncementActive = (bool) data_get($headerSettings, 'announcement.status', false);
    $announcementText = data_get($headerSettings, 'announcement.text');
    $announcementLink = data_get($headerSettings, 'announcement.link');
    $announcementBg = data_get($headerSettings, 'announcement.bg_color', '#2563eb');
    $announcementTextCol = data_get($headerSettings, 'announcement.text_color', '#ffffff');

    $isSticky = (bool) data_get($headerSettings, 'controls.sticky', true);
    $showDarkMode = (bool) data_get($headerSettings, 'controls.show_dark_mode', true);
    $showLocales = (bool) data_get($headerSettings, 'controls.show_locales', true);
    $showAuth = (bool) data_get($headerSettings, 'controls.show_auth', true);
@endphp

{!! view_render_event('bagisto.shop.layout.header.before') !!}

@if ($isAnnouncementActive && ! empty($announcementText))
    <div
        class="py-2 px-4 text-center text-xs sm:text-sm font-medium transition-colors"
        style="background-color: {{ $announcementBg }}; color: {{ $announcementTextCol }};"
    >
        <div class="max-w-7xl mx-auto flex items-center justify-center gap-2">
            @if ($announcementLink)
                <a href="{{ $announcementLink }}" class="hover:underline flex items-center gap-1.5">
                    <span>{{ $announcementText }}</span>
                    <span class="icon-arrow-right text-xs"></span>
                </a>
            @else
                <span>{{ $announcementText }}</span>
            @endif
        </div>
    </div>
@endif

<header class="{{ $isSticky ? 'sticky top-0' : 'relative' }} z-50 bg-[var(--glass-bg)] backdrop-blur-xl border-b border-[var(--border-subtle)] shadow-[0_1px_12px_rgba(0,0,0,0.03)] dark:shadow-[0_4px_20px_rgba(0,0,0,0.4)] transition-all duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <!-- Left: Hamburger (Mobile) + Brand Logo + Desktop Nav -->
        <div class="flex items-center gap-3 sm:gap-6 min-w-0">
            <!-- Mobile Menu Toggle Button -->
            <button
                type="button"
                class="md:hidden flex items-center justify-center w-9 h-9 rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)] hover:text-[var(--text-primary)] transition-colors focus:outline-none"
                @click="$refs.mobileMenuDrawer.open()"
                aria-label="Open navigation menu"
            >
                <span class="icon-menu text-xl"></span>
            </button>

            <!-- Brand Logo -->
            <a href="{{ portal_home_url() }}" class="flex items-center gap-2.5 shrink-0 transition-transform hover:scale-[1.02]">
                @if ($logo = core()->getCurrentChannel()->logo_url)
                    <img id="logo-image" src="{{ $logo }}" class="h-8 w-auto" alt="{{ config('app.name') }}" />
                @else
                    <span class="flex items-center gap-2">
                        <svg class="h-7 w-7 rounded-lg shrink-0 shadow-sm" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="32" height="32" rx="8" fill="#1c66f6"/>
                            <path d="M10 8H20C21.6569 8 23 9.34315 23 11C23 12.6569 21.6569 14 20 14H10V8Z" fill="#fe8312"/>
                            <path d="M10 14H19L23 24H18L15 16.5H10V24H7V8H10V14Z" fill="white"/>
                        </svg>
                        <span class="text-xl font-bold tracking-tight text-[var(--text-primary)] font-cairo">
                            {{ core()->getCurrentChannel()->name ?? config('app.name') }}
                        </span>
                    </span>
                @endif
            </a>

            <!-- Desktop Navigation Links via Dynamic Menu Slot -->
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium ltr:ml-4 rtl:mr-4">
                <x-portal::menu-slot
                    id="header_main"
                    location="header"
                    class="flex items-center gap-6"
                />
            </nav>
        </div>

        <!-- Right: Actions (Theme Switcher + Locales + Optional Auth) -->
        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
            <!-- Dark Mode Switcher (Standard Bagisto Admin Pattern) -->
            @if ($showDarkMode)
                <v-dark>
                    <button
                        type="button"
                        class="flex items-center justify-center w-9 h-9 rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:border-[var(--brand-primary)]/40 hover:bg-[var(--bg-surface-elevated)] transition-all focus:outline-none"
                        title="{{ request()->cookie('portal_dark_mode') ? trans('portal::app.home.theme.light-mode') : trans('portal::app.home.theme.dark-mode') }}"
                        aria-label="Toggle theme"
                    >
                        <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark' }} text-lg"></span>
                    </button>
                </v-dark>
            @endif

            <!-- Locales Dropdown -->
            @if ($showLocales && core()->getCurrentChannel()->locales->count() > 1)
                <x-portal::dropdown position="bottom-right">
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="flex items-center gap-1.5 h-9 px-3 text-xs font-semibold text-[var(--text-primary)] bg-[var(--bg-surface-elevated)] hover:bg-[var(--bg-surface-muted)] border border-[var(--border-subtle)] rounded-xl transition-all shadow-sm focus:outline-none"
                        >
                            <span>{{ core()->getCurrentLocale()->name }}</span>
                            <span class="icon-arrow-down text-xs opacity-70"></span>
                        </button>
                    </x-slot>

                    <x-slot:content class="!p-1.5 min-w-[140px] shadow-[var(--shadow-card)] rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)]">
                        @foreach (core()->getCurrentChannel()->locales as $locale)
                            <a
                                href="{{ route('portal.locale.switch', $locale->code) }}"
                                class="flex items-center px-3.5 py-2 text-xs font-medium rounded-lg transition-colors {{ app()->getLocale() == $locale->code ? 'font-bold text-[var(--brand-primary)] bg-[var(--brand-primary-light)]' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-muted)]' }}"
                            >
                                {{ $locale->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-portal::dropdown>
            @endif

            <!-- Optional Storefront Authentication (Plug-and-Play) -->
            @if ($showAuth && \Platform\Portal\Helpers\PortalAuth::isEnabled())
                @php($portalGuard = \Platform\Portal\Helpers\PortalAuth::guard())

                @if (auth()->guard($portalGuard)->check())
                    <x-portal::dropdown position="bottom-right">
                        <x-slot:toggle>
                            <button
                                type="button"
                                class="flex items-center gap-1.5 h-9 px-3 text-xs font-semibold text-[var(--text-primary)] bg-[var(--bg-surface-elevated)] hover:bg-[var(--bg-surface-muted)] border border-[var(--border-subtle)] rounded-xl transition-all shadow-sm focus:outline-none"
                            >
                                <span class="icon-avatar text-base"></span>
                                <span class="truncate max-w-[100px]">{{ auth()->guard($portalGuard)->user()->name }}</span>
                                <span class="icon-arrow-down text-xs opacity-70"></span>
                            </button>
                        </x-slot>

                        <x-slot:content class="!p-1.5 min-w-[140px] shadow-[var(--shadow-card)] rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)]">
                            <a
                                href="{{ route('portal.auth.logout') }}"
                                class="flex items-center px-3.5 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors"
                            >
                                @lang('portal::app.auth.logout.title')
                            </a>
                        </x-slot>
                    </x-portal::dropdown>
                @else
                    <div class="flex items-center gap-1.5 ltr:ml-1 rtl:mr-1">
                        <a
                            href="{{ route('portal.auth.login') }}"
                            class="btn-ghost !px-3 !py-1.5 !text-xs font-semibold"
                        >
                            @lang('portal::app.auth.login.title')
                        </a>

                        <a
                            href="{{ route('portal.auth.register') }}"
                            class="btn-primary !px-3.5 !py-1.5 !text-xs font-semibold shadow-xs"
                        >
                            @lang('portal::app.auth.register.title')
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>
</header>

<!-- Mobile Navigation Drawer -->
<x-portal::drawer
    position="left"
    width="280px"
    ref="mobileMenuDrawer"
>
    <x-slot:header>
        <div class="flex items-center justify-between">
            <span class="flex items-center gap-2">
                <svg class="h-6 w-6 rounded-md shrink-0 shadow-sm" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="32" height="32" rx="8" fill="#1c66f6"/>
                    <path d="M10 8H20C21.6569 8 23 9.34315 23 11C23 12.6569 21.6569 14 20 14H10V8Z" fill="#fe8312"/>
                    <path d="M10 14H19L23 24H18L15 16.5H10V24H7V8H10V14Z" fill="white"/>
                </svg>
                <span class="text-lg font-bold text-[var(--text-primary)] font-cairo">
                    {{ core()->getCurrentChannel()->name ?? config('app.name') }}
                </span>
            </span>
        </div>
    </x-slot>

    <x-slot:content class="p-4 space-y-4">
        <!-- Dark Mode Switcher Row in Mobile Drawer -->
        <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)]">
            <span class="text-sm font-medium text-[var(--text-secondary)]">
                @lang('portal::app.home.theme.dark-mode')
            </span>

            <v-dark>
                <button
                    type="button"
                    class="flex items-center justify-center w-9 h-9 rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:border-[var(--brand-primary)]/40 hover:bg-[var(--bg-surface-elevated)] transition-all focus:outline-none"
                    title="{{ request()->cookie('portal_dark_mode') ? trans('portal::app.home.theme.light-mode') : trans('portal::app.home.theme.dark-mode') }}"
                    aria-label="Toggle theme"
                >
                    <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark' }} text-lg"></span>
                </button>
            </v-dark>
        </div>

        @if (\Platform\Portal\Helpers\PortalAuth::isEnabled())
            @php($portalGuard = \Platform\Portal\Helpers\PortalAuth::guard())

            @if (auth()->guard($portalGuard)->check())
                <div class="px-4 py-3 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] flex items-center justify-between">
                    <span class="text-sm font-semibold text-[var(--text-primary)]">
                        {{ auth()->guard($portalGuard)->user()->name }}
                    </span>

                    <a
                        href="{{ route('portal.auth.logout') }}"
                        class="text-xs font-semibold text-red-600 dark:text-red-400"
                    >
                        @lang('portal::app.auth.logout.title')
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 gap-2">
                    <a
                        href="{{ route('portal.auth.login') }}"
                        class="flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-[var(--text-primary)] bg-[var(--bg-surface-elevated)] hover:bg-[var(--bg-surface-muted)] border border-[var(--border-subtle)] transition-colors"
                    >
                        @lang('portal::app.auth.login.title')
                    </a>

                    <a
                        href="{{ route('portal.auth.register') }}"
                        class="flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[var(--brand-primary)] hover:bg-[var(--brand-primary-hover)] transition-colors shadow-sm"
                    >
                        @lang('portal::app.auth.register.title')
                    </a>
                </div>
            @endif
        @endif

        <nav class="grid gap-1 border-t border-[var(--border-subtle)] pt-3">
            <x-portal::menu-slot
                id="header_mobile"
                location="header"
                direction="column"
            />

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
        </nav>
    </x-slot>
</x-portal::drawer>

{!! view_render_event('bagisto.shop.layout.header.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dark-template"
    >
        <button
            type="button"
            class="flex items-center justify-center w-9 h-9 rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:border-[var(--brand-primary)]/40 hover:bg-[var(--bg-surface-elevated)] transition-all focus:outline-none"
            :title="isDarkMode ? '@lang('portal::app.home.theme.light-mode')' : '@lang('portal::app.home.theme.dark-mode')'"
            @click="toggle"
            aria-label="Toggle theme"
        >
            <span
                class="text-lg transition-transform"
                :class="[isDarkMode ? 'icon-light text-amber-400' : 'icon-dark text-[var(--text-secondary)]']"
            ></span>
        </button>
    </script>

    <script type="module">
        app.component('v-dark', {
            template: '#v-dark-template',

            data() {
                return {
                    isDarkMode: {{ request()->cookie('portal_dark_mode') ?? 0 }},

                    logo: "{{ core()->getCurrentChannel()->logo_url ?? bagisto_asset('images/logo.svg') }}",

                    dark_logo: "{{ bagisto_asset('images/dark-logo.svg') }}",
                };
            },

            mounted() {
                if (this.$emitter) {
                    this.$emitter.on('change-theme', (theme) => {
                        this.isDarkMode = theme === 'dark' ? 1 : 0;
                    });
                }
            },

            methods: {
                toggle() {
                    this.isDarkMode = parseInt(this.isDarkModeCookie()) ? 0 : 1;

                    var expiryDate = new Date();

                    expiryDate.setMonth(expiryDate.getMonth() + 1);

                    document.cookie = 'portal_dark_mode=' + this.isDarkMode + '; path=/; expires=' + expiryDate.toGMTString();

                    document.documentElement.classList.toggle('dark', this.isDarkMode === 1);

                    if (this.$emitter) {
                        if (this.isDarkMode) {
                            this.$emitter.emit('change-theme', 'dark');
                        } else {
                            this.$emitter.emit('change-theme', 'light');
                        }
                    }

                    const logoImg = document.getElementById('logo-image');
                    if (logoImg && this.dark_logo) {
                        logoImg.src = this.isDarkMode ? this.dark_logo : this.logo;
                    }
                },

                isDarkModeCookie() {
                    const cookies = document.cookie.split(';');

                    for (const cookie of cookies) {
                        const [name, value] = cookie.trim().split('=');

                        if (name === 'portal_dark_mode') {
                            return value;
                        }
                    }

                    return 0;
                },
            },
        });
    </script>
@endPushOnce
