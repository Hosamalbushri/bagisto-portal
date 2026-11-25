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

@pushOnce('scripts')
    <script>
        function syncPortalThemeIcons(isDark) {
            document.querySelectorAll('[data-portal-theme-icon]').forEach(function(icon) {
                icon.classList.toggle('icon-light', isDark);
                icon.classList.toggle('icon-dark', ! isDark);
                icon.classList.toggle('text-amber-400', isDark);
            });
        }

        function togglePortalDarkMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            const val = isDark ? '1' : '0';
            try {
                localStorage.setItem('portal_dark_mode', val);
                document.cookie = 'portal_dark_mode=' + val + '; path=/; max-age=31536000; SameSite=Lax';
            } catch (e) {
                // Ignore storage restrictions
            }

            syncPortalThemeIcons(isDark);

            window.dispatchEvent(new CustomEvent('portal:theme-changed', { detail: { isDark: isDark } }));
        }

        (function() {
            const init = function() {
                syncPortalThemeIcons(document.documentElement.classList.contains('dark'));
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
@endpushOnce
