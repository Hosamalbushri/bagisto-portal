@php
    $channel = core()->getCurrentChannel();
    $metaTitle = $page->meta_title ?: ($page->title ?: ($page->is_home_page ? ($channel->home_seo['meta_title'] ?? config('app.name')) : config('app.name')));
    $metaDesc = $page->meta_description ?? ($page->is_home_page ? ($channel->home_seo['meta_description'] ?? '') : '');
    $metaKeywords = $page->meta_keywords ?? ($page->is_home_page ? ($channel->home_seo['meta_keywords'] ?? '') : '');
@endphp

<!-- SEO Meta Content -->
@push ('meta')
    <meta
        name="title"
        content="{{ $metaTitle }}"
    />

    <meta
        name="description"
        content="{{ $metaDesc }}"
    />

    <meta
        name="keywords"
        content="{{ $metaKeywords }}"
    />
@endpush

<x-portal::layouts :has-feature="false">
    <!-- Page Title -->
    <x-slot:title>
        {{ $metaTitle }}
    </x-slot>

    <!-- Loop over the storefront sections -->
    @if ($sections->isNotEmpty())
        @foreach ($sections as $section)
            @php
                $data = $section->options ?? [];
                event('portal.section.rendering', [$section, &$data]);
                $marks = ($preview ?? false) && ! $section->getTypeInstance()?->rendersInLayout();
            @endphp

            @if ($marks)
                <div
                    data-section-id="{{ $section->id }}"
                    data-section-name="{{ $section->name }}"
                >
            @endif

            @switch ($section->type)
                @case ('hero_scaffold')
                    <x-portal::sections.hero-scaffold :options="$data" />
                    @break

                @case ('bento_grid')
                    <x-portal::sections.bento-grid :options="$data" />
                    @break

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
                    @php
                        $typeInstance = $section->getTypeInstance();
                        $customView = $typeInstance?->getView();
                    @endphp

                    @if ($customView && view()->exists($customView))
                        @include($customView, ['options' => $data, 'section' => $section])
                    @elseif (view()->exists("portal::sections.{$section->type}"))
                        @include("portal::sections.{$section->type}", ['options' => $data, 'section' => $section])
                    @elseif (view()->exists("portal.sections.{$section->type}"))
                        @include("portal.sections.{$section->type}", ['options' => $data, 'section' => $section])
                    @elseif (view()->exists("sections.{$section->type}"))
                        @include("sections.{$section->type}", ['options' => $data, 'section' => $section])
                    @endif
            @endswitch

            @if ($marks)
                </div>
            @endif
        @endforeach
    @else
        <div class="py-24 text-center">
            <div class="container m-auto max-w-xl max-1180:px-5 max-md:px-4">
                <h1 class="font-cairo font-bold text-4xl text-[var(--text-primary)] mb-4 max-md:text-3xl max-sm:text-xl">
                    {{ $page->title }}
                </h1>
                <p class="text-[var(--text-secondary)] text-base">
                    @lang('portal::app.page.no-sections')
                </p>
            </div>
        </div>
    @endif

    @if ($preview ?? false)
        @include('portal::home.preview-bridge')
    @endif
</x-portal::layouts>
