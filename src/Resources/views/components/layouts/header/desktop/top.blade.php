@php
    $headerSettings = portal_layout_settings('header');
    $isShopMode = function_exists('portal_shop_enabled') && portal_shop_enabled();

    $announcementEnabled = (bool) data_get($headerSettings, 'announcement.status', true);
    $announcementText = $announcementEnabled ? data_get($headerSettings, 'announcement.text') : null;
    $announcementLink = data_get($headerSettings, 'announcement.link');

    if (! $announcementText && $isShopMode && $announcementEnabled) {
        $announcementText = core()->getConfigData('general.content.header_offer.title');
        $announcementLink = core()->getConfigData('general.content.header_offer.redirection_link');
    }

    $showLocales = (bool) data_get($headerSettings, 'controls.show_locales', true);
    $channel = core()->getCurrentChannel();

    $rawCurrencies = $channel->currencies ?? collect();
    $currencies = is_iterable($rawCurrencies) ? collect($rawCurrencies) : collect();
    $hasCurrencies = $currencies->count() > 1;

    $rawLocales = $channel->locales ?? collect();
    $locales = (is_iterable($rawLocales) ? collect($rawLocales) : collect())->sortBy('name');
    $hasLocales = $showLocales && $locales->count() > 1;
    $hasContent = $announcementText || $hasCurrencies || $hasLocales;

    $currentLocaleName = $locales->firstWhere('code', app()->getLocale())?->name ?? core()->getCurrentLocale()->name;
@endphp

@if ($hasContent)
    {!! view_render_event('bagisto.portal.layout.header.desktop.top.before') !!}

    <div class="flex w-full items-center justify-between border border-b border-l-0 border-r-0 border-t-0 border-[var(--border-subtle,#e5e7eb)] dark:border-slate-800 px-16 max-1180:px-8 text-sm text-[var(--text-secondary,#475569)] dark:text-slate-400 bg-white dark:bg-[#0b0f19]">
        {!! view_render_event('bagisto.portal.layout.header.desktop.top.currency_switcher.before') !!}

        <!-- Currency Switcher -->
        @if ($hasCurrencies)
            <x-portal::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'left' : 'right' }}">
                <!-- Dropdown Toggler -->
                <x-slot:toggle>
                    <div
                        class="flex cursor-pointer items-center gap-2.5 py-3 hover:text-black dark:hover:text-white transition-colors"
                        role="button"
                        tabindex="0"
                    >
                        <span>
                            {{ core()->getCurrentCurrency()->symbol . ' ' . core()->getCurrentCurrencyCode() }}
                        </span>

                        <span
                            class="icon-arrow-down text-2xl"
                            role="presentation"
                        ></span>
                    </div>
                </x-slot>

                <!-- Dropdown Content -->
                <x-slot:content class="journal-scroll max-h-[500px] !p-0">
                    <div class="my-2.5 grid gap-1 overflow-auto max-md:my-0 sm:max-h-[500px]">
                        @foreach ($currencies as $currency)
                            <a
                                href="{{ route('portal.currency.switch', $currency->code) }}"
                                class="cursor-pointer px-5 py-2 text-base hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors {{ core()->getCurrentCurrencyCode() == $currency->code ? 'bg-gray-100 dark:bg-slate-800 font-semibold text-black dark:text-white' : 'text-gray-700 dark:text-slate-300' }}"
                            >
                                {{ $currency->symbol . ' ' . $currency->code }}
                            </a>
                        @endforeach
                    </div>
                </x-slot>
            </x-portal::dropdown>
        @else
            <div class="w-20"></div>
        @endif

        {!! view_render_event('bagisto.portal.layout.header.desktop.top.currency_switcher.after') !!}

        <!-- Center: Offers / Announcement -->
        @if ($announcementText)
            <p class="py-3 text-xs font-medium text-center text-gray-700 dark:text-slate-300">
                {{ $announcementText }}

                @if ($announcementLink)
                    <a
                        href="{{ $announcementLink }}"
                        class="underline ltr:ml-1 rtl:mr-1 hover:text-black dark:hover:text-white transition-colors"
                        role="button"
                    >
                        {{ data_get($headerSettings, 'announcement.link_text') ?: (core()->getConfigData('general.content.header_offer.redirection_title') ?: $announcementText) }}
                    </a>
                @endif
            </p>
        @else
            <div></div>
        @endif

        {!! view_render_event('bagisto.portal.layout.header.desktop.top.locale_switcher.before') !!}

        <!-- Locales Switcher -->
        @if ($hasLocales)
            <x-portal::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'right' : 'left' }}">
                <x-slot:toggle>
                    <!-- Dropdown Toggler -->
                    <div
                        class="flex cursor-pointer items-center gap-2.5 py-3 hover:text-black dark:hover:text-white transition-colors"
                        role="button"
                        tabindex="0"
                    >
                        @php
                            $isDefaultLogo = empty(core()->getCurrentLocale()->logo_url);
                        @endphp
                        <img
                            src="{{ ! empty(core()->getCurrentLocale()->logo_url)
                                    ? core()->getCurrentLocale()->logo_url
                                    : portal_asset('images/default-language.svg', bagisto_asset('images/default-language.svg'))
                                }}"
                            class="h-[16px] w-[24px] object-cover rounded-sm shadow-sm transition-all {{ $isDefaultLogo ? 'dark:invert' : '' }}"
                            alt="@lang('portal::app.components.layouts.header.desktop.top.default-locale')"
                            width="24"
                            height="16"
                        />

                        <span>
                            {{ $currentLocaleName }}
                        </span>

                        <span
                            class="icon-arrow-down text-2xl"
                            role="presentation"
                        ></span>
                    </div>
                </x-slot>

                <!-- Dropdown Content -->
                <x-slot:content class="journal-scroll max-h-[500px] !p-0">
                    <div class="my-2.5 grid gap-1 overflow-auto max-md:my-0 sm:max-h-[500px]">
                        @foreach ($locales as $locale)
                            @php
                                $isLocaleDefaultLogo = empty($locale->logo_url);
                            @endphp
                            <a
                                href="{{ route('portal.locale.switch', $locale->code) }}"
                                class="flex cursor-pointer items-center gap-2.5 px-5 py-2 text-base hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors {{ app()->getLocale() == $locale->code ? 'bg-gray-100 dark:bg-slate-800 font-semibold text-black dark:text-white' : 'text-gray-700 dark:text-slate-300' }}"
                            >
                                <img
                                    src="{{ ! empty($locale->logo_url) ? $locale->logo_url : portal_asset('images/default-language.svg', bagisto_asset('images/default-language.svg')) }}"
                                    width="24"
                                    height="16"
                                    class="h-[16px] w-[24px] object-cover rounded-sm shadow-sm transition-all {{ $isLocaleDefaultLogo ? 'dark:invert' : '' }}"
                                    alt="{{ $locale->name }}"
                                />

                                <span>{{ $locale->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </x-slot>
            </x-portal::dropdown>
        @else
            <div class="w-32"></div>
        @endif

        {!! view_render_event('bagisto.portal.layout.header.desktop.top.locale_switcher.after') !!}
    </div>

    {!! view_render_event('bagisto.portal.layout.header.desktop.top.after') !!}
@endif
