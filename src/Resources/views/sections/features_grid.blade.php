@php
    $heading = $options['heading'] ?? '';
    $subheading = $options['subheading'] ?? '';
    $items = $options['items'] ?? [];
    $columns = (int) ($options['columns'] ?? 3);
    $colClass = match ($columns) {
        2 => 'grid-cols-1 md:grid-cols-2',
        4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        default => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    };
@endphp

<section class="py-16 lg:py-24 bg-white">
    <div class="container mx-auto px-4 max-w-7xl">
        @if ($heading || $subheading)
            <div class="text-center max-w-2xl mx-auto mb-14 space-y-3">
                @if ($heading)
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight sm:text-4xl">
                        {{ $heading }}
                    </h2>
                @endif
                @if ($subheading)
                    <p class="text-lg text-gray-600">
                        {{ $subheading }}
                    </p>
                @endif
            </div>
        @endif

        @if (! empty($items))
            <div class="grid {{ $colClass }} gap-8">
                @foreach ($items as $item)
                    <div class="p-8 bg-slate-50 border border-slate-200/80 rounded-2xl hover:border-blue-400 hover:shadow-md transition-all duration-200">
                        <div class="w-12 h-12 mb-6 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-2xl font-bold">
                            <span class="{{ $item['icon'] ?? 'icon-star' }}"></span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">
                            {{ $item['title'] ?? '' }}
                        </h3>
                        <p class="text-gray-600 leading-relaxed text-sm md:text-base">
                            {{ $item['description'] ?? '' }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
