{!! view_render_event('bagisto.portal.layout.header.before') !!}

<header class="sticky top-0 z-40 bg-[var(--header-bg,var(--bg-surface,#ffffff))]/90 dark:bg-[#0b0f19]/90 backdrop-blur-md border-b border-[var(--border-subtle,#e2e8f0)]/80 dark:border-slate-800/80">
    <x-portal::layouts.header.desktop />

    <x-portal::layouts.header.mobile />
</header>

{!! view_render_event('bagisto.portal.layout.header.after') !!}
