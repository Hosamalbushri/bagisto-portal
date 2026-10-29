@php
    $title = $options['title'] ?? '';
    $html = $options['html'] ?? '';
@endphp

<section class="py-16 lg:py-24 bg-white">
    <div class="container mx-auto px-4 max-w-4xl">
        @if ($title)
            <h2 class="text-3xl font-bold text-gray-900 mb-8 tracking-tight">
                {{ $title }}
            </h2>
        @endif

        @if ($html)
            <div class="prose max-w-none text-gray-700 leading-relaxed text-base md:text-lg">
                {!! $html !!}
            </div>
        @endif
    </div>
</section>
