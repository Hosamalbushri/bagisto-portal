@php
    $footerSettings = portal_layout_settings('footer');

    $customBio = data_get($footerSettings, 'contact.bio')
        ?: (core()->getCurrentChannel()->description ?? trans('portal::app.home.hero.subtitle'));

    $phone = data_get($footerSettings, 'contact.phone');
    $email = data_get($footerSettings, 'contact.email');
    $address = data_get($footerSettings, 'contact.address');

    $isSocialActive = (bool) data_get($footerSettings, 'social.status', true);
    $platforms = data_get($footerSettings, 'social.platforms', []);

    $channelName = core()->getCurrentChannel()->name ?? config('app.name');
    $currentYear = date('Y');

    $copyrightText = data_get($footerSettings, 'copyright.text');
    if (! empty($copyrightText)) {
        $formattedCopyright = str_replace(
            ['{year}', '{channel}'],
            [$currentYear, $channelName],
            $copyrightText
        );
    } else {
        $formattedCopyright = "&copy; {$currentYear} {$channelName}. " . trans('portal::app.footer.all-rights-reserved');
    }
@endphp

{!! view_render_event('bagisto.portal.layout.footer.before') !!}

<footer class="relative border-t border-[var(--border-subtle)] bg-[var(--bg-surface)] mt-20 transition-colors duration-200 overflow-hidden">
    <!-- Top Accent Highlight Gradient Line -->
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-[var(--brand-primary)]/40 to-transparent" aria-hidden="true"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand & Contact Column -->
            <div class="space-y-4">
                <a href="{{ portal_home_url() }}" class="flex items-center gap-2.5">
                    @if ($logo = core()->getCurrentChannel()->logo_url)
                        <img src="{{ $logo }}" class="h-8 w-auto" alt="{{ config('app.name') }}" />
                    @else
                        <span class="flex items-center gap-2">
                            <svg class="h-7 w-7 rounded-lg shrink-0 shadow-sm" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="32" height="32" rx="8" fill="#1c66f6"/>
                                <path d="M10 8H20C21.6569 8 23 9.34315 23 11C23 12.6569 21.6569 14 20 14H10V8Z" fill="#fe8312"/>
                                <path d="M10 14H19L23 24H18L15 16.5H10V24H7V8H10V14Z" fill="white"/>
                            </svg>
                            <span class="text-xl font-bold text-[var(--text-primary)] font-cairo">
                                {{ $channelName }}
                            </span>
                        </span>
                    @endif
                </a>

                @if ($customBio)
                    <p class="text-sm text-[var(--text-secondary)] leading-relaxed">
                        {{ $customBio }}
                    </p>
                @endif

                <!-- Contact Details -->
                @if ($phone || $email || $address)
                    <div class="space-y-2 pt-2 text-xs text-[var(--text-secondary)]">
                        @if ($phone)
                            <div class="flex items-center gap-2">
                                <span class="icon-phone text-sm text-[var(--brand-primary)]"></span>
                                <a href="tel:{{ $phone }}" class="hover:underline hover:text-[var(--brand-primary)]" dir="ltr">{{ $phone }}</a>
                            </div>
                        @endif

                        @if ($email)
                            <div class="flex items-center gap-2">
                                <span class="icon-mail text-sm text-[var(--brand-primary)]"></span>
                                <a href="mailto:{{ $email }}" class="hover:underline hover:text-[var(--brand-primary)]">{{ $email }}</a>
                            </div>
                        @endif

                        @if ($address)
                            <div class="flex items-center gap-2">
                                <span class="icon-location text-sm text-[var(--brand-primary)]"></span>
                                <span>{{ $address }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Social Media Platforms -->
                @if ($isSocialActive && ! empty($platforms))
                    <div class="flex items-center flex-wrap gap-2.5 pt-2 text-[var(--text-muted)]">
                        @if (! empty($platforms['twitter']))
                            <a href="{{ $platforms['twitter'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-8 h-8 rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:border-[var(--brand-primary)]/40 hover:-translate-y-0.5 transition-all" title="X (Twitter)">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </a>
                        @endif

                        @if (! empty($platforms['instagram']))
                            <a href="{{ $platforms['instagram'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-8 h-8 rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] hover:text-pink-600 hover:border-pink-500/40 hover:-translate-y-0.5 transition-all" title="Instagram">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        @endif

                        @if (! empty($platforms['facebook']))
                            <a href="{{ $platforms['facebook'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-8 h-8 rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] hover:text-blue-600 hover:border-blue-500/40 hover:-translate-y-0.5 transition-all" title="Facebook">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                        @endif

                        @if (! empty($platforms['youtube']))
                            <a href="{{ $platforms['youtube'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-8 h-8 rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] hover:text-red-600 hover:border-red-500/40 hover:-translate-y-0.5 transition-all" title="YouTube">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                        @endif

                        @if (! empty($platforms['linkedin']))
                            <a href="{{ $platforms['linkedin'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-8 h-8 rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] hover:text-blue-700 hover:border-blue-500/40 hover:-translate-y-0.5 transition-all" title="LinkedIn">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            </a>
                        @endif

                        @if (! empty($platforms['whatsapp']))
                            @php
                                $waUrl = str_starts_with($platforms['whatsapp'], 'http')
                                     ? $platforms['whatsapp']
                                    : 'https://wa.me/' . preg_replace('/[^0-9]/', '', $platforms['whatsapp']);
                            @endphp
                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-8 h-8 rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] hover:text-green-500 hover:border-green-500/40 hover:-translate-y-0.5 transition-all" title="WhatsApp">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            </a>
                        @endif

                        @if (! empty($platforms['telegram']))
                            <a href="{{ $platforms['telegram'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-8 h-8 rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] hover:text-sky-500 hover:border-sky-500/40 hover:-translate-y-0.5 transition-all" title="Telegram">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                            </a>
                        @endif

                        @if (! empty($platforms['tiktok']))
                            <a href="{{ $platforms['tiktok'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-8 h-8 rounded-lg border border-[var(--border-subtle)] bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:border-[var(--brand-primary)]/40 hover:-translate-y-0.5 transition-all" title="TikTok">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Footer Column 1 Slot -->
            <div>
                <x-portal::menu-slot
                    id="footer_column_1"
                    location="footer"
                    direction="column"
                    class="space-y-2.5 text-sm"
                />
            </div>

            <!-- Footer Column 2 Slot -->
            <div>
                <x-portal::menu-slot
                    id="footer_column_2"
                    location="footer"
                    direction="column"
                    class="space-y-2.5 text-sm"
                />
            </div>

            <!-- Footer Column 3 Slot -->
            <div>
                <x-portal::menu-slot
                    id="footer_column_3"
                    location="footer"
                    direction="column"
                    class="space-y-2.5 text-sm"
                />
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="border-t border-[var(--border-subtle)] py-6 bg-[var(--bg-surface-elevated)]/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[var(--text-muted)]">
            <div class="flex items-center gap-3">
                <x-portal::badge variant="success" dot class="text-[11px] !py-0.5 !px-2.5 font-mono">
                    @lang('portal::app.footer.status-operational')
                </x-portal::badge>
                <p>{!! $formattedCopyright !!}</p>
            </div>

            <x-portal::menu-slot
                id="footer_bottom"
                location="footer"
                class="flex items-center gap-6"
            />
        </div>
    </div>
</footer>

{!! view_render_event('bagisto.portal.layout.footer.after') !!}
