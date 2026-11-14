@php
    $headerSettings = portal_layout_settings('header');
    $isAnnouncementActive = (bool) data_get($headerSettings, 'announcement.status', false);
    $announcementText = data_get($headerSettings, 'announcement.text');
    $announcementLink = data_get($headerSettings, 'announcement.link');
    $announcementBg = data_get($headerSettings, 'announcement.bg_color', '#2563eb');
    $announcementTextCol = data_get($headerSettings, 'announcement.text_color', '#ffffff');

    $showDarkMode = (bool) data_get($headerSettings, 'controls.show_dark_mode', true);
    $showLocales = (bool) data_get($headerSettings, 'controls.show_locales', true);
    $showCurrencies = core()->getCurrentChannel()->currencies->count() > 1;
@endphp

{!! view_render_event('bagisto.portal.layout.header.desktop.top.before') !!}

<!-- Top Announcement Bar (if enabled) -->
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

<!-- Top Utility Bar (Locales, Currencies & Mode) -->
@if ($showLocales && core()->getCurrentChannel()->locales->count() > 1 || $showCurrencies)
    <div class="border-b border-[var(--border-subtle)] bg-[var(--bg-surface-elevated)]/50 py-1.5 px-4 text-xs">
        <div class="portal-container mx-auto flex items-center justify-between" style="max-width: var(--container-max-width, 1280px);">
            <div class="text-[var(--text-muted)] text-[11px] font-mono">
                {{ core()->getCurrentChannel()->name }}
            </div>

            <div class="flex items-center gap-3">
                <!-- Currencies Dropdown -->
                @if ($showCurrencies)
                    <x-portal::dropdown position="bottom-right">
                        <x-slot:toggle>
                            <button
                                type="button"
                                class="flex items-center gap-1 text-[11px] font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors focus:outline-none"
                            >
                                <span>{{ core()->getCurrentCurrencyCode() }} ({{ core()->getCurrentCurrency()->symbol }})</span>
                                <span class="icon-arrow-down text-[10px] opacity-70"></span>
                            </button>
                        </x-slot>

                        <x-slot:content class="!p-1 min-w-[100px] shadow-sm rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface)]">
                            @foreach (core()->getCurrentChannel()->currencies as $currency)
                                <a
                                    href="{{ route('portal.currency.switch', $currency->code) }}"
                                    class="flex items-center px-2.5 py-1.5 text-xs rounded transition-colors {{ core()->getCurrentCurrencyCode() == $currency->code ? 'font-bold text-[var(--brand-primary)] bg-[var(--brand-primary-light)]' : 'text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)]' }}"
                                >
                                    {{ $currency->code }} ({{ $currency->symbol }})
                                </a>
                            @endforeach
                        </x-slot>
                    </x-portal::dropdown>
                @endif

                <!-- Locales Dropdown -->
                @if ($showLocales && core()->getCurrentChannel()->locales->count() > 1)
                    <x-portal::dropdown position="bottom-right">
                        <x-slot:toggle>
                            <button
                                type="button"
                                class="flex items-center gap-1 text-[11px] font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors focus:outline-none"
                            >
                                <span>{{ core()->getCurrentLocale()->name }}</span>
                                <span class="icon-arrow-down text-[10px] opacity-70"></span>
                            </button>
                        </x-slot>

                        <x-slot:content class="!p-1 min-w-[120px] shadow-sm rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface)]">
                            @foreach (core()->getCurrentChannel()->locales as $locale)
                                <a
                                    href="{{ route('portal.locale.switch', $locale->code) }}"
                                    class="flex items-center px-2.5 py-1.5 text-xs rounded transition-colors {{ app()->getLocale() == $locale->code ? 'font-bold text-[var(--brand-primary)] bg-[var(--brand-primary-light)]' : 'text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)]' }}"
                                >
                                    {{ $locale->name }}
                                </a>
                            @endforeach
                        </x-slot>
                    </x-portal::dropdown>
                @endif
            </div>
        </div>
    </div>
@endif

{!! view_render_event('bagisto.portal.layout.header.desktop.top.after') !!}
