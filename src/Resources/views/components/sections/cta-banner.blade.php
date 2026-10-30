@props(['options' => []])

@php
    $heading = $options['heading'] ?? '';
    $description = $options['description'] ?? '';
    $buttonText = $options['button_text'] ?? '';
    $buttonUrl = $options['button_url'] ?? '#';
@endphp

<div class="container mt-20 max-1180:px-5 max-md:mt-10 max-md:px-4" v-pre>
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#0b101b] via-[#111827] to-[#0b101b] border border-[var(--border-subtle)] px-12 py-16 text-center text-white max-md:px-6 max-md:py-10 shadow-[var(--shadow-card)]">
        <div class="m-auto max-w-[700px] relative z-10">
            @if ($heading)
                <h2 class="font-cairo font-bold text-4xl max-md:text-3xl max-sm:text-xl text-white">
                    {{ $heading }}
                </h2>
            @endif

            @if ($description)
                <p class="mt-4 text-lg max-sm:text-sm text-zinc-300 leading-relaxed">
                    {{ $description }}
                </p>
            @endif

            @if ($buttonText)
                <div class="mt-8 flex justify-center">
                    <a
                        href="{{ $buttonUrl }}"
                        class="btn-primary !px-10 !py-3.5 text-base shadow-md hover:shadow-[var(--shadow-glow)]"
                    >
                        {{ $buttonText }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

