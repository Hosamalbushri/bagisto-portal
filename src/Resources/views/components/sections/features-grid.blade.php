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

<div class="container mt-20 max-1180:px-5 max-md:mt-10 max-md:px-4" v-pre>
    @if ($heading || $subheading)
        <div class="text-center max-w-[700px] m-auto mb-12 max-md:mb-8">
            @if ($heading)
                <h2 class="font-dmserif text-4xl max-md:text-3xl max-sm:text-xl text-zinc-900 dark:text-white">
                    {{ $heading }}
                </h2>
            @endif

            @if ($subheading)
                <p class="mt-3 text-lg max-sm:text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
                    {{ $subheading }}
                </p>
            @endif
        </div>
    @endif

    @if (! empty($items))
        <div class="grid {{ $colClass }} gap-6">
            @foreach ($items as $item)
                <div class="flex flex-col rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-8 transition-all hover:border-zinc-400 dark:hover:border-zinc-700 hover:shadow-sm max-md:p-6">
                    <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800 text-2xl text-zinc-800 dark:text-zinc-200">
                        <span class="{{ $item['icon'] ?? 'icon-star' }}"></span>
                    </div>

                    <h3 class="font-dmserif text-xl max-sm:text-lg text-zinc-900 dark:text-white mb-2.5">
                        {{ $item['title'] ?? '' }}
                    </h3>

                    <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
                        {{ $item['description'] ?? '' }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif
</div>

