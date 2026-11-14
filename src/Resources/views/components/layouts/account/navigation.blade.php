@php
    $portalGuard = \Platform\Portal\Helpers\PortalAuth::guard();
    $user = auth()->guard($portalGuard)->check()
        ? auth()->guard($portalGuard)->user()
        : (auth()->guard('customer')->check() ? auth()->guard('customer')->user() : null);

    $userName = $user?->name ?? $user?->first_name ?? 'User';
    $userEmail = $user?->email ?? '';
@endphp

{!! view_render_event('bagisto.portal.layout.account.navigation.before') !!}

<div class="panel-side grid min-w-[280px] max-w-[340px] gap-6 rounded-2xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] p-5 shadow-sm">
    <!-- User Avatar & Profile Card -->
    <div class="flex items-center gap-4 border-b border-[var(--border-subtle)] pb-5">
        <div class="w-12 h-12 rounded-full bg-[var(--brand-primary-light)] text-[var(--brand-primary)] flex items-center justify-center font-bold text-lg">
            {{ strtoupper(substr($userName, 0, 1)) }}
        </div>

        <div class="flex flex-col min-w-0">
            <p class="text-base font-bold text-[var(--text-primary)] truncate">
                {{ $userName }}
            </p>
            <p class="text-xs text-[var(--text-muted)] truncate">
                {{ $userEmail }}
            </p>
        </div>
    </div>

    <!-- Account Navigation Links -->
    <nav class="space-y-1">
        @if (function_exists('menu') && count(menu()->getItems('customer') ?? []) > 0)
            @foreach (menu()->getItems('customer') as $item)
                <a
                    href="{{ $item->getUrl() }}"
                    class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $item->isActive() ? 'bg-[var(--brand-primary-light)] text-[var(--brand-primary)] font-semibold' : 'text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)] hover:text-[var(--text-primary)]' }}"
                >
                    <span class="flex items-center gap-3">
                        @if ($item->getIcon())
                            <span class="{{ $item->getIcon() }} text-lg"></span>
                        @endif
                        <span>{{ $item->getName() }}</span>
                    </span>
                    <span class="icon-arrow-right rtl:rotate-180 text-xs opacity-50"></span>
                </a>
            @endforeach
        @else
            <a
                href="{{ url('/') }}"
                class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)] hover:text-[var(--text-primary)] transition-colors"
            >
                <span class="flex items-center gap-3">
                    <span class="icon-store text-lg"></span>
                    <span>@lang('portal::app.home.nav.home')</span>
                </span>
                <span class="icon-arrow-right rtl:rotate-180 text-xs opacity-50"></span>
            </a>
        @endif
    </nav>
</div>

{!! view_render_event('bagisto.portal.layout.account.navigation.after') !!}
