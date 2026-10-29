@props(['options' => []])

@php
    $heading = $options['heading'] ?? '';
    $description = $options['description'] ?? '';
    $buttonText = $options['button_text'] ?? '';
    $buttonUrl = $options['button_url'] ?? '#';
@endphp

<div class="container mt-20 max-1180:px-5 max-md:mt-10 max-md:px-4" v-pre>
    <div class="rounded-2xl bg-zinc-950 px-12 py-16 text-center text-white max-md:px-6 max-md:py-10">
        <div class="m-auto max-w-[700px]">
            @if ($heading)
                <h2 class="font-dmserif text-4xl max-md:text-3xl max-sm:text-xl">
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
                        class="inline-block rounded-2xl bg-white px-11 py-3.5 text-center text-base font-semibold text-zinc-900 transition-all hover:bg-zinc-100 shadow-sm max-md:rounded-lg max-md:py-2.5 max-sm:py-2"
                    >
                        {{ $buttonText }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

