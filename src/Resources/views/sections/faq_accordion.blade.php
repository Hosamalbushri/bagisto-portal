@php
    $heading = $options['heading'] ?? '';
    $subheading = $options['subheading'] ?? '';
    $items = $options['items'] ?? [];
@endphp

<section class="py-16 lg:py-24 bg-slate-50">
    <div class="container mx-auto px-4 max-w-4xl">
        @if ($heading || $subheading)
            <div class="text-center max-w-2xl mx-auto mb-12 space-y-3">
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
            <div class="space-y-4">
                @foreach ($items as $index => $item)
                    <x-portal::accordion>
                        <x-slot:header>
                            <span class="text-base md:text-lg font-semibold text-gray-900">
                                {{ $item['question'] ?? '' }}
                            </span>
                        </x-slot>

                        <x-slot:content>
                            <p class="text-gray-600 leading-relaxed text-sm md:text-base pt-2">
                                {{ $item['answer'] ?? '' }}
                            </p>
                        </x-slot>
                    </x-portal::accordion>
                @endforeach
            </div>
        @endif
    </div>
</section>
