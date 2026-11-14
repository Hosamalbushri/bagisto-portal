{!! view_render_event('bagisto.portal.layout.header.desktop.bottom.before') !!}

@php
    $channel = core()->getCurrentChannel();
    $channelName = $channel?->name ?? config('app.name');
    $assignedMenu = portal_menu_slot('header_main');
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
@endphp

<div
    class="portal-header-bottom relative z-20 backdrop-blur-md transition-all duration-300"
    style="background-color: var(--header-bg, var(--bg-surface)); color: var(--header-text, var(--text-primary)); min-height: var(--header-height, 4rem);"
>
    <div class="portal-container mx-auto flex items-center justify-between gap-4 px-4 sm:px-6 lg:px-8" style="max-width: var(--container-max-width, 1280px); min-height: var(--header-height, 4rem);">
        <!-- Left: Brand Logo & Monogram -->
        <div class="flex items-center gap-3 sm:gap-4 shrink-0 min-w-0">
            {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.logo.before') !!}

            <a href="{{ portal_home_url() }}" class="flex items-center gap-3 group focus:outline-none select-none shrink-0" aria-label="{{ $channelName }}">
                @if ($logo = $channel?->logo_url)
                    <img id="logo-image" src="{{ $logo }}" class="h-8 w-auto max-h-[36px] transition-transform group-hover:scale-105" alt="{{ $channelName }}" />
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

            {!! view_render_event('bagisto.portal.layout.header.desktop.bottom.logo.after') !!}
        </div>

        <!-- Center: General Portal Navigation -->
        <nav class="hidden md:flex items-center gap-1" aria-label="Main Navigation">
            @if ($hasAssignedMenuItems)
                @foreach ($assignedMenu->rootItems as $item)
                    @if (filled($item->getTitle()))
                        @php
                            $children = $item->children ? $item->children->where('status', 1)->filter(fn ($c) => filled($c->getTitle())) : collect();
                            $hasChildren = $children->isNotEmpty();
                            $active = $item->isActive();
                        @endphp

                        <div class="relative group">
                            <a
                                href="{{ $item->getUrl() }}"
                                target="{{ $item->target ?: '_self' }}"
                                @if ($item->target === '_blank') rel="noopener noreferrer" @endif
                                class="flex items-center gap-1 px-3.5 py-2 text-sm font-medium rounded-full transition-colors {{ $active ? 'bg-[var(--brand-primary-light)] text-[var(--brand-primary)] font-semibold' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)]' }}"
                            >
                                @if (! empty($item->icon))
                                    <i class="{{ $item->icon }} text-xs"></i>
                                @endif
                                <span>{{ $item->getTitle() }}</span>
                                @if ($hasChildren)
                                    <span class="icon-arrow-down text-[10px] opacity-70 transition-transform group-hover:rotate-180"></span>
                                @endif
                            </a>

                            @if ($hasChildren)
                                <div class="pointer-events-none absolute top-full start-0 mt-1 z-50 min-w-[200px] rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] p-1.5 opacity-0 shadow-xl transition-all duration-200 group-hover:pointer-events-auto group-hover:opacity-100">
                                    <div class="grid gap-0.5">
                                        @foreach ($children as $child)
                                            <a
                                                href="{{ $child->getUrl() }}"
                                                target="{{ $child->target ?: '_self' }}"
                                                class="block px-3.5 py-2 text-xs font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] rounded-lg transition-colors"
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
                <a
                    href="{{ portal_home_url() }}"
                    class="px-3.5 py-2 text-sm font-medium rounded-full transition-colors {{ request()->routeIs('portal.home') ? 'bg-[var(--brand-primary-light)] text-[var(--brand-primary)] font-semibold' : 'text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)]' }}"
                >
                    @lang('portal::app.home.nav.home')
                </a>
                <a
                    href="#services"
                    class="px-3.5 py-2 text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors rounded-full hover:bg-[var(--bg-surface-elevated)]"
                >
                    @lang('portal::app.home.nav.services')
                </a>
                <a
                    href="#contact"
                    class="px-3.5 py-2 text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors rounded-full hover:bg-[var(--bg-surface-elevated)]"
                >
                    @lang('portal::app.home.nav.contact-us')
                </a>
            @endif
        </nav>

        <!-- Right Side: Utility Controls (Search + Theme + Auth) -->
        <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
            <!-- Search Bar (Clean general portal search input) -->
            @if ($searchRoute)
                <div class="hidden lg:block relative w-44 xl:w-56">
                    <form action="{{ $searchRoute }}" method="GET" class="relative flex items-center">
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

            <!-- Dark Mode Switcher -->
            <v-dark>
                <button
                    type="button"
                    class="flex items-center justify-center w-9 h-9 rounded-full border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:border-[var(--brand-primary)]/40 hover:bg-[var(--bg-surface-elevated)] transition-all focus:outline-none shadow-2xs cursor-pointer"
                    title="{{ request()->cookie('portal_dark_mode') ? trans('portal::app.home.theme.light-mode') : trans('portal::app.home.theme.dark-mode') }}"
                    aria-label="Toggle theme"
                >
                    <span class="{{ request()->cookie('portal_dark_mode') ? 'icon-light text-amber-400' : 'icon-dark text-[var(--text-secondary)]' }} text-base"></span>
                </button>
            </v-dark>

            <!-- Optional Mini-Cart (Only when E-commerce Shop is enabled) -->
            @if (function_exists('portal_shop_enabled') && portal_shop_enabled())
                @if (view()->exists('shop::checkout.cart.mini-cart'))
                    <div class="flex items-center">
                        @include('shop::checkout.cart.mini-cart')
                    </div>
                @elseif (Route::has('shop.checkout.cart.index'))
                    <a
                        href="{{ route('shop.checkout.cart.index') }}"
                        class="flex items-center justify-center w-9 h-9 rounded-full border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors"
                        aria-label="Shopping Cart"
                    >
                        <span class="icon-cart text-lg"></span>
                    </a>
                @endif
            @endif

            <!-- User Authentication / Membership Dropdown -->
            @if ($currentUser)
                <x-portal::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'right' : 'left' }}">
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="flex items-center gap-1.5 h-9 px-3 text-xs font-semibold text-[var(--text-primary)] bg-[var(--bg-surface)] hover:bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] rounded-full transition-all shadow-2xs focus:outline-none cursor-pointer"
                        >
                            <span class="icon-avatar text-base text-[var(--brand-primary)]"></span>
                            <span class="truncate max-w-[110px]">{{ $currentUser->name ?? $currentUser->first_name ?? 'User' }}</span>
                            <span class="icon-arrow-down text-[10px] opacity-70"></span>
                        </button>
                    </x-slot>

                    <x-slot:content class="!p-1.5 min-w-[180px] rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] shadow-xl">
                        <div class="px-3 py-2 border-b border-[var(--border-subtle)] mb-1">
                            <p class="text-xs font-bold text-[var(--text-primary)] truncate">
                                {{ $currentUser->name ?? $currentUser->first_name ?? 'User' }}
                            </p>
                            <p class="text-[11px] text-[var(--text-muted)] truncate">
                                {{ $currentUser->email }}
                            </p>
                        </div>

                        @if (Route::has('customer.account.profile.index'))
                            <a
                                href="{{ route('customer.account.profile.index') }}"
                                class="flex items-center px-3 py-1.5 text-xs font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] rounded-lg transition-colors"
                            >
                                @lang('shop::app.components.layouts.header.desktop.top.profile')
                            </a>
                        @endif

                        @if ($isCustomerAuth && Route::has('customer.session.destroy'))
                            <form method="POST" action="{{ route('customer.session.destroy') }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="w-full flex items-center px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-lg transition-colors cursor-pointer text-start"
                                >
                                    @lang('shop::app.components.layouts.header.desktop.top.logout')
                                </button>
                            </form>
                        @elseif ($isPortalAuth && Route::has('portal.auth.logout'))
                            <form method="POST" action="{{ route('portal.auth.logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full flex items-center px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-lg transition-colors cursor-pointer text-start"
                                >
                                    @lang('portal::app.auth.logout.title')
                                </button>
                            </form>
                        @endif
                    </x-slot:content>
                </x-portal::dropdown>
            @else
                <div class="flex items-center gap-1.5">
                    @if ($loginUrl)
                        <a
                            href="{{ $loginUrl }}"
                            class="px-3.5 py-1.5 text-xs font-semibold text-[var(--text-secondary)] hover:text-[var(--brand-primary)] rounded-full hover:bg-[var(--bg-surface-elevated)] transition-colors"
                        >
                            @lang('portal::app.auth.login.title')
                        </a>
                    @endif

                    @if ($registerUrl)
                        <a
                            href="{{ $registerUrl }}"
                            class="btn-primary !px-4 !py-1.5 !text-xs font-semibold shadow-xs"
                            style="border-radius: var(--btn-radius, 9999px);"
                        >
                            @lang('portal::app.auth.register.title')
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

{!! view_render_event('bagisto.portal.layout.header.desktop.bottom.after') !!}
