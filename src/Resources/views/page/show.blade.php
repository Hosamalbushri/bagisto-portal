<x-portal::layouts
    :title="$page->meta_title ?: $page->title"
    :meta-description="$page->meta_description"
    :meta-keywords="$page->meta_keywords"
>
    <main class="min-h-screen">
        @if ($sections->isNotEmpty())
            @foreach ($sections as $section)
                @includeIf("portal::sections.{$section->type}", [
                    'options' => $section->options ?? [],
                    'section' => $section,
                ])
            @endforeach
        @else
            <div class="py-24 text-center">
                <div class="container mx-auto px-4 max-w-xl">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $page->title }}</h1>
                    <p class="text-gray-500">لا توجد أقسام منشورة في هذه الصفحة حتى الآن.</p>
                </div>
            </div>
        @endif
    </main>

    @if (isset($preview) && $preview)
        @include('portal::home.preview-bridge')
    @endif
</x-portal::layouts>
