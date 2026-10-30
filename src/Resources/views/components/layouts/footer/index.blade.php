{!! view_render_event('bagisto.shop.layout.footer.before') !!}

<footer class="border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand Column -->
            <div class="space-y-4">
                <a href="{{ route('shop.home.index') }}" class="flex items-center gap-2">
                    @if ($logo = core()->getCurrentChannel()->logo_url)
                        <img src="{{ $logo }}" class="h-8 w-auto" alt="{{ config('app.name') }}" />
                    @else
                        <span class="text-xl font-bold text-blue-600 dark:text-blue-400">
                            {{ core()->getCurrentChannel()->name ?? config('app.name') }}
                        </span>
                    @endif
                </a>

                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                    {{ core()->getCurrentChannel()->description ?? trans('portal::app.home.hero.subtitle') }}
                </p>
            </div>

            <!-- Footer Column 1 Slot -->
            <div>
                <x-portal::menu-slot
                    id="footer_column_1"
                    location="footer"
                    direction="column"
                    class="space-y-2.5 text-sm"
                />
            </div>

            <!-- Footer Column 2 Slot -->
            <div>
                <x-portal::menu-slot
                    id="footer_column_2"
                    location="footer"
                    direction="column"
                    class="space-y-2.5 text-sm"
                />
            </div>

            <!-- Footer Column 3 Slot -->
            <div>
                <x-portal::menu-slot
                    id="footer_column_3"
                    location="footer"
                    direction="column"
                    class="space-y-2.5 text-sm"
                />
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="border-t border-gray-100 dark:border-gray-800/60 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500 dark:text-gray-400">
            <p>&copy; {{ date('Y') }} {{ core()->getCurrentChannel()->name ?? config('app.name') }}. @lang('portal::app.footer.all-rights-reserved')</p>

            <x-portal::menu-slot
                id="footer_bottom"
                location="footer"
                class="flex items-center gap-6"
            />
        </div>
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
