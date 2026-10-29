@props(['options' => []])

@php
    $slides = $options['slides'] ?? [];
@endphp

@if (! empty($slides))
    <div class="relative overflow-hidden bg-zinc-950 text-white" v-pre>
        @foreach ($slides as $slide)
            <div class="relative min-h-[480px] max-md:min-h-[360px] flex items-center justify-center py-20 px-4">
                @if (! empty($slide['image']))
                    <img
                        src="{{ str_starts_with($slide['image'], 'http') || str_starts_with($slide['image'], '/') ? $slide['image'] : asset($slide['image']) }}"
                        alt="{{ $slide['title'] ?? '' }}"
                        class="absolute inset-0 w-full h-full object-cover opacity-30 select-none pointer-events-none"
                    />
                @endif

                <div class="container relative z-10 m-auto text-center max-w-[850px] max-1180:px-5 max-md:px-4">
                    @if (! empty($slide['title']))
                        <h1 class="font-dmserif text-5xl leading-tight max-md:text-3xl max-sm:text-2xl">
                            {{ $slide['title'] }}
                        </h1>
                    @endif

                    @if (! empty($slide['subtitle']))
                        <p class="mt-4 text-xl text-zinc-300 max-md:text-base max-sm:text-sm leading-relaxed">
                            {{ $slide['subtitle'] }}
                        </p>
                    @endif

                    @if (! empty($slide['button_text']))
                        <div class="mt-8 flex justify-center">
                            <a
                                href="{{ $slide['button_url'] ?? '#' }}"
                                class="inline-block rounded-2xl bg-white px-11 py-3.5 text-center text-base font-semibold text-zinc-900 transition-all hover:bg-zinc-100 shadow-md max-md:rounded-lg max-md:py-2.5 max-sm:py-2"
                            >
                                {{ $slide['button_text'] }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

