@props([
    'hasHeader'  => true,
    'hasFeature' => true,
    'hasFooter'  => true,
])

<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
    class="{{ request()->cookie('portal_dark_mode') ? 'dark' : '' }}"
>
<head>
    {!! view_render_event('bagisto.portal.layout.head.before') !!}

    <script>
        (function() {
            var cookie = document.cookie.match(/(?:^|; )portal_dark_mode=([^;]*)/);
            var manualPreference = cookie ? cookie[1] : localStorage.getItem('portal_dark_mode');
            if (manualPreference === '1') {
                document.documentElement.classList.add('dark');
            } else if (manualPreference === '0') {
                document.documentElement.classList.remove('dark');
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <title>{{ $title ?? config('app.name') }}</title>

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="content-language" content="{{ app()->getLocale() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="base-url" content="{{ url()->to('/') }}">
    <meta name="currency" content="{{ core()->getCurrentCurrency()->toJson() }}">
    <meta name="generator" content="Bagisto">
    <meta name="theme-color" content="#1c66f6">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    @stack('meta')

    <link rel="manifest" href="{{ route('portal.manifest') }}">

    <link
        rel="icon"
        sizes="16x16"
        href="{{ core()->getCurrentChannel()->favicon_url ?? portal_asset('images/favicon.ico', asset('favicon.ico')) }}"
    />

    @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

    @if ($customThemeCss = portal_theme_css())
        <style id="portal-theme-custom-tokens">
            {!! $customThemeCss !!}
        </style>
    @else
        <style id="portal-theme-custom-tokens">
            :root {
                --header-bg: var(--bg-surface, #ffffff);
                --header-text: var(--text-primary, #090d16);
                --header-height: 4rem;
                --footer-bg: #0f172a;
                --footer-text: #94a3b8;
                --btn-radius: 0.5rem;
                --card-radius: 1rem;
                --container-max-width: 1280px;
                --page-padding-x: 1rem;
            }
            .dark, [data-theme="dark"] {
                --header-bg: #0f141f;
                --header-text: #f8fafc;
                --footer-bg: #080b11;
                --footer-text: #94a3b8;
            }
            .portal-container {
                width: 100%;
                max-width: var(--container-max-width, 1280px);
                margin-inline: auto;
                padding-inline: var(--page-padding-x, 1rem);
            }
            .portal-header, header[data-portal-header], header.portal-header {
                background-color: var(--header-bg, var(--bg-surface)) !important;
                color: var(--header-text, var(--text-primary)) !important;
                min-height: var(--header-height, 4rem);
            }
            .portal-footer, footer[data-portal-footer], footer.portal-footer {
                background-color: var(--footer-bg, var(--bg-surface-elevated)) !important;
                color: var(--footer-text, var(--text-secondary)) !important;
            }
            .portal-card, [data-portal-card], .glass-card, [data-spotlight], .rounded-card {
                border-radius: var(--card-radius, 1rem) !important;
            }
            .btn-primary, .primary-button, [data-portal-btn="primary"], button.primary-button, a.primary-button {
                border-radius: var(--btn-radius, 0.5rem) !important;
                background-color: var(--brand-primary) !important;
                color: var(--on-brand-primary, #ffffff) !important;
            }
            .btn-accent, [data-portal-btn="accent"] {
                border-radius: var(--btn-radius, 0.5rem) !important;
                background-color: var(--brand-accent) !important;
                color: var(--on-brand-accent, #090d16) !important;
            }
            .btn-secondary, .secondary-button, [data-portal-btn="secondary"], button.secondary-button, a.secondary-button, .btn-outline, .btn-ghost {
                border-radius: var(--btn-radius, 0.5rem) !important;
            }
            input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="search"], select, textarea {
                border-radius: var(--btn-radius, 0.5rem) !important;
            }
        </style>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" />

    @stack('styles')

    @if ($customCss = core()->getConfigData('general.content.custom_scripts.custom_css'))
        <style id="portal-channel-custom-css">
            {!! $customCss !!}
        </style>
    @endif

    @if (core()->getConfigData('general.content.speculation_rules.enabled'))
        <script type="speculationrules">
            @json(core()->getSpeculationRules(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        </script>
    @endif

    {!! view_render_event('bagisto.portal.layout.head.after') !!}
</head>

<body class="antialiased min-h-screen flex flex-col justify-between overflow-x-hidden transition-colors duration-200" style="background-color: var(--bg-app); color: var(--text-primary);">
    {!! view_render_event('bagisto.portal.layout.body.before') !!}

    <a
        href="#main"
        class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:start-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-[var(--bg-surface-elevated)] focus:text-[var(--brand-primary)] focus:rounded-xl focus:shadow-lg focus:border focus:border-[var(--border-subtle)] focus:font-semibold"
    >
        @lang('portal::app.components.layouts.index.skip-to-main-content')
    </a>

    <div id="app" class="flex min-h-screen flex-col justify-between">
        <x-portal::flash-group />

        <x-portal::modal.confirm />

        @if ($hasHeader)
            <x-portal::layouts.header />
        @endif

        {!! view_render_event('bagisto.portal.layout.content.before') !!}

        <main id="main" class="flex-1">
            {{ $slot }}
        </main>

        {!! view_render_event('bagisto.portal.layout.content.after') !!}

        @if ($hasFeature)
            <x-portal::layouts.services />
        @endif

        @if ($hasFooter)
            <x-portal::layouts.footer />
        @endif

        @if (
            core()->getConfigData('general.gdpr.settings.enabled')
            && core()->getConfigData('general.gdpr.cookie.enabled')
        )
            <x-portal::layouts.cookie />
        @endif
    </div>

    {!! view_render_event('bagisto.portal.layout.body.after') !!}

    <x-portal::layouts.webmcp />

    @stack('scripts')

    {!! view_render_event('bagisto.portal.layout.vue-app-mount.before') !!}
    <script>
        function mountApp() {
            const vueApp = window.app || (typeof app !== "undefined" ? app : null);
            if (vueApp && !vueApp._instance) {
                vueApp.mount("#app");
            }
        }

        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", mountApp);
        } else {
            mountApp();
        }
    </script>
    {!! view_render_event('bagisto.portal.layout.vue-app-mount.after') !!}

    @if ($customJs = core()->getConfigData('general.content.custom_scripts.custom_javascript'))
        <script type="text/javascript" id="portal-channel-custom-js">
            {!! $customJs !!}
        </script>
    @endif
</body>
</html>

