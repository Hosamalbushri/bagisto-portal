{!! view_render_event('bagisto.portal.layout.header.before') !!}

@php
    $channel = core()->getCurrentChannel();
    $localesCount = 0;
    if ($channel) {
        if (isset($channel->locales)) {
            $localesCount = is_countable($channel->locales) ? count($channel->locales) : 0;
        } elseif (method_exists($channel, 'locales')) {
            $localesCount = $channel->locales()->count();
        }
    }

    $currenciesCount = 0;
    if ($channel) {
        if (isset($channel->currencies)) {
            $currenciesCount = is_countable($channel->currencies) ? count($channel->currencies) : 0;
        } elseif (method_exists($channel, 'currencies')) {
            $currenciesCount = $channel->currencies()->count();
        }
    }

    $hasTopBarContent = ($localesCount > 1)
        || ($currenciesCount > 1)
        || ! empty(core()->getConfigData('general.content.header_offer.title'))
        || ! empty(data_get(portal_layout_settings('header'), 'announcement.text'));
@endphp

@if ($hasTopBarContent)
    <div class="max-lg:hidden relative z-[60]">
        <x-portal::layouts.header.desktop.top />
    </div>
@endif

<header class="shadow-gray sticky top-0 z-40 bg-white dark:bg-zinc-900 border-b border-[var(--border-subtle)] shadow-xs max-lg:shadow-none transition-all duration-300">
    <!-- Desktop Header -->
    <div class="max-lg:hidden">
        <x-portal::layouts.header.desktop.bottom />
    </div>

    <!-- Mobile Header -->
    <div class="lg:hidden">
        <x-portal::layouts.header.mobile />
    </div>
</header>

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
