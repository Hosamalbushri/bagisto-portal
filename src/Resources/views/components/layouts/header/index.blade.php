{!! view_render_event('bagisto.portal.layout.header.before') !!}

<header class="sticky top-0 z-40 w-full bg-[var(--header-bg,var(--bg-surface))]/90 dark:bg-zinc-900/90 backdrop-blur-md border-b border-[var(--border-subtle)] shadow-xs transition-colors">
    <x-portal::layouts.header.desktop />

    <x-portal::layouts.header.mobile />
</header>

{!! view_render_event('bagisto.portal.layout.header.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-dark-template"
    >
        <div @click="toggle" class="inline-flex cursor-pointer">
            <slot>
                <button
                    type="button"
                    class="flex items-center justify-center w-9 h-9 rounded-lg text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] transition-all cursor-pointer focus:outline-none"
                    :title="isDarkMode ? '@lang('portal::app.home.theme.light-mode')' : '@lang('portal::app.home.theme.dark-mode')'"
                    :aria-label="isDarkMode ? '@lang('portal::app.home.theme.light-mode')' : '@lang('portal::app.home.theme.dark-mode')'"
                >
                    <span :class="isDarkMode ? 'icon-light text-amber-400' : 'icon-dark text-[var(--text-secondary)]'" class="text-lg"></span>
                </button>
            </slot>
        </div>
    </script>

    <script type="module">
        app.component('v-dark', {
            template: '#v-dark-template',

            data() {
                const cookie = document.cookie.match(/(?:^|; )portal_dark_mode=([^;]*)/);
                const manualPreference = cookie ? cookie[1] : localStorage.getItem('portal_dark_mode');
                const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = manualPreference !== null ? manualPreference === '1' : systemPrefersDark;

                return {
                    isDarkMode: isDark,
                };
            },

            methods: {
                toggle() {
                    this.isDarkMode = ! this.isDarkMode;
                    document.cookie = 'portal_dark_mode=' + (this.isDarkMode ? '1' : '0') + '; path=/; max-age=31536000; SameSite=Lax';
                    localStorage.setItem('portal_dark_mode', this.isDarkMode ? '1' : '0');

                    if (this.isDarkMode) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                },
            },
        });
    </script>
@endPushOnce
