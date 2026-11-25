@php
    $headerSettings = portal_layout_settings('header');
    $isSticky = (bool) data_get($headerSettings, 'controls.sticky', true);
@endphp

{!! view_render_event('bagisto.portal.layout.header.before') !!}

<div class="hidden lg:block">
    <x-portal::layouts.header.desktop.top />
</div>

<header class="{{ $isSticky ? 'sticky top-0 z-40' : 'relative z-40' }} bg-white dark:bg-[#0b0f19] border-b border-[var(--border-subtle,#e2e8f0)] dark:border-slate-800 transition-all duration-200" style="background-color: var(--header-bg);">
    <x-portal::layouts.header.desktop />

    <x-portal::layouts.header.mobile />
</header>

{!! view_render_event('bagisto.portal.layout.header.after') !!}

<style>
    .portal-theme-icon {
        display: inline-block;
        line-height: 1 !important;
        speak: never;
        font-style: normal;
        font-weight: normal;
        font-variant: normal;
        text-transform: none;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    /* Light Mode: Moon icon is cool slate */
    html:not(.dark) .portal-theme-icon {
        color: #475569 !important;
    }
    html:not(.dark) button:hover .portal-theme-icon {
        color: #0f172a !important;
    }
    html:not(.dark) .portal-theme-icon:before {
        content: "\e949" !important;
        font-family: "bagisto-admin" !important;
    }

    /* Dark Mode: Sun icon is vibrant warm amber / gold */
    html.dark .portal-theme-icon {
        color: #f59e0b !important;
    }
    html.dark button:hover .portal-theme-icon {
        color: #fbbf24 !important;
    }
    html.dark .portal-theme-icon:before {
        content: "\e947" !important;
        font-family: "bagisto-admin" !important;
    }

    /* Language icon: invert default black SVG in dark mode so it matches theme */
    html.dark .portal-default-lang-icon,
    html.dark img[src*="default-language"] {
        filter: invert(1) brightness(1.2);
    }
</style>

@pushOnce('scripts')
    <script>
        function togglePortalDarkMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            const val = isDark ? '1' : '0';
            try {
                localStorage.setItem('portal_dark_mode', val);
                document.cookie = 'portal_dark_mode=' + val + '; path=/; max-age=31536000; SameSite=Lax';
            } catch (e) {
                // Ignore storage restrictions
            }

            document.querySelectorAll('.portal-theme-icon').forEach(function(icon) {
                if (isDark) {
                    icon.classList.remove('icon-dark');
                    icon.classList.add('icon-light');
                } else {
                    icon.classList.remove('icon-light');
                    icon.classList.add('icon-dark');
                }
            });

            window.dispatchEvent(new CustomEvent('portal:theme-changed', { detail: { isDark: isDark } }));
        }

        (function() {
            function syncThemeIcons() {
                const isDark = document.documentElement.classList.contains('dark');
                document.querySelectorAll('.portal-theme-icon').forEach(function(icon) {
                    if (isDark) {
                        icon.classList.remove('icon-dark');
                        icon.classList.add('icon-light');
                    } else {
                        icon.classList.remove('icon-light');
                        icon.classList.add('icon-dark');
                    }
                });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', syncThemeIcons);
            } else {
                syncThemeIcons();
            }
        })();
    </script>
@endpushOnce
