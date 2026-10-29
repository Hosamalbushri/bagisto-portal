@php
    $channel = core()->getCurrentChannel();
@endphp

@push ('meta')
    <meta name="title" content="{{ $channel->home_seo['meta_title'] ?? config('app.name') }}" />
    <meta name="description" content="{{ $channel->home_seo['meta_description'] ?? '' }}" />
    <meta name="keywords" content="{{ $channel->home_seo['meta_keywords'] ?? '' }}" />
@endpush

<x-portal::layouts>
    <x-slot:title>
        {{ $channel->home_seo['meta_title'] ?? config('app.name') }}
    </x-slot>

    @if ($sections->isEmpty())
        <div class="py-24 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-5xl">
                @lang('portal::app.home.welcome-title')
            </h1>

            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                @lang('portal::app.home.welcome-description')
            </p>

            <div class="mt-8">
                <a
                    href="{{ route('admin.appearance.themes.index') }}"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-semibold rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700"
                >
                    @lang('portal::app.home.customize-appearance') &rarr;
                </a>
            </div>
        </div>
    @endif

    @foreach ($sections as $section)
        @php ($data = $section->options) @endphp

        @php ($marks = ($preview ?? false) && ! $section->getTypeInstance()?->rendersInLayout())

        @if ($marks)
            <div
                data-section-id="{{ $section->id }}"
                data-section-name="{{ $section->name }}"
            >
        @endif

        @switch ($section->type)
            @case (\Webkul\Theme\Enums\SectionTypeEnum::IMAGE_CAROUSEL->value)
                <x-portal::carousel
                    :options="$section->getTypeInstance()?->sanitize((array) $data) ?? $data"
                />
                @break

            @case (\Webkul\Theme\Enums\SectionTypeEnum::STATIC_CONTENT->value)
                @if (! empty($data['css']))
                    @push ('styles')
                        <style>
                            {!! $data['css'] !!}
                        </style>
                    @endpush
                @endif

                @if (! empty($data['html']))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                        {!! $data['html'] !!}
                    </div>
                @endif
                @break

            @case (\Webkul\Theme\Enums\SectionTypeEnum::SERVICES_CONTENT->value)
                <x-portal::layouts.services />
                @break

            @case (\Webkul\Theme\Enums\SectionTypeEnum::FOOTER_LINKS->value)
                <x-portal::layouts.footer />
                @break
        @endswitch

        @if ($marks)
            </div>
        @endif
    @endforeach

    @if ($preview ?? false)
        @include('portal::home.preview-bridge')
    @endif
</x-portal::layouts>

