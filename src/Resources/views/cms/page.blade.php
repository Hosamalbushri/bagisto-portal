<x-portal::layouts>
    <x-slot:title>
        {{ $page->meta_title ?? $page->page_title }}
    </x-slot>

    @push('meta')
        <meta name="description" content="{{ $page->meta_description ?? '' }}" />
        <meta name="keywords" content="{{ $page->meta_keywords ?? '' }}" />
    @endpush

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-bold tracking-tight text-[var(--text-primary)] font-cairo sm:text-4xl mb-8">
            {{ $page->page_title }}
        </h1>

        <div class="prose dark:prose-invert max-w-none text-[var(--text-secondary)]">
            {!! $page->html_content !!}
        </div>
    </div>
</x-portal::layouts>

