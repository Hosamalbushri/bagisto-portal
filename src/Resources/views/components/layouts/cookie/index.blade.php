<div
    id="portal-cookie-consent"
    class="fixed bottom-0 inset-x-0 z-50 p-4 sm:p-6 transition-all duration-300 transform translate-y-full"
    style="display: none;"
    role="dialog"
    aria-live="polite"
    aria-label="@lang('portal::app.components.layouts.cookie.consent.your-cookie-consent-preferences')"
>
    <div class="max-w-4xl mx-auto bg-[var(--bg-surface)]/95 backdrop-blur-md border border-[var(--border-subtle)] rounded-2xl p-5 sm:p-6 shadow-[var(--shadow-card)] flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex-1 pr-0 md:pr-4">
            <h3 class="text-base font-semibold text-[var(--text-primary)] mb-1">
                @lang('portal::app.components.layouts.cookie.consent.your-cookie-consent-preferences')
            </h3>
            <p class="text-xs sm:text-sm text-[var(--text-secondary)] leading-relaxed">
                @lang('portal::app.components.layouts.cookie.consent.basic-interactions')
            </p>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto shrink-0 justify-end">
            <button
                type="button"
                id="portal-cookie-reject"
                class="px-4 py-2 text-xs sm:text-sm font-medium rounded-xl border border-[var(--border-subtle)] text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-elevated)] transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]/40"
            >
                @lang('portal::app.components.layouts.cookie.index.reject')
            </button>

            <button
                type="button"
                id="portal-cookie-accept"
                class="px-5 py-2 text-xs sm:text-sm font-medium rounded-xl text-white bg-[var(--brand-primary)] hover:bg-[var(--brand-primary-hover)] transition-colors shadow-xs focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]/40"
            >
                @lang('portal::app.components.layouts.cookie.index.accept')
            </button>
        </div>
    </div>
</div>

@pushOnce('scripts')
<script>
    (function() {
        const COOKIE_NAME = 'portal_cookie_consent';
        const banner = document.getElementById('portal-cookie-consent');
        if (!banner) return;

        function getCookie(name) {
            const matches = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
            return matches ? decodeURIComponent(matches[1]) : undefined;
        }

        function setCookie(name, value, days) {
            let expires = '';
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = '; expires=' + date.toUTCString();
            }
            document.cookie = name + '=' + (value || '') + expires + '; path=/; SameSite=Lax';
        }

        if (getCookie(COOKIE_NAME) === undefined) {
            banner.style.display = 'block';
            setTimeout(() => {
                banner.classList.remove('translate-y-full');
            }, 150);
        }

        function closeBanner(value) {
            setCookie(COOKIE_NAME, value, 365);
            banner.classList.add('translate-y-full');
            setTimeout(() => {
                banner.style.display = 'none';
            }, 300);
        }

        const acceptBtn = document.getElementById('portal-cookie-accept');
        const rejectBtn = document.getElementById('portal-cookie-reject');

        if (acceptBtn) {
            acceptBtn.addEventListener('click', function() {
                closeBanner('accepted');
            });
        }

        if (rejectBtn) {
            rejectBtn.addEventListener('click', function() {
                closeBanner('essential');
            });
        }
    })();
</script>
@endpushOnce
