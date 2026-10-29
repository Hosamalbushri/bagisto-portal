@props(['options' => []])

@php
    $heading = $options['heading'] ?? '';
    $subheading = $options['subheading'] ?? '';
    $items = $options['items'] ?? [];
@endphp

<div class="container mt-20 max-1180:px-5 max-md:mt-10 max-md:px-4">
    <div class="m-auto max-w-[850px]">
        @if ($heading || $subheading)
            <div class="text-center mb-10 max-md:mb-6" v-pre>
                @if ($heading)
                    <h2 class="font-dmserif text-4xl max-md:text-3xl max-sm:text-xl text-zinc-900">
                        {{ $heading }}
                    </h2>
                @endif

                @if ($subheading)
                    <p class="mt-3 text-lg max-sm:text-sm text-zinc-500">
                        {{ $subheading }}
                    </p>
                @endif
            </div>
        @endif

        @if (! empty($items))
            <div class="grid gap-3">
                @foreach ($items as $item)
                    <x-portal::accordion class="border border-zinc-200 rounded-xl bg-white overflow-hidden">
                        <x-slot:header class="p-5 font-semibold text-base text-zinc-900 hover:bg-zinc-50 cursor-pointer">
                            {{ $item['question'] ?? '' }}
                        </x-slot>

                        <x-slot:content class="p-5 pt-0 text-sm text-zinc-500 leading-relaxed">
                            {{ $item['answer'] ?? '' }}
                        </x-slot>
                    </x-portal::accordion>
                @endforeach
            </div>
        @endif
    </div>
</div>

