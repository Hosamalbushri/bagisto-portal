@props(['options' => []])

@php
    $title = $options['title'] ?? '';
    $html = $options['html'] ?? '';
    $css = $options['css'] ?? '';
@endphp

@if (! empty($css))
    @push ('styles')
        <style>
            {!! $css !!}
        </style>
    @endpush
@endif

<div class="portal-container mx-auto mt-16 max-md:mt-8" v-pre>
    <div class="m-auto max-w-[850px]">
        @if ($title)
            <h2 class="font-cairo font-bold text-3xl max-md:text-2xl text-[var(--text-primary)] mb-6">
                {{ $title }}
            </h2>
        @endif

        @if ($html)
            <div class="text-[var(--text-secondary)] leading-relaxed text-base">
                {!! $html !!}
            </div>
        @endif
    </div>
</div>

