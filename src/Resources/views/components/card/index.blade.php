@props([
    'interactive' => false,
    'glass' => false,
    'spotlight' => true,
])

<div
    @if ($spotlight) data-spotlight @endif
    data-portal-card
    {{ $attributes->merge([
        'class' => 'portal-card relative overflow-hidden border border-[var(--border-subtle)] p-6 lg:p-8 transition-all duration-300 '
            . ($glass ? 'glass-card ' : 'bg-[var(--bg-surface)] shadow-[var(--shadow-card)] ')
            . ($interactive ? 'hover:border-[var(--brand-primary)]/40 hover:-translate-y-1 hover:shadow-lg cursor-pointer ' : ''),
        'style' => 'border-radius: var(--card-radius, 1rem);',
    ]) }}
>
    {{ $slot }}
</div>

