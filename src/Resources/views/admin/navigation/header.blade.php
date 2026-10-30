<x-admin::layouts>
    <x-slot:title>
        @lang('portal::app.admin.navigation.header-title')
    </x-slot>

    {!! view_render_event('bagisto.admin.portal.navigation.header.before') !!}

    <x-admin::form :action="route('admin.portal.navigation.header.update', ['channel' => $currentChannel->code, 'locale' => $currentLocale->code])">
        <input type="hidden" name="channel" value="{{ $currentChannel->code }}" />
        <input type="hidden" name="locale" value="{{ $currentLocale->code }}" />
        <input type="hidden" name="theme_code" value="{{ $themeCode }}" />

        <!-- Page Header -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('portal::app.admin.navigation.header-title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.portal.navigation.footer', ['channel' => $currentChannel->code, 'locale' => $currentLocale->code]) }}"
                    class="secondary-button"
                >
                    <span class="icon-arrow-down text-lg"></span>
                    @lang('portal::app.admin.navigation.switch-to-footer')
                </a>

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

        <!-- Channel and Locale Switcher Row -->
        <div class="mt-6 flex items-center justify-between gap-4 max-md:flex-wrap">
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

                    <x-slot:menu class="!p-0">
                        @foreach ($channels as $channel)
                            <a
                                href="?{{ Arr::query(['channel' => $channel->code, 'locale' => $currentLocale->code]) }}"
                                class="flex cursor-pointer gap-2.5 px-5 py-2 text-base hover:bg-gray-100 dark:text-white dark:hover:bg-gray-950"
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

                    <x-slot:menu class="!p-0">
                        @foreach ($currentChannel->locales as $locale)
                            <a
                                href="?{{ Arr::query(['channel' => $currentChannel->code, 'locale' => $locale->code]) }}"
                                class="flex cursor-pointer gap-2.5 px-5 py-2 text-base hover:bg-gray-100 dark:text-white dark:hover:bg-gray-950"
                            >
                                {{ $locale->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>
            </div>
        </div>

        <!-- Main Body Grid -->
        <div class="mt-4 flex gap-4 max-xl:flex-wrap">
            <!-- Left Container: Accordion Sections (Closed by default) -->
            <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
                <!-- 1. Header Navigation Slots Accordion (Placed First) -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header class="p-4">
                        <div class="flex items-center gap-2">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('portal::app.admin.navigation.header-slots')
                            </p>
                            <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                {{ count($slots) }}
                            </span>
                        </div>
                    </x-slot>

                    <x-slot:content class="p-5 pt-0 space-y-4">
                        @forelse ($slots as $slotId => $slotInfo)
                            @php
                                $assignment = $assignments[$slotId] ?? null;
                                $selectedMenuId = old("slots.{$slotId}.portal_menu_id", $assignment['portal_menu_id'] ?? '');
                                $slotStatus = old("slots.{$slotId}.status", $assignment['status'] ?? true);
                                $slotLabel = Lang::has('portal::app.admin.navigation.slots.' . $slotId)
                                    ? trans('portal::app.admin.navigation.slots.' . $slotId)
                                    : $slotInfo['label'];
                            @endphp

                            <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 transition-colors hover:border-gray-300 dark:border-gray-800 dark:bg-gray-950/40 dark:hover:border-gray-700">
                                <div class="mb-4 flex items-center justify-between border-b border-gray-200 pb-3 dark:border-gray-800">
                                    <div class="flex items-center gap-2.5">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                            {{ $slotLabel }}
                                        </p>
                                        <span class="rounded bg-white px-2 py-0.5 font-mono text-[11px] text-gray-600 shadow-sm dark:bg-gray-900 dark:text-gray-300">
                                            #{{ $slotId }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500">@lang('portal::app.admin.navigation.status')</span>

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
                                            class="rounded border border-gray-200 bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
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
                        @empty
                            <div class="py-8 text-center text-sm text-gray-500">
                                @lang('portal::app.admin.navigation.no-slots-found')
                            </div>
                        @endforelse
                    </x-slot:content>
                </x-admin::accordion>

                <!-- 2. Announcement Bar Accordion -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header class="p-4">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.navigation.announcement.title')
                        </p>
                    </x-slot>

                    <x-slot:content class="p-5 pt-0 space-y-4">
                        <div class="flex items-center justify-between border-b pb-3 dark:border-gray-800">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                @lang('portal::app.admin.navigation.announcement.status')
                            </span>

                            <input type="hidden" name="header_settings[announcement][status]" value="0" />
                            <x-admin::form.control-group.control
                                type="switch"
                                id="header_announcement_status"
                                name="header_settings[announcement][status]"
                                value="1"
                                :checked="(bool) data_get($headerSettings, 'announcement.status', false)"
                            />
                        </div>

                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('portal::app.admin.navigation.announcement.text')
                                <span class="rounded border border-gray-200 bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" v-pre>
                                    {{ $currentLocale->name }}
                                </span>
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="header_announcement_text"
                                name="header_settings[announcement][text]"
                                :value="data_get($headerSettings, 'announcement.text', '')"
                                :placeholder="trans('portal::app.admin.navigation.announcement.text-placeholder')"
                            />
                        </x-admin::form.control-group>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <x-admin::form.control-group class="md:col-span-2 !mb-0">
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.navigation.announcement.link')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="header_announcement_link"
                                    name="header_settings[announcement][link]"
                                    :value="data_get($headerSettings, 'announcement.link', '')"
                                    :placeholder="trans('portal::app.admin.navigation.announcement.link-placeholder')"
                                />
                            </x-admin::form.control-group>

                            <div class="grid grid-cols-2 gap-3">
                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label class="text-xs">
                                        @lang('portal::app.admin.navigation.announcement.bg-color')
                                    </x-admin::form.control-group.label>

                                    <div class="flex items-center gap-2">
                                        <input
                                            type="color"
                                            name="header_settings[announcement][bg_color]"
                                            id="header_announcement_bg_color"
                                            value="{{ data_get($headerSettings, 'announcement.bg_color', '#1e293b') }}"
                                            class="h-9 w-full cursor-pointer rounded border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800"
                                        />
                                    </div>
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label class="text-xs">
                                        @lang('portal::app.admin.navigation.announcement.text-color')
                                    </x-admin::form.control-group.label>

                                    <div class="flex items-center gap-2">
                                        <input
                                            type="color"
                                            name="header_settings[announcement][text_color]"
                                            id="header_announcement_text_color"
                                            value="{{ data_get($headerSettings, 'announcement.text_color', '#ffffff') }}"
                                            class="h-9 w-full cursor-pointer rounded border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-800"
                                        />
                                    </div>
                                </x-admin::form.control-group>
                            </div>
                        </div>
                    </x-slot>
                </x-admin::accordion>

                <!-- 3. Header Controls Accordion -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header class="p-4">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.navigation.header-controls.title')
                        </p>
                    </x-slot>

                    <x-slot:content class="p-5 pt-0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Sticky Header -->
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 transition-colors hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700">
                                <div class="pe-4">
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">
                                        @lang('portal::app.admin.navigation.header-controls.sticky')
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                        @lang('portal::app.admin.navigation.header-controls.sticky-desc')
                                    </p>
                                </div>

                                <input type="hidden" name="header_settings[controls][sticky]" value="0" />
                                <x-admin::form.control-group.control
                                    type="switch"
                                    id="header_control_sticky"
                                    name="header_settings[controls][sticky]"
                                    value="1"
                                    :checked="(bool) data_get($headerSettings, 'controls.sticky', true)"
                                />
                            </div>

                            <!-- Dark Mode Switcher -->
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 transition-colors hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700">
                                <div class="pe-4">
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">
                                        @lang('portal::app.admin.navigation.header-controls.show-dark-mode')
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                        @lang('portal::app.admin.navigation.header-controls.show-dark-mode-desc')
                                    </p>
                                </div>

                                <input type="hidden" name="header_settings[controls][show_dark_mode]" value="0" />
                                <x-admin::form.control-group.control
                                    type="switch"
                                    id="header_control_dark_mode"
                                    name="header_settings[controls][show_dark_mode]"
                                    value="1"
                                    :checked="(bool) data_get($headerSettings, 'controls.show_dark_mode', true)"
                                />
                            </div>

                            <!-- Locales Switcher -->
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 transition-colors hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700">
                                <div class="pe-4">
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">
                                        @lang('portal::app.admin.navigation.header-controls.show-locales')
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                        @lang('portal::app.admin.navigation.header-controls.show-locales-desc')
                                    </p>
                                </div>

                                <input type="hidden" name="header_settings[controls][show_locales]" value="0" />
                                <x-admin::form.control-group.control
                                    type="switch"
                                    id="header_control_locales"
                                    name="header_settings[controls][show_locales]"
                                    value="1"
                                    :checked="(bool) data_get($headerSettings, 'controls.show_locales', true)"
                                />
                            </div>

                            <!-- Auth Links -->
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 transition-colors hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700">
                                <div class="pe-4">
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">
                                        @lang('portal::app.admin.navigation.header-controls.show-auth')
                                    </p>
                                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                        @lang('portal::app.admin.navigation.header-controls.show-auth-desc')
                                    </p>
                                </div>

                                <input type="hidden" name="header_settings[controls][show_auth]" value="0" />
                                <x-admin::form.control-group.control
                                    type="switch"
                                    id="header_control_auth"
                                    name="header_settings[controls][show_auth]"
                                    value="1"
                                    :checked="(bool) data_get($headerSettings, 'controls.show_auth', true)"
                                />
                            </div>
                        </div>
                    </x-slot>
                </x-admin::accordion>
            </div>

            <!-- Right Container: Scope Info Card (Standard Card, NOT Accordion) -->
            <div class="flex w-[340px] max-w-full select-none flex-col gap-4">
                <div class="box-shadow rounded bg-white p-5 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('portal::app.admin.navigation.general-info')
                    </p>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b pb-2.5 text-sm dark:border-gray-800">
                            <span class="text-xs text-gray-500">
                                @lang('portal::app.admin.navigation.channel')
                            </span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $currentChannel->name }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between border-b pb-2.5 text-sm dark:border-gray-800">
                            <span class="text-xs text-gray-500">
                                @lang('portal::app.admin.navigation.locale')
                            </span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $currentLocale->name }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between border-b pb-2.5 text-sm dark:border-gray-800">
                            <span class="text-xs text-gray-500">
                                @lang('portal::app.admin.navigation.theme')
                            </span>
                            <span class="rounded bg-gray-100 px-2 py-0.5 font-mono text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                {{ $themeCode }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between border-b pb-2.5 text-sm dark:border-gray-800">
                            <span class="text-xs text-gray-500">
                                @lang('portal::app.admin.navigation.slots-count')
                            </span>
                            <span class="font-bold text-blue-600">
                                {{ count($slots) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2.5">
                        <a
                            href="{{ route('admin.portal.navigation.footer', ['channel' => $currentChannel->code, 'locale' => $currentLocale->code]) }}"
                            class="secondary-button flex w-full items-center justify-center gap-1.5 py-2.5 text-xs font-semibold"
                        >
                            <span class="icon-arrow-down text-base"></span>
                            <span>@lang('portal::app.admin.navigation.switch-to-footer')</span>
                        </a>

                        <a
                            href="{{ route('admin.portal.menus.index') }}"
                            class="transparent-button flex w-full items-center justify-center gap-1.5 py-2.5 text-xs border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <span class="icon-menu text-base"></span>
                            <span>@lang('portal::app.admin.navigation.all-menus-btn') ({{ $menus->count() }})</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('bagisto.admin.portal.navigation.header.after') !!}
</x-admin::layouts>
