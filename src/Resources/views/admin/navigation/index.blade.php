@php
    $activeTab = $activeTab ?? request('tab', 'all');
@endphp

<x-admin::layouts>
    <x-slot:title>
        @if ($activeTab === 'header')
            @lang('portal::app.admin.navigation.header-title')
        @elseif ($activeTab === 'footer')
            @lang('portal::app.admin.navigation.footer-title')
        @else
            @lang('portal::app.admin.navigation.title')
        @endif
    </x-slot>

    {!! view_render_event('bagisto.admin.portal.navigation.before') !!}

    <x-admin::form :action="route('admin.portal.navigation.update', ['channel' => $currentChannel->code, 'locale' => $currentLocale->code])">
        <input type="hidden" name="channel" value="{{ $currentChannel->code }}" />
        <input type="hidden" name="locale" value="{{ $currentLocale->code }}" />
        <input type="hidden" name="theme_code" value="{{ $themeCode }}" />
        <input type="hidden" name="active_tab" value="{{ $activeTab }}" />

        <!-- Page Header -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div>
                <p class="text-xl font-bold text-gray-800 dark:text-white">
                    @if ($activeTab === 'header')
                        @lang('portal::app.admin.navigation.header-title')
                    @elseif ($activeTab === 'footer')
                        @lang('portal::app.admin.navigation.footer-title')
                    @else
                        @lang('portal::app.admin.navigation.title')
                    @endif
                </p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @lang('portal::app.admin.navigation.subtitle')
                </p>
            </div>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.portal.menus.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('portal::app.admin.navigation.back-btn')
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('portal::app.admin.navigation.save-btn')
                </button>
            </div>
        </div>

        <!-- Customization Tabs Navigation -->
        <div class="mt-4 flex items-center gap-2 border-b border-gray-200 pb-2 dark:border-gray-800">
            <a
                href="{{ route('admin.portal.navigation.index', ['channel' => $currentChannel->code, 'locale' => $currentLocale->code]) }}"
                class="rounded-md px-3 py-1.5 text-sm font-semibold transition-all {{ $activeTab === 'all' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
            >
                @lang('portal::app.admin.navigation.tabs.all')
            </a>

            <a
                href="{{ route('admin.portal.navigation.header', ['channel' => $currentChannel->code, 'locale' => $currentLocale->code]) }}"
                class="rounded-md px-3 py-1.5 text-sm font-semibold transition-all {{ $activeTab === 'header' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
            >
                @lang('portal::app.admin.navigation.tabs.header')
            </a>

            <a
                href="{{ route('admin.portal.navigation.footer', ['channel' => $currentChannel->code, 'locale' => $currentLocale->code]) }}"
                class="rounded-md px-3 py-1.5 text-sm font-semibold transition-all {{ $activeTab === 'footer' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}"
            >
                @lang('portal::app.admin.navigation.tabs.footer')
            </a>

            <a
                href="{{ route('admin.appearance.themes.index') }}"
                class="rounded-md px-3 py-1.5 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
            >
                @lang('portal::app.admin.navigation.tabs.themes')
            </a>
        </div>

        <!-- Channel and Locale Switcher Row (Standard Bagisto Admin Pattern) -->
        <div class="mt-7 flex items-center justify-between gap-4 max-md:flex-wrap">
            <div class="flex items-center gap-x-1">
                <!-- Channel Switcher -->
                <x-admin::dropdown :class="$channels->count() <= 1 ? 'hidden' : ''">
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="transparent-button px-1 py-1.5 hover:bg-gray-200 focus:bg-gray-200 dark:text-white dark:hover:bg-gray-800 dark:focus:bg-gray-800"
                        >
                            <span class="icon-store text-2xl"></span>

                            <span v-pre>{{ $currentChannel->name }}</span>

                            <input
                                type="hidden"
                                name="channel"
                                value="{{ $currentChannel->code }}"
                            />

                            <span class="icon-sort-down text-2xl"></span>
                        </button>
                    </x-slot>

                    <x-slot:content class="!p-0">
                        @foreach ($channels as $channel)
                            <a
                                href="?{{ Arr::query(['channel' => $channel->code, 'locale' => $channel->default_locale?->code ?? $currentLocale->code]) }}"
                                class="flex cursor-pointer gap-2.5 px-5 py-2 text-base hover:bg-gray-100 dark:text-white dark:hover:bg-gray-950 {{ $channel->id == $currentChannel->id ? 'bg-gray-100 font-bold dark:bg-gray-950' : '' }}"
                                v-pre
                            >
                                {{ $channel->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>

                <!-- Locale Switcher -->
                <x-admin::dropdown :class="$currentChannel->locales->count() <= 1 ? 'hidden' : ''">
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
                        @foreach ($currentChannel->locales->sortBy('name') as $locale)
                            <a
                                href="?{{ Arr::query(['channel' => $currentChannel->code, 'locale' => $locale->code]) }}"
                                class="flex gap-2.5 px-5 py-2 text-base cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-950 dark:text-white {{ $locale->code == $currentLocale->code ? 'bg-gray-100 dark:bg-gray-950' : '' }}"
                                v-pre
                            >
                                {{ $locale->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>

                <input type="hidden" name="theme_code" value="{{ $themeCode }}" />
            </div>
        </div>

        <!-- Main Body Grid -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Container: Slots -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <!-- Header Slots Section -->
                <x-admin::accordion :is-active="in_array($activeTab, ['all', 'header'])">
                    <x-slot:header class="p-3">
                        <div class="flex items-center gap-2">
                            <span class="icon-attribute text-xl text-blue-600"></span>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('portal::app.admin.navigation.header-slots')
                            </p>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-950 dark:text-gray-300">
                                {{ count($slots['header']) }}
                            </span>
                        </div>
                    </x-slot>

                    <x-slot:content class="p-4">
                        @if (empty($slots['header']))
                            <p class="py-3 text-center text-sm text-gray-500">
                                @lang('portal::app.admin.navigation.no-slots-found')
                            </p>
                        @else
                            @foreach ($slots['header'] as $slotId => $slotInfo)
                                @php
                                    $assignment = $assignments->get($slotId);
                                    $selectedMenuId = $assignment?->portal_menu_id;
                                    $slotStatus = $assignment ? (bool) $assignment->status : true;
                                    $slotLabel = Lang::has('portal::app.admin.navigation.slots.' . $slotId)
                                        ? trans('portal::app.admin.navigation.slots.' . $slotId)
                                        : $slotInfo['label'];
                                @endphp

                                <div class="box-shadow mb-4 rounded bg-white p-4 dark:bg-gray-900 last:mb-0">
                                    <div class="mb-4 flex items-center justify-between border-b pb-3 dark:border-gray-800">
                                        <div>
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                {{ $slotLabel }}
                                            </p>
                                            <span class="font-mono text-xs text-gray-500 dark:text-gray-400">
                                                #{{ $slotId }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <x-admin::form.control-group.label class="!mb-0 text-xs">
                                                @lang('portal::app.admin.navigation.status')
                                            </x-admin::form.control-group.label>

                                            <input
                                                type="hidden"
                                                name="slots[{{ $slotId }}][status]"
                                                value="0"
                                            />

                                            <x-admin::form.control-group.control
                                                type="switch"
                                                id="switch_{{ $slotId }}"
                                                name="slots[{{ $slotId }}][status]"
                                                value="1"
                                                :checked="$slotStatus"
                                            />
                                        </div>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="slots[{{ $slotId }}][location]"
                                        value="header"
                                    />

                                    <x-admin::form.control-group class="!mb-0">
                                        <x-admin::form.control-group.label>
                                            @lang('portal::app.admin.navigation.assigned-menu')

                                            <span
                                                class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                                v-pre
                                            >
                                                {{ $currentLocale->name }}
                                            </span>
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="select"
                                            id="slot_menu_{{ $slotId }}"
                                            name="slots[{{ $slotId }}][portal_menu_id]"
                                            :value="$selectedMenuId"
                                        >
                                            <option value="">
                                                @lang('portal::app.admin.navigation.select-menu')
                                            </option>

                                            @foreach ($menus as $menu)
                                                <option
                                                    value="{{ $menu->id }}"
                                                    {{ $selectedMenuId == $menu->id ? 'selected' : '' }}
                                                >
                                                    {{ $menu->name }} ({{ $menu->slug }})
                                                </option>
                                            @endforeach
                                        </x-admin::form.control-group.control>
                                    </x-admin::form.control-group>
                                </div>
                            @endforeach
                        @endif
                    </x-slot>
                </x-admin::accordion>

                <!-- Footer Slots Section -->
                <x-admin::accordion :is-active="in_array($activeTab, ['all', 'footer'])">
                    <x-slot:header class="p-3">
                        <div class="flex items-center gap-2">
                            <span class="icon-folder text-xl text-green-600"></span>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('portal::app.admin.navigation.footer-slots')
                            </p>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-950 dark:text-gray-300">
                                {{ count($slots['footer']) }}
                            </span>
                        </div>
                    </x-slot>

                    <x-slot:content class="p-4">
                        @if (empty($slots['footer']))
                            <p class="py-3 text-center text-sm text-gray-500">
                                @lang('portal::app.admin.navigation.no-slots-found')
                            </p>
                        @else
                            @foreach ($slots['footer'] as $slotId => $slotInfo)
                                @php
                                    $assignment = $assignments->get($slotId);
                                    $isCurrentLocale = $assignment && $assignment->locale_code === $currentLocale->code;
                                    $selectedMenuId = $assignment?->portal_menu_id;
                                    $slotTitle = $isCurrentLocale ? $assignment->title : ($assignment?->title ?? '');
                                    $slotStatus = $assignment ? (bool) $assignment->status : true;
                                    $slotLabel = Lang::has('portal::app.admin.navigation.slots.' . $slotId)
                                        ? trans('portal::app.admin.navigation.slots.' . $slotId)
                                        : $slotInfo['label'];
                                @endphp

                                <div class="box-shadow mb-4 rounded bg-white p-4 dark:bg-gray-900 last:mb-0">
                                    <div class="mb-4 flex items-center justify-between border-b pb-3 dark:border-gray-800">
                                        <div>
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                {{ $slotLabel }}
                                            </p>
                                            <span class="font-mono text-xs text-gray-500 dark:text-gray-400">
                                                #{{ $slotId }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <x-admin::form.control-group.label class="!mb-0 text-xs">
                                                @lang('portal::app.admin.navigation.status')
                                            </x-admin::form.control-group.label>

                                            <input
                                                type="hidden"
                                                name="slots[{{ $slotId }}][status]"
                                                value="0"
                                            />

                                            <x-admin::form.control-group.control
                                                type="switch"
                                                id="switch_{{ $slotId }}"
                                                name="slots[{{ $slotId }}][status]"
                                                value="1"
                                                :checked="$slotStatus"
                                            />
                                        </div>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="slots[{{ $slotId }}][location]"
                                        value="footer"
                                    />

                                    <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                                        <!-- Menu Selector -->
                                        <x-admin::form.control-group class="!mb-0">
                                            <x-admin::form.control-group.label>
                                                @lang('portal::app.admin.navigation.assigned-menu')

                                                <span
                                                    class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                                    v-pre
                                                >
                                                    {{ $currentLocale->name }}
                                                </span>
                                            </x-admin::form.control-group.label>

                                            <x-admin::form.control-group.control
                                                type="select"
                                                id="slot_menu_{{ $slotId }}"
                                                name="slots[{{ $slotId }}][portal_menu_id]"
                                                :value="$selectedMenuId"
                                            >
                                                <option value="">
                                                    @lang('portal::app.admin.navigation.select-menu')
                                                </option>

                                                @foreach ($menus as $menu)
                                                    <option
                                                        value="{{ $menu->id }}"
                                                        {{ $selectedMenuId == $menu->id ? 'selected' : '' }}
                                                    >
                                                        {{ $menu->name }} ({{ $menu->slug }})
                                                    </option>
                                                @endforeach
                                            </x-admin::form.control-group.control>
                                        </x-admin::form.control-group>

                                        <!-- Optional Title Override -->
                                        <x-admin::form.control-group class="!mb-0">
                                            <x-admin::form.control-group.label>
                                                @lang('portal::app.admin.navigation.slot-title')

                                                <span
                                                    class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                                    v-pre
                                                >
                                                    {{ $currentLocale->name }}
                                                </span>
                                            </x-admin::form.control-group.label>

                                            <x-admin::form.control-group.control
                                                type="text"
                                                id="slot_title_{{ $slotId }}"
                                                name="slots[{{ $slotId }}][title]"
                                                :value="$slotTitle"
                                                :placeholder="trans('portal::app.admin.navigation.slot-title')"
                                            />
                                        </x-admin::form.control-group>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </x-slot>
                </x-admin::accordion>

                <!-- Other Slots Section -->
                @if (! empty($slots['other']))
                    <x-admin::accordion :is-active="$activeTab === 'all'">
                        <x-slot:header class="p-3">
                            <div class="flex items-center gap-2">
                                <span class="icon-cms text-xl text-purple-600"></span>
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('portal::app.admin.navigation.other-slots')
                                </p>
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-950 dark:text-gray-300">
                                    {{ count($slots['other']) }}
                                </span>
                            </div>
                        </x-slot>

                        <x-slot:content class="p-4">
                            @foreach ($slots['other'] as $slotId => $slotInfo)
                                @php
                                    $assignment = $assignments->get($slotId);
                                    $selectedMenuId = $assignment?->portal_menu_id;
                                    $slotStatus = $assignment ? (bool) $assignment->status : true;
                                    $slotLabel = Lang::has('portal::app.admin.navigation.slots.' . $slotId)
                                        ? trans('portal::app.admin.navigation.slots.' . $slotId)
                                        : $slotInfo['label'];
                                @endphp

                                <div class="box-shadow mb-4 rounded bg-white p-4 dark:bg-gray-900 last:mb-0">
                                    <div class="mb-4 flex items-center justify-between border-b pb-3 dark:border-gray-800">
                                        <div>
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                {{ $slotLabel }}
                                            </p>
                                            <span class="font-mono text-xs text-gray-500 dark:text-gray-400">
                                                #{{ $slotId }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <x-admin::form.control-group.label class="!mb-0 text-xs">
                                                @lang('portal::app.admin.navigation.status')
                                            </x-admin::form.control-group.label>

                                            <input
                                                type="hidden"
                                                name="slots[{{ $slotId }}][status]"
                                                value="0"
                                            />

                                            <x-admin::form.control-group.control
                                                type="switch"
                                                id="switch_{{ $slotId }}"
                                                name="slots[{{ $slotId }}][status]"
                                                value="1"
                                                :checked="$slotStatus"
                                            />
                                        </div>
                                    </div>

                                    <input
                                        type="hidden"
                                        name="slots[{{ $slotId }}][location]"
                                        value="other"
                                    />

                                    <x-admin::form.control-group class="!mb-0">
                                        <x-admin::form.control-group.label>
                                            @lang('portal::app.admin.navigation.assigned-menu')

                                            <span
                                                class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                                v-pre
                                            >
                                                {{ $currentLocale->name }}
                                            </span>
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="select"
                                            id="slot_menu_{{ $slotId }}"
                                            name="slots[{{ $slotId }}][portal_menu_id]"
                                            :value="$selectedMenuId"
                                        >
                                            <option value="">
                                                @lang('portal::app.admin.navigation.select-menu')
                                            </option>

                                            @foreach ($menus as $menu)
                                                <option
                                                    value="{{ $menu->id }}"
                                                    {{ $selectedMenuId == $menu->id ? 'selected' : '' }}
                                                >
                                                    {{ $menu->name }} ({{ $menu->slug }})
                                                </option>
                                            @endforeach
                                        </x-admin::form.control-group.control>
                                    </x-admin::form.control-group>
                                </div>
                            @endforeach
                        </x-slot:content>
                    </x-admin::accordion>
                @endif
            </div>

            <!-- Right Container: Information & Guide -->
            <div class="flex w-[360px] max-w-full select-none flex-col gap-2">
                <!-- Scope Accordion -->
                <x-admin::accordion>
                    <x-slot:header class="p-3">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.navigation.general-info')
                        </p>
                    </x-slot>

                    <x-slot:content class="p-4 space-y-3">
                        <div class="flex items-center justify-between border-b pb-2 text-sm dark:border-gray-800">
                            <span class="text-gray-500">
                                @lang('portal::app.admin.navigation.channel')
                            </span>
                            <span class="font-semibold text-gray-800 dark:text-white">
                                {{ $currentChannel->name }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between border-b pb-2 text-sm dark:border-gray-800">
                            <span class="text-gray-500">
                                @lang('portal::app.admin.navigation.locale')
                            </span>
                            <span class="font-semibold text-gray-800 dark:text-white">
                                {{ $currentLocale->name }} ({{ $currentLocale->code }})
                            </span>
                        </div>

                        <div class="flex items-center justify-between border-b pb-2 text-sm dark:border-gray-800">
                            <span class="text-gray-500">
                                @lang('portal::app.admin.navigation.theme')
                            </span>
                            <span class="font-mono text-xs text-gray-700 dark:text-gray-300">
                                {{ $themeCode }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between border-b pb-2 text-sm dark:border-gray-800">
                            <span class="text-gray-500">
                                @lang('portal::app.admin.navigation.slots-count')
                            </span>
                            <span class="font-bold text-blue-600">
                                {{ count($slots['header']) + count($slots['footer']) + count($slots['other']) }}
                            </span>
                        </div>

                        <div class="pt-2">
                            <a
                                href="{{ route('admin.portal.menus.index') }}"
                                class="secondary-button flex w-full items-center justify-center gap-1.5 py-2 text-xs"
                            >
                                <span class="icon-menu text-base"></span>
                                <span>@lang('portal::app.admin.navigation.all-menus-btn') ({{ $menus->count() }})</span>
                            </a>
                        </div>
                    </x-slot:content>
                </x-admin::accordion>

                <!-- Developer Guide Accordion -->
                <x-admin::accordion>
                    <x-slot:header class="p-3">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.navigation.developer-hint-title')
                        </p>
                    </x-slot>

                    <x-slot:content class="p-4 space-y-3 text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                        <p>
                            @lang('portal::app.admin.navigation.developer-hint-desc')
                        </p>

                        <div class="rounded bg-gray-100 p-2.5 font-mono text-[11px] text-gray-800 dark:bg-gray-950 dark:text-gray-200" dir="ltr">
                            &lt;x-portal::menu-slot id="slot_id" /&gt;
                        </div>

                        <p class="text-[11px] text-gray-500">
                            @lang('portal::app.admin.navigation.developer-hint-note')
                        </p>
                    </x-slot:content>
                </x-admin::accordion>
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('bagisto.admin.portal.navigation.after') !!}
</x-admin::layouts>
