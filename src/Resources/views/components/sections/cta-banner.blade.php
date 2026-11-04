@props(['options' => []])

@php
    $heading = $options['heading'] ?? '';
    $description = $options['description'] ?? '';
    $buttonText = $options['button_text'] ?? '';
    $buttonUrl = $options['button_url'] ?? '#';
@endphp

<div class="portal-container mx-auto mt-20 max-md:mt-10" v-pre>
    <div
        class="portal-card relative overflow-hidden bg-gradient-to-br from-[#0b101b] via-[#111827] to-[#0b101b] border border-[var(--border-subtle)] px-12 py-16 text-center text-white max-md:px-6 max-md:py-10 shadow-[var(--shadow-card)]"
        data-portal-card
        style="border-radius: var(--card-radius, 1rem);"
    >
        <div class="m-auto max-w-[700px] relative z-10">
            @if ($heading)
                <h2 class="font-cairo font-bold text-4xl max-md:text-3xl max-sm:text-xl text-white">
                    {{ $heading }}
                </h2>
            @endif

            @if ($description)
                <p class="mt-4 text-lg max-sm:text-sm text-white/80 leading-relaxed">
                    {{ $description }}
                </p>
            @endif

            @if ($buttonText)
                <div class="mt-8 flex justify-center">
                    <a
                        href="{{ portal_sanitize_url($buttonUrl) }}"
                        class="btn-primary !px-10 !py-3.5 text-base shadow-md hover:shadow-[var(--shadow-glow)]"
                    >
                        {{ $buttonText }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

