@props(['options' => []])

@php
    $heading = $options['heading'] ?? '';
    $subheading = $options['subheading'] ?? '';
    $items = $options['items'] ?? [];
@endphp

<div class="container mt-20 max-1180:px-5 max-md:mt-10 max-md:px-4" v-pre>
    @if ($heading || $subheading)
        <div class="text-center max-w-[700px] m-auto mb-12 max-md:mb-8">
            @if ($heading)
                <h2 class="font-dmserif text-4xl max-md:text-3xl max-sm:text-xl text-zinc-900">
                    {{ $heading }}
                </h2>
            @endif

            @if ($subheading)
                <p class="mt-3 text-lg max-sm:text-sm text-zinc-500 leading-relaxed">
                    {{ $subheading }}
                </p>
            @endif
        </div>
    @endif

    @if (! empty($items))
        <div class="grid grid-cols-2 gap-6 max-md:grid-cols-1">
            @foreach ($items as $item)
                <div class="flex flex-col justify-between rounded-xl border border-zinc-200 bg-white p-8 max-md:p-6">
                    <p class="text-base text-zinc-600 italic leading-relaxed mb-6">
                        "{{ $item['content'] ?? '' }}"
                    </p>

                    <div>
                        <p class="font-dmserif text-base text-zinc-900">
                            {{ $item['name'] ?? '' }}
                        </p>

                        <p class="text-xs text-zinc-400 mt-0.5">
                            {{ $item['role'] ?? '' }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

