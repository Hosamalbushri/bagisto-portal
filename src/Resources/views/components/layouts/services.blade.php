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
                <x-portal::card :interactive="true" class="flex items-start gap-4 !p-6 group">
                    <span
                        class="{{ $service['service_icon'] ?? 'icon-cms' }} flex items-center justify-center w-12 h-12 rounded-xl bg-[var(--brand-primary-light)] text-[var(--brand-primary)] text-2xl flex-shrink-0 group-hover:scale-105 transition-transform"
                        role="presentation"
                    >
                    </span>

                    <div>
                        <h3 class="text-base font-bold text-[var(--text-primary)] font-cairo">
                            {{ $service['title'] ?? '' }}
                        </h3>

                        <p class="mt-2 text-sm text-[var(--text-secondary)] leading-relaxed">
                            {{ $service['description'] ?? '' }}
                        </p>
                    </div>
                </x-portal::card>
            @endforeach
        </div>
    </div>
@endforeach

{!! view_render_event('bagisto.shop.layout.features.after') !!}

