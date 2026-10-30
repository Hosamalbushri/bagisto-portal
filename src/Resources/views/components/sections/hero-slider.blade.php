@props(['options' => []])

@php
    $slides = array_values($options['slides'] ?? []);
@endphp

@if (! empty($slides))
    <v-hero-slider :slides='@json($slides)'>
        {{-- SSR first slide fallback to prevent layout shift before Vue mount --}}
        @php $firstSlide = $slides[0]; @endphp
        <div class="relative overflow-hidden bg-zinc-950 text-white min-h-[480px] max-md:min-h-[360px] flex items-center justify-center py-20 px-4">
            @if (! empty($firstSlide['image']))
                <img
                    src="{{ str_starts_with($firstSlide['image'], 'http') || str_starts_with($firstSlide['image'], '/') ? $firstSlide['image'] : asset($firstSlide['image']) }}"
                    alt="{{ $firstSlide['title'] ?? '' }}"
                    class="absolute inset-0 w-full h-full object-cover opacity-30 select-none pointer-events-none"
                />
            @endif

            <div class="container relative z-10 m-auto text-center max-w-[850px] max-1180:px-5 max-md:px-4">
                @if (! empty($firstSlide['title']))
                    <h1 class="font-dmserif text-5xl leading-tight max-md:text-3xl max-sm:text-2xl">
                        {{ $firstSlide['title'] }}
                    </h1>
                @endif

                @if (! empty($firstSlide['subtitle']))
                    <p class="mt-4 text-xl text-zinc-300 max-md:text-base max-sm:text-sm leading-relaxed">
                        {{ $firstSlide['subtitle'] }}
                    </p>
                @endif

                @if (! empty($firstSlide['button_text']))
                    <div class="mt-8 flex justify-center">
                        <a
                            href="{{ $firstSlide['button_url'] ?? '#' }}"
                            class="inline-block rounded-2xl bg-white px-11 py-3.5 text-center text-base font-semibold text-zinc-900 transition-all hover:bg-zinc-100 shadow-md max-md:rounded-lg max-md:py-2.5 max-sm:py-2"
                        >
                            {{ $firstSlide['button_text'] }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </v-hero-slider>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-hero-slider-template">
            <div class="relative overflow-hidden bg-zinc-950 text-white min-h-[480px] max-md:min-h-[360px]">
                <div
                    v-for="(slide, index) in slides"
                    :key="index"
                    v-show="currentIndex === index"
                    class="relative min-h-[480px] max-md:min-h-[360px] flex items-center justify-center py-20 px-4 transition-opacity duration-700 ease-in-out"
                >
                    <img
                        v-if="slide.image"
                        :src="resolveImage(slide.image)"
                        :alt="slide.title || ''"
                        class="absolute inset-0 w-full h-full object-cover opacity-30 select-none pointer-events-none"
                    />

                    <div class="container relative z-10 m-auto text-center max-w-[850px] max-1180:px-5 max-md:px-4">
                        <h1 v-if="slide.title" class="font-dmserif text-5xl leading-tight max-md:text-3xl max-sm:text-2xl">
                            @{{ slide.title }}
                        </h1>

                        <p v-if="slide.subtitle" class="mt-4 text-xl text-zinc-300 max-md:text-base max-sm:text-sm leading-relaxed">
                            @{{ slide.subtitle }}
                        </p>

                        <div v-if="slide.button_text" class="mt-8 flex justify-center">
                            <a
                                :href="slide.button_url || '#'"
                                class="inline-block rounded-2xl bg-white px-11 py-3.5 text-center text-base font-semibold text-zinc-900 transition-all hover:bg-zinc-100 shadow-md max-md:rounded-lg max-md:py-2.5 max-sm:py-2"
                            >
                                @{{ slide.button_text }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Slider Arrows (if more than 1 slide) -->
                <template v-if="slides.length > 1">
                    <button
                        type="button"
                        @click="prev"
                        class="absolute start-4 top-1/2 -translate-y-1/2 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur hover:bg-white/40 transition"
                        aria-label="Previous Slide"
                    >
                        <span class="icon-arrow-left rtl:rotate-180 text-xl"></span>
                    </button>

                    <button
                        type="button"
                        @click="next"
                        class="absolute end-4 top-1/2 -translate-y-1/2 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur hover:bg-white/40 transition"
                        aria-label="Next Slide"
                    >
                        <span class="icon-arrow-right rtl:rotate-180 text-xl"></span>
                    </button>

                    <!-- Dot Indicators -->
                    <div class="absolute bottom-6 start-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
                        <button
                            v-for="(_, index) in slides"
                            :key="index"
                            type="button"
                            @click="goTo(index)"
                            :class="currentIndex === index ? 'w-8 bg-white' : 'w-2 bg-white/40'"
                            class="h-2 rounded-full transition-all duration-300"
                            :aria-label="'Slide ' + (index + 1)"
                        ></button>
                    </div>
                </template>
            </div>
        </script>

        <script type="module">
            app.component('v-hero-slider', {
                template: '#v-hero-slider-template',

                props: {
                    slides: {
                        type: Array,
                        default: () => [],
                    },
                },

                data() {
                    return {
                        currentIndex: 0,
                        timer: null,
                    };
                },

                mounted() {
                    if (this.slides.length > 1) {
                        this.startAutoPlay();
                    }
                },

                beforeUnmount() {
                    this.stopAutoPlay();
                },

                methods: {
                    resolveImage(image) {
                        if (!image) return '';
                        if (image.startsWith('http://') || image.startsWith('https://') || image.startsWith('/')) {
                            return image;
                        }
                        return '{{ asset('') }}' + image;
                    },

                    next() {
                        this.currentIndex = (this.currentIndex + 1) % this.slides.length;
                        this.restartAutoPlay();
                    },

                    prev() {
                        this.currentIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
                        this.restartAutoPlay();
                    },

                    goTo(index) {
                        this.currentIndex = index;
                        this.restartAutoPlay();
                    },

                    startAutoPlay() {
                        this.timer = setInterval(() => {
                            this.currentIndex = (this.currentIndex + 1) % this.slides.length;
                        }, 5000);
                    },

                    stopAutoPlay() {
                        if (this.timer) {
                            clearInterval(this.timer);
                            this.timer = null;
                        }
                    },

                    restartAutoPlay() {
                        this.stopAutoPlay();
                        this.startAutoPlay();
                    },
                },
            });
        </script>
    @endpushOnce
@endif
