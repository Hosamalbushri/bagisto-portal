@props(['options' => []])

@php
    $heading = $options['heading'] ?? '';
    $subheading = $options['subheading'] ?? '';
    $items = $options['items'] ?? [];
    $columns = (int) ($options['columns'] ?? 3);
    $colClass = match ($columns) {
        2 => 'grid-cols-2 max-md:grid-cols-1',
        4 => 'grid-cols-4 max-lg:grid-cols-2 max-md:grid-cols-1',
        default => 'grid-cols-3 max-lg:grid-cols-2 max-md:grid-cols-1',
    };
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
        <div class="grid {{ $colClass }} gap-6">
            @foreach ($items as $item)
                <x-portal::card :interactive="true" class="flex flex-col !p-8 max-md:!p-6 group">
                    <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-[var(--btn-radius,0.75rem)] bg-[var(--brand-primary-light)] text-2xl text-[var(--brand-primary)] group-hover:scale-105 transition-transform">
                        <span class="{{ $item['icon'] ?? 'icon-star' }}"></span>
                    </div>

                    <h3 class="font-cairo font-bold text-xl max-sm:text-lg text-[var(--text-primary)] mb-2.5">
                        {{ $item['title'] ?? '' }}
                    </h3>

                    <p class="text-sm text-[var(--text-secondary)] leading-relaxed">
                        {{ $item['description'] ?? '' }}
                    </p>
                </x-portal::card>
            @endforeach
        </div>
    @endif
</div>

