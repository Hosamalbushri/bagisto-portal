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

<header class="{{ $isSticky ? 'sticky top-0' : 'relative' }} z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200/80 dark:border-gray-800 transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <!-- Left: Hamburger (Mobile) + Brand Logo + Desktop Nav -->
        <div class="flex items-center gap-3 sm:gap-6 min-w-0">
            <!-- Mobile Menu Toggle Button -->
            <button
                type="button"
                class="md:hidden p-2 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none"
                @click="$refs.mobileMenuDrawer.open()"
                aria-label="Open navigation menu"
            >
                <span class="icon-menu text-2xl"></span>
            </button>

            <!-- Brand Logo -->
            <a href="{{ portal_home_url() }}" class="flex items-center gap-2 shrink-0 transition-transform hover:scale-[1.02]">
                @if ($logo = core()->getCurrentChannel()->logo_url)
                    <img id="logo-image" src="{{ $logo }}" class="h-8 w-auto" alt="{{ config('app.name') }}" />
                @else
                    <span class="text-xl font-bold tracking-tight text-blue-600 dark:text-blue-400">
                        {{ core()->getCurrentChannel()->name ?? config('app.name') }}
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
                    <div class="flex">
                        <span
                            class="{{ request()->cookie('portal_dark_mode') ? 'icon-light' : 'icon-dark' }} cursor-pointer rounded-md p-1.5 text-xl transition-all hover:bg-gray-100 dark:hover:bg-gray-800 sm:text-2xl text-gray-700 dark:text-gray-300"
                            title="{{ request()->cookie('portal_dark_mode') ? trans('portal::app.home.theme.light-mode') : trans('portal::app.home.theme.dark-mode') }}"
                        ></span>
                    </div>
                </v-dark>
            @endif

            <!-- Locales Dropdown -->
            @if ($showLocales && core()->getCurrentChannel()->locales->count() > 1)
                <x-portal::dropdown position="bottom-right">
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="flex items-center gap-1.5 h-9 px-3 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-100/80 hover:bg-gray-200/80 dark:bg-gray-800/80 dark:hover:bg-gray-700/80 border border-gray-200/50 dark:border-gray-700/50 rounded-xl transition-all shadow-sm focus:outline-none"
                        >
                            <span>{{ core()->getCurrentLocale()->name }}</span>
                            <span class="icon-arrow-down text-xs opacity-70"></span>
                        </button>
                    </x-slot>

                    <x-slot:content class="!p-1.5 min-w-[140px] shadow-lg rounded-xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
                        @foreach (core()->getCurrentChannel()->locales as $locale)
                            <a
                                href="{{ route('portal.locale.switch', $locale->code) }}"
                                class="flex items-center px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors {{ app()->getLocale() == $locale->code ? 'font-bold text-blue-600 dark:text-blue-400 bg-blue-50/60 dark:bg-blue-950/40' : '' }}"
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
                                class="flex items-center gap-1.5 h-9 px-3 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-100/80 hover:bg-gray-200/80 dark:bg-gray-800/80 dark:hover:bg-gray-700/80 border border-gray-200/50 dark:border-gray-700/50 rounded-xl transition-all shadow-sm focus:outline-none"
                            >
                                <span class="icon-avatar text-base"></span>
                                <span class="truncate max-w-[100px]">{{ auth()->guard($portalGuard)->user()->name }}</span>
                                <span class="icon-arrow-down text-xs opacity-70"></span>
                            </button>
                        </x-slot>

                        <x-slot:content class="!p-1.5 min-w-[140px] shadow-lg rounded-xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900">
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
                            class="px-3 py-1.5 text-xs font-semibold text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors"
                        >
                            @lang('portal::app.auth.login.title')
                        </a>

                        <a
                            href="{{ route('portal.auth.register') }}"
                            class="px-3.5 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-all shadow-sm hover:shadow"
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
            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                {{ core()->getCurrentChannel()->name ?? config('app.name') }}
            </span>
        </div>
    </x-slot>

    <x-slot:content class="p-4 space-y-4">
        <!-- Dark Mode Switcher Row in Mobile Drawer -->
        <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-gray-100 dark:bg-gray-800">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                @lang('portal::app.home.theme.dark-mode')
            </span>

            <v-dark>
                <div class="flex">
                    <span
                        class="{{ request()->cookie('portal_dark_mode') ? 'icon-light' : 'icon-dark' }} cursor-pointer rounded-md p-1.5 text-xl transition-all hover:bg-gray-200 dark:hover:bg-gray-700 sm:text-2xl text-gray-700 dark:text-gray-300"
                        title="{{ request()->cookie('portal_dark_mode') ? trans('portal::app.home.theme.light-mode') : trans('portal::app.home.theme.dark-mode') }}"
                    ></span>
                </div>
            </v-dark>
        </div>

        @if (\Platform\Portal\Helpers\PortalAuth::isEnabled())
            @php($portalGuard = \Platform\Portal\Helpers\PortalAuth::guard())

            @if (auth()->guard($portalGuard)->check())
                <div class="px-4 py-3 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">
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
                        class="flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                    >
                        @lang('portal::app.auth.login.title')
                    </a>

                    <a
                        href="{{ route('portal.auth.register') }}"
                        class="flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-sm"
                    >
                        @lang('portal::app.auth.register.title')
                    </a>
                </div>
            @endif
        @endif

        <nav class="grid gap-1 border-t border-gray-100 dark:border-gray-800 pt-3">
            <x-portal::menu-slot
                id="header_mobile"
                location="header"
                direction="column"
            />

            @if (core()->getCurrentChannel()->locales->count() > 1)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <span class="text-xs font-semibold uppercase text-gray-400 px-3">
                        @lang('portal::app.home.nav.languages')
                    </span>
                    <div class="mt-2 grid gap-1">
                        @foreach (core()->getCurrentChannel()->locales as $locale)
                            <a
                                href="{{ route('portal.locale.switch', $locale->code) }}"
                                class="flex items-center px-3.5 py-2 rounded-xl text-sm {{ app()->getLocale() == $locale->code ? 'font-bold text-blue-600 bg-blue-50 dark:bg-blue-950/30 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
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
        <div class="flex">
            <span
                class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300"
                :class="[isDarkMode ? 'icon-light' : 'icon-dark']"
                :title="isDarkMode ? '@lang('portal::app.home.theme.light-mode')' : '@lang('portal::app.home.theme.dark-mode')'"
                @click="toggle"
            ></span>
        </div>
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
