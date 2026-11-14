{!! view_render_event('bagisto.portal.layout.header.desktop.top.before') !!}

@php
    $channel = core()->getCurrentChannel();
    $headerSettings = portal_layout_settings('header');
    $offerTitle = core()->getConfigData('general.content.header_offer.title')
        ?: data_get($headerSettings, 'announcement.text');
    $offerLink = core()->getConfigData('general.content.header_offer.redirection_link')
        ?: data_get($headerSettings, 'announcement.link');
    $offerLinkTitle = core()->getConfigData('general.content.header_offer.redirection_title');

    $hasCurrencies = $channel && isset($channel->currencies) && count($channel->currencies) > 1;
    $hasLocales = $channel && isset($channel->locales) && count($channel->locales) > 1;
@endphp

<div class="border-b border-[var(--border-subtle)] bg-[var(--bg-surface-elevated)]/60 text-xs text-[var(--text-secondary)]">
    <div class="portal-container mx-auto flex items-center justify-between py-1.5 px-4 sm:px-6 lg:px-8" style="max-width: var(--container-max-width, 1280px);">
        {!! view_render_event('bagisto.portal.layout.header.desktop.top.currency_switcher.before') !!}

        <!-- Left: Currency Switcher or Channel Info -->
        <div class="flex items-center gap-3">
            @if ($hasCurrencies)
                <x-portal::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'left' : 'right' }}">
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="flex items-center gap-1.5 py-0.5 text-xs font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors focus:outline-none"
                        >
                            <span>{{ core()->getCurrentCurrencyCode() }} ({{ core()->getCurrentCurrency()->symbol }})</span>
                            <span class="icon-arrow-down text-[10px] opacity-70"></span>
                        </button>
                    </x-slot>

                    <x-slot:content class="!p-1.5 min-w-[120px] rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] shadow-lg">
                        @foreach ($channel->currencies as $currency)
                            <a
                                href="{{ route('portal.currency.switch', $currency->code) }}"
                                class="flex items-center px-3 py-1.5 text-xs rounded-lg transition-colors {{ core()->getCurrentCurrencyCode() == $currency->code ? 'font-bold text-[var(--brand-primary)] bg-[var(--brand-primary-light)]' : 'text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)]' }}"
                            >
                                {{ $currency->code }} ({{ $currency->symbol }})
                            </a>
                        @endforeach
                    </x-slot:content>
                </x-portal::dropdown>
            @else
                <span class="text-[11px] font-mono text-[var(--text-muted)]">
                    {{ $channel?->name }}
                </span>
            @endif
        </div>

        {!! view_render_event('bagisto.portal.layout.header.desktop.top.currency_switcher.after') !!}

        <!-- Center: Announcement / Offer Banner -->
        @if ($offerTitle)
            <div class="text-center truncate px-4">
                <span class="font-medium text-[var(--text-primary)] text-xs">
                    {{ $offerTitle }}
                </span>
                @if ($offerLink)
                    <a href="{{ $offerLink }}" class="underline ltr:ml-1.5 rtl:mr-1.5 text-xs font-semibold text-[var(--brand-primary)] hover:underline">
                        {{ $offerLinkTitle ?: $offerTitle }}
                    </a>
                @endif
            </div>
        @else
            <div></div>
        @endif

        {!! view_render_event('bagisto.portal.layout.header.desktop.top.locale_switcher.before') !!}

        <!-- Right: Language Switcher Dropdown (Properly Stacked Above Header) -->
        <div class="flex items-center gap-3">
            @if ($hasLocales)
                <x-portal::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'right' : 'left' }}">
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="flex items-center gap-1.5 py-0.5 text-xs font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors focus:outline-none"
                        >
                            @if (! empty(core()->getCurrentLocale()->logo_url))
                                <img src="{{ core()->getCurrentLocale()->logo_url }}" class="h-3.5 w-3.5 rounded-full" alt="{{ core()->getCurrentLocale()->name }}">
                            @else
                                <span class="icon-language text-sm"></span>
                            @endif
                            <span>{{ core()->getCurrentLocale()->name }}</span>
                            <span class="icon-arrow-down text-[10px] opacity-70"></span>
                        </button>
                    </x-slot>

                    <x-slot:content class="!p-1.5 min-w-[130px] rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] shadow-lg">
                        @foreach ($channel->locales as $locale)
                            <a
                                href="{{ route('portal.locale.switch', $locale->code) }}"
                                class="flex items-center gap-2 px-3 py-1.5 text-xs rounded-lg transition-colors {{ app()->getLocale() == $locale->code ? 'font-bold text-[var(--brand-primary)] bg-[var(--brand-primary-light)]' : 'text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)]' }}"
                            >
                                @if (! empty($locale->logo_url))
                                    <img src="{{ $locale->logo_url }}" class="h-3.5 w-3.5 rounded-full" alt="{{ $locale->name }}">
                                @endif
                                <span>{{ $locale->name }}</span>
                            </a>
                        @endforeach
                    </x-slot:content>
                </x-portal::dropdown>
            @endif
        </div>

        {!! view_render_event('bagisto.portal.layout.header.desktop.top.after') !!}
    </div>
</div>
