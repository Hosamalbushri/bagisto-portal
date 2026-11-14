{!! view_render_event('bagisto.portal.layout.header.desktop.top.before') !!}

<div class="relative z-[60] flex w-full items-center justify-between border-b border-[var(--border-subtle)] px-[60px] max-1180:px-8 text-xs bg-white dark:bg-zinc-900 text-[var(--text-secondary)]">
    {!! view_render_event('bagisto.portal.layout.header.desktop.top.currency_switcher.before') !!}

    <!-- Left: Currency Switcher -->
    @if (core()->getCurrentChannel()->currencies->count() > 1)
        <x-portal::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'left' : 'right' }}">
            <x-slot:toggle>
                <div class="flex cursor-pointer items-center gap-2 py-3 hover:text-[var(--text-primary)] transition-colors" role="button" tabindex="0">
                    <span>
                        {{ core()->getCurrentCurrency()->symbol . ' ' . core()->getCurrentCurrencyCode() }}
                    </span>
                    <span class="icon-arrow-down text-base opacity-70"></span>
                </div>
            </x-slot>

            <x-slot:content class="journal-scroll max-h-[500px] !p-1 min-w-[120px] rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface)] shadow-xl z-[70]">
                @foreach (core()->getCurrentChannel()->currencies as $currency)
                    <a
                        href="{{ route('portal.currency.switch', $currency->code) }}"
                        class="flex items-center px-3 py-1.5 text-xs rounded-md transition-colors {{ core()->getCurrentCurrencyCode() == $currency->code ? 'font-bold text-[var(--brand-primary)] bg-[var(--brand-primary-light)]' : 'text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)]' }}"
                    >
                        {{ $currency->code }} ({{ $currency->symbol }})
                    </a>
                @endforeach
            </x-slot>
        </x-portal::dropdown>
    @else
        <div class="text-[11px] font-mono text-[var(--text-muted)] py-3">
            {{ core()->getCurrentChannel()->name }}
        </div>
    @endif

    {!! view_render_event('bagisto.portal.layout.header.desktop.top.currency_switcher.after') !!}

    <!-- Center: Announcement / Brand Motto -->
    @php
        $headerSettings = portal_layout_settings('header');
        $isShopMode = function_exists('portal_shop_enabled') && portal_shop_enabled();

        $announcementText = data_get($headerSettings, 'announcement.text');
        $announcementLink = data_get($headerSettings, 'announcement.link');

        if (! $announcementText && $isShopMode) {
            $announcementText = core()->getConfigData('general.content.header_offer.title');
            $announcementLink = core()->getConfigData('general.content.header_offer.redirection_link');
        }
    @endphp

    @if ($announcementText)
        <p class="py-3 text-xs font-medium text-[var(--text-primary)]">
            {{ $announcementText }}

            @if ($announcementLink)
                <a href="{{ $announcementLink }}" class="underline ltr:ml-1 rtl:mr-1 hover:text-[var(--brand-primary)] transition-colors">
                    {{ data_get($headerSettings, 'announcement.link_text') ?: $announcementText }}
                </a>
            @endif
        </p>
    @else
        <div></div>
    @endif

    {!! view_render_event('bagisto.portal.layout.header.desktop.top.locale_switcher.before') !!}

    <!-- Right: Locale Switcher -->
    @if (core()->getCurrentChannel()->locales->count() > 1)
        <x-portal::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'right' : 'left' }}">
            <x-slot:toggle>
                <div class="flex cursor-pointer items-center gap-2 py-3 hover:text-[var(--text-primary)] transition-colors" role="button" tabindex="0">
                    @if (! empty(core()->getCurrentLocale()->logo_url))
                        <img src="{{ core()->getCurrentLocale()->logo_url }}" class="h-4 w-4 rounded-full" alt="{{ core()->getCurrentLocale()->name }}">
                    @endif
                    <span>{{ core()->getCurrentLocale()->name }}</span>
                    <span class="icon-arrow-down text-base opacity-70"></span>
                </div>
            </x-slot>

            <x-slot:content class="journal-scroll max-h-[500px] !p-1 min-w-[130px] rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface)] shadow-xl z-[70]">
                @foreach (core()->getCurrentChannel()->locales as $locale)
                    <a
                        href="{{ route('portal.locale.switch', $locale->code) }}"
                        class="flex items-center gap-2 px-3 py-1.5 text-xs rounded-md transition-colors {{ app()->getLocale() == $locale->code ? 'font-bold text-[var(--brand-primary)] bg-[var(--brand-primary-light)]' : 'text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)]' }}"
                    >
                        @if (! empty($locale->logo_url))
                            <img src="{{ $locale->logo_url }}" class="h-3.5 w-3.5 rounded-full" alt="{{ $locale->name }}">
                        @endif
                        {{ $locale->name }}
                    </a>
                @endforeach
            </x-slot>
        </x-portal::dropdown>
    @else
        <div></div>
    @endif

    {!! view_render_event('bagisto.portal.layout.header.desktop.top.after') !!}
</div>
