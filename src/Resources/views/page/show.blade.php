@php
    $channel = core()->getCurrentChannel();
@endphp

<!-- SEO Meta Content -->
@push ('meta')
    <meta
        name="title"
        content="{{ $page->meta_title ?: $page->title }}"
    />

    <meta
        name="description"
        content="{{ $page->meta_description ?? '' }}"
    />

    <meta
        name="keywords"
        content="{{ $page->meta_keywords ?? '' }}"
    />
@endpush

<x-portal::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{ $page->meta_title ?: $page->title }}
    </x-slot>

    <!-- Loop over the storefront sections -->
    @if ($sections->isNotEmpty())
        @foreach ($sections as $section)
            @php ($data = $section->options ?? []) @endphp

            {{-- The layout marks the types it draws on every page, so this page marks the rest. --}}
            @php ($marks = ($preview ?? false) && ! $section->getTypeInstance()?->rendersInLayout())

            @if ($marks)
                <div
                    data-section-id="{{ $section->id }}"
                    data-section-name="{{ $section->name }}"
                >
            @endif

            @switch ($section->type)
                @case ('hero_slider')
                    <x-portal::sections.hero-slider :options="$data" />
                    @break

                @case ('features_grid')
                    <x-portal::sections.features-grid :options="$data" />
                    @break

                @case ('faq_accordion')
                    <x-portal::sections.faq-accordion :options="$data" />
                    @break

                @case ('cta_banner')
                    <x-portal::sections.cta-banner :options="$data" />
                    @break

                @case ('testimonials')
                    <x-portal::sections.testimonials :options="$data" />
                    @break

                @case ('rich_content')
                    <x-portal::sections.rich-content :options="$data" />
                    @break

                @case ('contact_section')
                    <x-portal::sections.contact-section :options="$data" />
                    @break

                @case (\Webkul\Theme\Enums\SectionTypeEnum::IMAGE_CAROUSEL->value)
                    <x-portal::carousel :options="$data" />
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
                        <div class="container my-12 max-1180:px-5 max-md:px-4" v-pre>
                            {!! $data['html'] !!}
                        </div>
                    @endif
                    @break

                @case (\Webkul\Theme\Enums\SectionTypeEnum::SERVICES_CONTENT->value)
                    <x-portal::layouts.services />
                    @break

                @default
                    @includeIf("portal::sections.{$section->type}", [
                        'options' => $data,
                        'section' => $section,
                    ])
            @endswitch

            @if ($marks)
                </div>
            @endif
        @endforeach
    @else
        <div class="py-24 text-center">
            <div class="container m-auto max-w-xl max-1180:px-5 max-md:px-4">
                <h1 class="font-dmserif text-4xl text-zinc-900 mb-4 max-md:text-3xl max-sm:text-xl">
                    {{ $page->title }}
                </h1>
                <p class="text-zinc-500 text-base">
                    @lang('portal::app.page.no-sections')
                </p>
            </div>
        </div>
    @endif

    @if ($preview ?? false)
        @include('portal::home.preview-bridge')
    @endif
</x-portal::layouts>
