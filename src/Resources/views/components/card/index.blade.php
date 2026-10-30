@props([
    'interactive' => false,
    'glass' => false,
    'spotlight' => true,
])

<div
    @if ($spotlight) data-spotlight @endif
    {{ $attributes->merge([
        'class' => 'relative overflow-hidden rounded-2xl border border-[var(--border-subtle)] p-6 lg:p-8 transition-all duration-300 '
            . ($glass ? 'glass-card ' : 'bg-[var(--bg-surface)] shadow-[var(--shadow-card)] ')
            . ($interactive ? 'hover:border-[var(--brand-primary)]/40 hover:-translate-y-1 hover:shadow-lg cursor-pointer ' : '')
    ]) }}
>
    {{ $slot }}
</div>

