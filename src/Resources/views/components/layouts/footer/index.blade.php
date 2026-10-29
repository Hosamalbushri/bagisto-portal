{!! view_render_event('bagisto.shop.layout.footer.before') !!}

@inject('sectionRepository', 'Webkul\Theme\Repositories\SectionRepository')

@php
    $channel = core()->getCurrentChannel();

    $section = $sectionRepository->findOneOfType(
        \Webkul\Theme\Enums\SectionTypeEnum::FOOTER_LINKS->value,
        $channel->id,
        $channel->theme ?? config('themes.shop-default', 'default'),
        app()->getLocale()
    );

    $columns = collect($section?->getTypeInstance()?->sanitize((array) $section->options) ?? [])
        ->map(fn ($links) => array_filter((array) $links, fn ($link) => filled($link['title'] ?? null)))
        ->filter();
@endphp

<footer
    class="border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 mt-20"
    @if ($section && $sectionRepository->isPreviewing())
        data-section-id="{{ $section->id }}"
        data-section-name="{{ $section->name }}"
    @endif
>
    @if ($columns->isNotEmpty())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Desktop Columns View -->
            <div class="hidden md:grid grid-cols-2 md:grid-cols-4 gap-8" v-pre>
                @foreach ($columns as $column)
                    <div>
                        <ul class="space-y-3">
                            @foreach ($column as $link)
                                <li>
                                    <a
                                        href="{{ $link['url'] ?? '#' }}"
                                        class="text-sm text-gray-600 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 transition-colors"
                                    >
                                        {{ $link['title'] ?? '' }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <!-- Mobile Accordion View -->
            <div class="md:hidden">
                <x-portal::accordion :is-active="false">
                    <x-slot:header class="font-medium text-sm text-gray-700 dark:text-gray-300">
                        @lang('portal::app.home.nav.quick-links')
                    </x-slot>

                    <x-slot:content class="grid grid-cols-2 gap-4 pt-4">
                        @foreach ($columns as $column)
                            <ul class="space-y-2">
                                @foreach ($column as $link)
                                    <li>
                                        <a
                                            href="{{ $link['url'] ?? '#' }}"
                                            class="text-xs text-gray-600 dark:text-gray-400 hover:text-blue-600 transition-colors"
                                        >
                                            {{ $link['title'] ?? '' }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </x-slot>
                </x-portal::accordion>
            </div>
        </div>
    @endif

    <div class="border-t border-gray-100 dark:border-gray-800/60 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
            <p>&copy; {{ date('Y') }} {{ core()->getCurrentChannel()->name ?? config('app.name') }}. @lang('portal::app.footer.all-rights-reserved')</p>

            <div class="flex items-center gap-6">
                <a href="{{ route('shop.home.index') }}" class="hover:underline">@lang('portal::app.home.nav.home')</a>
                <a href="{{ route('shop.home.contact_us') }}" class="hover:underline">@lang('portal::app.home.nav.contact-us')</a>
            </div>
        </div>
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
