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

    $assignedMenu = portal_menu_slot('header_main');
    $hasAssignedMenuItems = $assignedMenu && $assignedMenu->rootItems && $assignedMenu->rootItems->isNotEmpty();
    $channelName = core()->getCurrentChannel()->name ?? config('app.name');
@endphp

{!! view_render_event('bagisto.portal.layout.header.before') !!}

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

<header class="{{ $isSticky ? 'sticky top-0' : 'relative' }} z-50 bg-[var(--bg-app)]/85 backdrop-blur-md border-b border-[var(--border-subtle)] shadow-[0_1px_12px_rgba(0,0,0,0.03)] dark:shadow-[0_4px_20px_rgba(0,0,0,0.3)] transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <!-- Left: Hamburger (Mobile) + Brand Monogram Logo -->
        <div class="flex items-center gap-3 sm:gap-4 shrink-0 min-w-0">
            <!-- Mobile Menu Toggle Button -->
            <button
                type="button"
                class="md:hidden flex items-center justify-center w-10 h-10 rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] transition-colors focus:outline-none"
                @click="$refs.mobileMenuDrawer.open()"
                aria-label="Open navigation menu"
            >
                <span class="icon-menu text-xl"></span>
            </button>

            <!-- Brand Logo & Roa Geometric Monogram -->
            <a href="{{ portal_home_url() }}" class="flex items-center gap-3 group focus:outline-none select-none shrink-0" aria-label="{{ $channelName }}">
                @if ($logo = core()->getCurrentChannel()->logo_url)
                    <img id="logo-image" src="{{ $logo }}" class="h-8 w-auto transition-transform group-hover:scale-105" alt="{{ $channelName }}" />
                @else
                    <div class="w-9 h-9 rounded-xl bg-[var(--bg-surface)] border border-[var(--border-subtle)] flex items-center justify-center p-1.5 shadow-2xs group-hover:border-[var(--brand-primary)] transition-colors duration-200 shrink-0">
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

        <!-- Center: Signature Floating Capsule Navigation Bar -->
        <nav class="hidden md:flex items-center gap-1 px-3 py-1.5 rounded-full bg-[var(--bg-surface)]/80 backdrop-blur-sm border border-[var(--border-subtle)] shadow-xs" aria-label="Main Navigation">
            @if ($hasAssignedMenuItems)
                <x-portal::menu
                    :menu="$assignedMenu"
                    class="flex items-center gap-1"
                    item-class="!px-3.5 !py-1.5 !text-sm font-medium !text-[var(--text-secondary)] hover:!text-[var(--text-primary)] transition-colors !rounded-full hover:!bg-[var(--bg-surface-elevated)]"
                    active-class="!bg-[var(--brand-primary-light)] !text-[var(--brand-primary)] !font-semibold"
                />
            @else
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
            @endif
        </nav>

        <!-- Right Side: Utility Controls (Theme + Locales + CTAs) -->
        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
            <!-- Locales Dropdown Pill -->
            @if ($showLocales && core()->getCurrentChannel()->locales->count() > 1)
                <x-portal::dropdown position="bottom-right">
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="flex items-center gap-1.5 h-9 px-3 text-xs font-semibold text-[var(--text-primary)] bg-[var(--bg-surface)]/80 hover:bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] rounded-full transition-all shadow-2xs focus:outline-none"
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

            <!-- Dark Mode Switcher Button -->
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
                class="btn-primary !px-4 !py-2 !text-xs !rounded-full hidden lg:inline-flex items-center gap-1.5 shadow-sm hover:shadow-[var(--shadow-glow)] transition-all"
            >
                <span>@lang('portal::app.sections.hero_scaffold.default_primary_cta')</span>
                <span class="icon-arrow-right rtl:rotate-180 text-xs"></span>
            </a>

            <!-- Optional Storefront Authentication -->
            @if ($showAuth && \Platform\Portal\Helpers\PortalAuth::isEnabled())
                @php($portalGuard = \Platform\Portal\Helpers\PortalAuth::guard())

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

                        <x-slot:content class="!p-1.5 min-w-[140px] shadow-[var(--shadow-card)] rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)]">
                            <form method="POST" action="{{ route('portal.auth.logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full flex items-center px-3.5 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors cursor-pointer text-start"
                                >
                                    @lang('portal::app.auth.logout.title')
                                </button>
                            </form>
                        </x-slot>
                    </x-portal::dropdown>
                @else
                    <div class="flex items-center gap-1 ltr:ml-1 rtl:mr-1">
                        <a
                            href="{{ route('portal.auth.login') }}"
                            class="px-3 py-1.5 text-xs font-semibold text-[var(--text-secondary)] hover:text-[var(--brand-primary)] rounded-full hover:bg-[var(--bg-surface-elevated)] transition-colors"
                        >
                            @lang('portal::app.auth.login.title')
                        </a>

                        <a
                            href="{{ route('portal.auth.register') }}"
                            class="btn-primary !px-3.5 !py-1.5 !text-xs !rounded-full font-semibold shadow-xs"
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

        @if (\Platform\Portal\Helpers\PortalAuth::isEnabled())
            @php($portalGuard = \Platform\Portal\Helpers\PortalAuth::guard())

            @if (auth()->guard($portalGuard)->check())
                <div class="px-4 py-3 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] flex items-center justify-between">
                    <span class="text-sm font-semibold text-[var(--text-primary)]">
                        {{ auth()->guard($portalGuard)->user()->name ?? auth()->guard($portalGuard)->user()->first_name ?? 'User' }}
                    </span>

                    <form method="POST" action="{{ route('portal.auth.logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="text-xs font-semibold text-red-600 dark:text-red-400 cursor-pointer"
                        >
                            @lang('portal::app.auth.logout.title')
                        </button>
                    </form>
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
                        class="btn-primary flex items-center justify-center px-4 py-2.5 !rounded-xl text-sm font-semibold shadow-sm"
                    >
                        @lang('portal::app.auth.register.title')
                    </a>
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
                    class="btn-primary w-full flex items-center justify-center gap-2 !py-2.5 !text-sm !rounded-xl"
                >
                    <span>@lang('portal::app.sections.hero_scaffold.default_primary_cta')</span>
                    <span class="icon-arrow-right rtl:rotate-180 text-xs"></span>
                </a>
            </div>
        </nav>
    </x-slot>
</x-portal::drawer>

{!! view_render_event('bagisto.portal.layout.header.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dark-template"
    >
        <button
            type="button"
            class="flex items-center justify-center w-9 h-9 rounded-full border border-[var(--border-subtle)] bg-[var(--bg-surface)]/80 text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:border-[var(--brand-primary)]/40 hover:bg-[var(--bg-surface-elevated)] transition-all focus:outline-none shadow-2xs"
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

                    logo: "{{ core()->getCurrentChannel()->logo_url ?? portal_asset('images/logo.svg', '') }}",

                    dark_logo: "{{ portal_asset('images/dark-logo.svg', core()->getCurrentChannel()->logo_url ?? '') }}",
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
