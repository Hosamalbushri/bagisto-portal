@props([
    'isRtl' => null,
])

@php
    $isRtlCalculated = $isRtl ?? (core()->getCurrentLocale()?->direction === 'rtl');
@endphp

<div
    {{ $attributes->merge(['class' => 'absolute inset-0 w-full h-full pointer-events-none overflow-hidden transition-opacity duration-700']) }}
    aria-hidden="true"
>
    <!-- Background layer reacting to active theme -->
    <div class="absolute inset-0 bg-[var(--bg-app)] transition-colors duration-200"></div>

    <!-- Pure CSS Multi-Point Radial Gradients simulating the shader composition with 0% GPU/CPU overhead -->
    <div
        class="absolute inset-0 portal-shader-glow transition-opacity duration-300 opacity-90 dark:opacity-75"
        @if ($isRtl !== null)
            style="background-image: {{ $isRtlCalculated
                ? 'radial-gradient(circle at 18% 45%, var(--hero-glow-color) 0%, transparent 65%), radial-gradient(circle at 35% 20%, rgba(254, 131, 18, 0.08) 0%, transparent 40%), radial-gradient(circle at 10% 80%, var(--hero-glow-color) 0%, transparent 50%)'
                : 'radial-gradient(circle at 82% 45%, var(--hero-glow-color) 0%, transparent 65%), radial-gradient(circle at 65% 20%, rgba(254, 131, 18, 0.08) 0%, transparent 40%), radial-gradient(circle at 90% 80%, var(--hero-glow-color) 0%, transparent 50%)' }};"
        @endif
    ></div>

    <!-- Architectural millimeter grid overlay with coordinate precision -->
    <div class="absolute inset-0 portal-grid-pattern opacity-100"></div>
</div>

