@php
    $heading = $options['heading'] ?? '';
    $subheading = $options['subheading'] ?? '';
    $items = $options['items'] ?? [];
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @foreach ($items as $item)
                    <div class="p-8 bg-slate-50 border border-slate-200/80 rounded-2xl flex flex-col justify-between space-y-6">
                        <p class="text-gray-700 italic text-base md:text-lg leading-relaxed">
                            "{{ $item['content'] ?? '' }}"
                        </p>
                        <div>
                            <div class="font-bold text-gray-900">
                                {{ $item['name'] ?? '' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $item['role'] ?? '' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
