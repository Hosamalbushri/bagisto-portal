@php
    $slides = $options['slides'] ?? [];
@endphp

@if (! empty($slides))
    <section class="relative overflow-hidden bg-slate-900 text-white py-20 lg:py-32">
        <div class="container mx-auto px-4 max-w-7xl">
            @foreach ($slides as $slide)
                <div class="max-w-3xl mx-auto text-center space-y-6">
                    @if (! empty($slide['title']))
                        <h1 class="text-3xl md:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight">
                            {{ $slide['title'] }}
                        </h1>
                    @endif

                    @if (! empty($slide['subtitle']))
                        <p class="text-lg md:text-xl text-slate-300 leading-relaxed">
                            {{ $slide['subtitle'] }}
                        </p>
                    @endif

                    @if (! empty($slide['button_text']))
                        <div class="pt-4">
                            <a
                                href="{{ $slide['button_url'] ?? '#' }}"
                                class="inline-flex items-center justify-center px-8 py-3.5 text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200"
                            >
                                {{ $slide['button_text'] }}
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif
