{!! view_render_event('bagisto.shop.layout.header.before') !!}

<header class="sticky top-0 z-50 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <!-- Left: Hamburger + Logo -->
        <div class="flex items-center gap-4">
            <!-- Mobile Menu Toggle Button -->
            <button
                type="button"
                class="md:hidden p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                @click="$refs.mobileMenuDrawer.open()"
                aria-label="Open navigation menu"
            >
                <span class="icon-menu text-2xl"></span>
            </button>

            <!-- Brand Logo -->
            <a href="{{ route('shop.home.index') }}" class="flex items-center gap-2">
                @if ($logo = core()->getCurrentChannel()->logo_url)
                    <img src="{{ $logo }}" class="h-8 w-auto" alt="{{ config('app.name') }}" />
                @else
                    <span class="text-xl font-bold text-blue-600 dark:text-blue-400">
                        {{ core()->getCurrentChannel()->name ?? config('app.name') }}
                    </span>
                @endif
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium ltr:ml-6 rtl:mr-6">
                <a href="{{ route('shop.home.index') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">
                    @lang('portal::app.home.nav.home')
                </a>

                <a href="{{ route('shop.home.contact_us') }}" class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors">
                    @lang('portal::app.home.nav.contact-us')
                </a>
            </nav>
        </div>

        <!-- Right: Switchers + Admin Button -->
        <div class="flex items-center gap-3">
            <!-- Locales Dropdown -->
            @if (core()->getCurrentChannel()->locales()->count() > 1)
                <x-portal::dropdown position="bottom-right">
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                        >
                            <span>{{ core()->getCurrentLocale()->name }}</span>
                            <span class="icon-arrow-down text-base"></span>
                        </button>
                    </x-slot>

                    <x-slot:content class="!p-1">
                        @foreach (core()->getCurrentChannel()->locales as $locale)
                            <a
                                href="{{ route('portal.locale.switch', $locale->code) }}"
                                class="flex items-center px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md {{ app()->getLocale() == $locale->code ? 'font-bold text-blue-600 dark:text-blue-400' : '' }}"
                            >
                                {{ $locale->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-portal::dropdown>
            @endif

            <!-- Optional Storefront Authentication (Plug-and-Play) -->
            @if (\Platform\Portal\Helpers\PortalAuth::isEnabled())
                @php($portalGuard = \Platform\Portal\Helpers\PortalAuth::guard())

                @if (auth()->guard($portalGuard)->check())
                    <x-portal::dropdown position="bottom-right">
                        <x-slot:toggle>
                            <button
                                type="button"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                            >
                                <span class="icon-avatar text-base"></span>
                                <span>{{ auth()->guard($portalGuard)->user()->name }}</span>
                                <span class="icon-arrow-down text-xs"></span>
                            </button>
                        </x-slot>

                        <x-slot:content class="!p-1">
                            <a
                                href="{{ route('portal.auth.logout') }}"
                                class="flex items-center px-4 py-2 text-xs text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md"
                            >
                                @lang('portal::app.auth.logout.title')
                            </a>
                        </x-slot>
                    </x-portal::dropdown>
                @else
                    <a
                        href="{{ route('portal.auth.login') }}"
                        class="text-xs font-semibold text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 transition-colors"
                    >
                        @lang('portal::app.auth.login.title')
                    </a>

                    <a
                        href="{{ route('portal.auth.register') }}"
                        class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 transition-colors"
                    >
                        @lang('portal::app.auth.register.title')
                    </a>
                @endif
            @endif

            <!-- Admin Panel Link -->
            <a
                href="{{ route('admin.dashboard.index') }}"
                class="inline-flex items-center px-3.5 py-1.5 border border-transparent text-xs font-semibold rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 transition-all"
            >
                @lang('portal::app.home.nav.dashboard')
            </a>
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

    <x-slot:content class="p-4">
        <nav class="grid gap-2">
            @if (\Platform\Portal\Helpers\PortalAuth::isEnabled())
                @php($portalGuard = \Platform\Portal\Helpers\PortalAuth::guard())

                @if (auth()->guard($portalGuard)->check())
                    <div class="px-4 py-3 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-between">
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
                            class="flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                        >
                            @lang('portal::app.auth.login.title')
                        </a>

                        <a
                            href="{{ route('portal.auth.register') }}"
                            class="flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors"
                        >
                            @lang('portal::app.auth.register.title')
                        </a>
                    </div>
                @endif
            @endif

            <a
                href="{{ route('shop.home.index') }}"
                class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                @lang('portal::app.home.nav.home')
            </a>

            <a
                href="{{ route('shop.home.contact_us') }}"
                class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
            >
                @lang('portal::app.home.nav.contact-us')
            </a>

            <a
                href="{{ route('admin.dashboard.index') }}"
                class="flex items-center px-4 py-3 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 mt-4"
            >
                @lang('portal::app.home.nav.dashboard')
            </a>

            @if (core()->getCurrentChannel()->locales()->count() > 1)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <span class="text-xs font-semibold uppercase text-gray-400 px-4">
                        @lang('portal::app.home.nav.languages')
                    </span>
                    <div class="mt-2 grid gap-1">
                        @foreach (core()->getCurrentChannel()->locales as $locale)
                            <a
                                href="{{ route('portal.locale.switch', $locale->code) }}"
                                class="flex items-center px-4 py-2 rounded-lg text-sm {{ app()->getLocale() == $locale->code ? 'font-bold text-blue-600 bg-blue-50 dark:bg-blue-950/30 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
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
