@props(['options' => []])

@php
    $heading = $options['heading'] ?? '';
    $subheading = $options['subheading'] ?? '';
    $items = $options['items'] ?? [];
@endphp

<div class="portal-container mx-auto mt-20 max-md:mt-10" v-pre>
    @if ($heading || $subheading)
        <div class="text-center max-w-[700px] m-auto mb-12 max-md:mb-8">
            @if ($heading)
                <h2 class="font-cairo font-bold text-4xl max-md:text-3xl max-sm:text-xl text-[var(--text-primary)]">
                    {{ $heading }}
                </h2>
            @endif

            @if ($subheading)
                <p class="mt-3 text-lg max-sm:text-sm text-[var(--text-secondary)] leading-relaxed">
                    {{ $subheading }}
                </p>
            @endif
        </div>
    @endif

    @if (! empty($items))
        <div class="grid grid-cols-2 gap-6 max-md:grid-cols-1">
            @foreach ($items as $item)
                <x-portal::card :interactive="true" class="flex flex-col justify-between !p-8 max-md:!p-6">
                    <p class="text-base text-[var(--text-secondary)] italic leading-relaxed mb-6">
                        "{{ $item['content'] ?? '' }}"
                    </p>

                    <div>
                        <p class="font-cairo font-semibold text-base text-[var(--text-primary)]">
                            {{ $item['name'] ?? '' }}
                        </p>

                        <p class="text-xs text-[var(--text-muted)] mt-0.5">
                            {{ $item['role'] ?? '' }}
                        </p>
                    </div>
                </x-portal::card>
            @endforeach
        </div>
    @endif
</div>

