@props(['currentLocale'])

<div class="mt-7 flex items-center justify-between gap-4 max-md:flex-wrap">
    <div class="flex items-center gap-x-1">
        <x-admin::dropdown
            position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'left' : 'right' }}"
            :class="core()->getAllLocales()->count() <= 1 ? 'hidden' : ''"
        >
            <x-slot:toggle>
                <button
                    type="button"
                    class="transparent-button px-1 py-1.5 hover:bg-gray-200 focus:bg-gray-200 dark:text-white dark:hover:bg-gray-800 dark:focus:bg-gray-800"
                >
                    <span class="icon-language text-2xl"></span>
                    <span v-pre>{{ $currentLocale->name }}</span>
                    <input
                        type="hidden"
                        name="locale"
                        value="{{ $currentLocale->code }}"
                    />
                    <span class="icon-sort-down text-2xl"></span>
                </button>
            </x-slot>

            <x-slot:content class="!p-0">
                @foreach (core()->getAllLocales() as $locale)
                    <a
                        href="?{{ Arr::query(['locale' => $locale->code]) }}"
                        class="flex gap-2.5 px-5 py-2 text-base cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-950 dark:text-white {{ $locale->code == $currentLocale->code ? 'bg-gray-100 dark:bg-gray-950' : ''}}"
                        v-pre
                    >
                        {{ $locale->name }}
                    </a>
                @endforeach
            </x-slot:content>
        </x-admin::dropdown>
    </div>
</div>
