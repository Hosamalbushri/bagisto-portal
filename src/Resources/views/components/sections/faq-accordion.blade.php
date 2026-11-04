@props(['options' => []])

@php
    $heading = $options['heading'] ?? '';
    $subheading = $options['subheading'] ?? '';
    $items = $options['items'] ?? [];
@endphp

<div class="portal-container mx-auto mt-20 max-md:mt-10">
    <div class="m-auto max-w-[850px]">
        @if ($heading || $subheading)
            <div class="text-center mb-10 max-md:mb-6" v-pre>
                @if ($heading)
                    <h2 class="font-cairo font-bold text-4xl max-md:text-3xl max-sm:text-xl text-[var(--text-primary)]">
                        {{ $heading }}
                    </h2>
                @endif

                @if ($subheading)
                    <p class="mt-3 text-lg max-sm:text-sm text-[var(--text-secondary)]">
                        {{ $subheading }}
                    </p>
                @endif
            </div>
        @endif

        @if (! empty($items))
            <div class="grid gap-3">
                @foreach ($items as $item)
                    <x-portal::accordion
                        class="portal-card border border-[var(--border-subtle)] bg-[var(--bg-surface)] overflow-hidden shadow-sm"
                        style="border-radius: var(--card-radius, 0.75rem);"
                    >
                        <x-slot:header class="p-5 font-semibold text-base text-[var(--text-primary)] hover:bg-[var(--bg-surface-muted)] cursor-pointer transition-colors">
                            {{ $item['question'] ?? '' }}
                        </x-slot>

                        <x-slot:content class="p-5 pt-0 text-sm text-[var(--text-secondary)] leading-relaxed">
                            {{ $item['answer'] ?? '' }}
                        </x-slot>
                    </x-portal::accordion>
                @endforeach
            </div>
        @endif
    </div>
</div>

