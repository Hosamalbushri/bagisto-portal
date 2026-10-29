@php
    $heading = $options['heading'] ?? '';
    $description = $options['description'] ?? '';
    $buttonText = $options['button_text'] ?? '';
    $buttonUrl = $options['button_url'] ?? '#';
    $bgColor = $options['background_color'] ?? 'bg-blue-600';
@endphp

<section class="py-16 lg:py-20 {{ $bgColor }} text-white">
    <div class="container mx-auto px-4 max-w-5xl text-center space-y-6">
        @if ($heading)
            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                {{ $heading }}
            </h2>
        @endif

        @if ($description)
            <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto leading-relaxed">
                {{ $description }}
            </p>
        @endif

        @if ($buttonText)
            <div class="pt-4">
                <a
                    href="{{ $buttonUrl }}"
                    class="inline-flex items-center justify-center px-8 py-3.5 text-base font-bold text-blue-900 bg-white hover:bg-blue-50 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200"
                >
                    {{ $buttonText }}
                </a>
            </div>
        @endif
    </div>
</section>
