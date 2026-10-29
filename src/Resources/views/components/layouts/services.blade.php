{!! view_render_event('bagisto.shop.layout.features.before') !!}

@inject('sectionRepository', 'Webkul\Theme\Repositories\SectionRepository')

@php
    $channel = core()->getCurrentChannel();

    $sections = $sectionRepository->findAllOfType(
        \Webkul\Theme\Enums\SectionTypeEnum::SERVICES_CONTENT->value,
        $channel->id,
        $channel->theme ?? config('themes.shop-default', 'default'),
        app()->getLocale()
    );
@endphp

@foreach ($sections as $section)
    @continue (empty($section->options['services']))

    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-16"
        v-pre
        @if ($sectionRepository->isPreviewing())
            data-section-id="{{ $section->id }}"
            data-section-name="{{ $section->name }}"
        @endif
    >
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach ($section->options['services'] as $service)
                <div class="flex items-start gap-4 p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm">
                    <span
                        class="{{ $service['service_icon'] ?? 'icon-cms' }} flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 text-2xl flex-shrink-0"
                        role="presentation"
                    >
                    </span>

                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">
                            {{ $service['title'] ?? '' }}
                        </h3>

                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                            {{ $service['description'] ?? '' }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

{!! view_render_event('bagisto.shop.layout.features.after') !!}

