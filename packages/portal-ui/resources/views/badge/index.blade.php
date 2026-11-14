@props([
    'variant' => 'primary',
    'dot' => false,
])

@php
    $variantClass = match ($variant) {
        'accent'  => 'badge-accent',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger'  => 'badge-danger',
        'muted'   => 'badge-muted',
        default   => 'badge-primary',
    };

    $dotColorClass = match ($variant) {
        'accent'  => 'bg-[var(--brand-accent)]',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger'  => 'bg-red-500',
        'muted'   => 'bg-slate-400',
        default   => 'bg-[var(--brand-primary)]',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$variantClass}"]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full animate-pulse shrink-0 {{ $dotColorClass }}" aria-hidden="true"></span>
    @endif

    {{ $slot }}
</span>

