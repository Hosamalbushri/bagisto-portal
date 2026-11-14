{!! view_render_event('bagisto.portal.layout.header.before') !!}

<header class="portal-header sticky top-0 z-50 transition-all duration-300">
    <!-- Desktop Header -->
    <x-portal::layouts.header.desktop />

    <!-- Mobile Header -->
    <x-portal::layouts.header.mobile />
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
