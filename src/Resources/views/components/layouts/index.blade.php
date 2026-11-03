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
            var isDark = cookie ? cookie[1] === '1' : (localStorage.getItem('portal_dark_mode') === '1');
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else if (cookie && cookie[1] === '0') {
                document.documentElement.classList.remove('dark');
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

    @stack('meta')

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
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" />

    @stack('styles')

    {!! view_render_event('bagisto.portal.layout.head.after') !!}
</head>

<body class="antialiased min-h-screen flex flex-col justify-between overflow-x-hidden transition-colors duration-200" style="background-color: var(--bg-app); color: var(--text-primary);">
    {!! view_render_event('bagisto.portal.layout.body.before') !!}

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
    </div>

    {!! view_render_event('bagisto.portal.layout.body.after') !!}

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
</body>
</html>

