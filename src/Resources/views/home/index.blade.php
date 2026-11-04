@php
    $channel = core()->getCurrentChannel();
@endphp

@push ('meta')
    <x-portal::seo
        :title="$channel->home_seo['meta_title'] ?? config('app.name')"
        :description="$channel->home_seo['meta_description'] ?? ''"
        :keywords="$channel->home_seo['meta_keywords'] ?? ''"
        type="website"
    />
@endpush

<x-portal::layouts>
    <x-slot:title>
        {{ $channel->home_seo['meta_title'] ?? config('app.name') }}
    </x-slot>

    @if ($sections->isEmpty())
        <div class="portal-container mx-auto py-24 text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-[var(--text-primary)] font-cairo sm:text-5xl">
                @lang('portal::app.home.welcome-title')
            </h1>

            <p class="mt-4 text-lg text-[var(--text-secondary)]">
                @lang('portal::app.home.welcome-description')
            </p>

            <div class="mt-8">
                <a
                    href="{{ route('admin.portal.themes.index') }}"
                    class="btn-primary !px-6 !py-3 text-sm shadow-sm"
                >
                    @lang('portal::app.home.customize-appearance') &rarr;
                </a>
            </div>
        </div>
    @endif

    @foreach ($sections as $section)
        @php
            $data = $section->options;
            $marks = ($preview ?? false) && ! $section->getTypeInstance()?->rendersInLayout();
        @endphp

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

