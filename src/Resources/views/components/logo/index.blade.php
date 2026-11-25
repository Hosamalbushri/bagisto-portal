@props([
    'alt' => core()->getCurrentChannel()?->name ?? config('app.name', 'Portal'),
    'fallbackText' => false,
])

@php
    $lightLogo = portal_logo('light');
    $darkLogo = portal_logo('dark');
    $hasDifferentLogos = $lightLogo && $darkLogo && $lightLogo !== $darkLogo;
@endphp

@if ($hasDifferentLogos)
    <img
        src="{{ $lightLogo }}"
        alt="{{ $alt }}"
        {{ $attributes->merge(['class' => 'portal-logo-light']) }}
    />
    <img
        src="{{ $darkLogo }}"
        alt="{{ $alt }}"
        {{ $attributes->merge(['class' => 'portal-logo-dark']) }}
    />
@elseif ($lightLogo ?: $darkLogo)
    <img
        src="{{ $lightLogo ?: $darkLogo }}"
        alt="{{ $alt }}"
        {{ $attributes }}
    />
@elseif ($fallbackText)
    <span class="flex items-center justify-center gap-2">
        <svg class="h-7 w-7 rounded-lg shrink-0 shadow-sm" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="32" height="32" rx="8" fill="#1c66f6"/>
            <path d="M10 8H20C21.6569 8 23 9.34315 23 11C23 12.6569 21.6569 14 20 14H10V8Z" fill="#fe8312"/>
            <path d="M10 14H19L23 24H18L15 16.5H10V24H7V8H10V14Z" fill="white"/>
        </svg>
        <span class="text-xl font-bold tracking-tight text-[var(--text-primary)] font-cairo">
            {{ $alt }}
        </span>
    </span>
@else
    <span class="font-semibold text-base tracking-tight text-[var(--text-primary)]">
        {{ $alt }}
    </span>
@endif
