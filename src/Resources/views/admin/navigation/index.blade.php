<x-admin::layouts>
    <x-slot:title>
        @lang('portal::app.admin.navigation.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.portal.navigation.before') !!}

    <x-admin::form :action="route('admin.portal.navigation.update', ['channel' => $currentChannel->code, 'locale' => $currentLocale->code])">
        <input type="hidden" name="channel" value="{{ $currentChannel->code }}" />
        <input type="hidden" name="locale" value="{{ $currentLocale->code }}" />
        <input type="hidden" name="theme_code" value="{{ $themeCode }}" />
        <input type="hidden" name="active_tab" value="{{ $activeTab ?? 'all' }}" />

        <!-- Page Header -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('portal::app.admin.navigation.title')
            </p>

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
                @if (in_array($activeTab ?? 'all', ['all', 'header']))
                    <!-- Header Announcement Bar Accordion -->
                    <x-admin::accordion :is-active="true">
                        <x-slot:header class="p-3">
                            <div class="flex items-center gap-2">
                                <span class="icon-notification text-xl text-amber-500"></span>
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('portal::app.admin.navigation.announcement.title')
                                </p>
                            </div>
                        </x-slot>

                        <x-slot:content class="p-4 space-y-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('portal::app.admin.navigation.announcement.description')
                            </p>

                            <div class="flex items-center justify-between border-b pb-3 dark:border-gray-800">
                                <x-admin::form.control-group.label class="!mb-0 text-xs">
                                    @lang('portal::app.admin.navigation.announcement.status')
                                </x-admin::form.control-group.label>

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
                                    <span class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" v-pre>
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

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

                                <div class="grid grid-cols-2 gap-2">
                                    <x-admin::form.control-group class="!mb-0">
                                        <x-admin::form.control-group.label class="text-xs">
                                            @lang('portal::app.admin.navigation.announcement.bg-color')
                                        </x-admin::form.control-group.label>

                                        <input
                                            type="color"
                                            class="h-10 w-full rounded border border-gray-200 p-1 dark:border-gray-800 dark:bg-gray-900 cursor-pointer"
                                            name="header_settings[announcement][bg_color]"
                                            value="{{ data_get($headerSettings, 'announcement.bg_color', '#2563eb') }}"
                                        />
                                    </x-admin::form.control-group>

                                    <x-admin::form.control-group class="!mb-0">
                                        <x-admin::form.control-group.label class="text-xs">
                                            @lang('portal::app.admin.navigation.announcement.text-color')
                                        </x-admin::form.control-group.label>

                                        <input
                                            type="color"
                                            class="h-10 w-full rounded border border-gray-200 p-1 dark:border-gray-800 dark:bg-gray-900 cursor-pointer"
                                            name="header_settings[announcement][text_color]"
                                            value="{{ data_get($headerSettings, 'announcement.text_color', '#ffffff') }}"
                                        />
                                    </x-admin::form.control-group>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-admin::accordion>

                    <!-- Header Controls & Behavior Accordion -->
                    <x-admin::accordion :is-active="true">
                        <x-slot:header class="p-3">
                            <div class="flex items-center gap-2">
                                <span class="icon-settings text-xl text-gray-700 dark:text-gray-300"></span>
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('portal::app.admin.navigation.header-controls.title')
                                </p>
                            </div>
                        </x-slot>

                        <x-slot:content class="p-4 space-y-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('portal::app.admin.navigation.header-controls.description')
                            </p>

                            <div class="flex items-center justify-between border-b pb-3 dark:border-gray-800">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">
                                        @lang('portal::app.admin.navigation.header-controls.sticky')
                                    </p>
                                </div>
                                <input type="hidden" name="header_settings[controls][sticky]" value="0" />
                                <x-admin::form.control-group.control
                                    type="switch"
                                    id="header_controls_sticky"
                                    name="header_settings[controls][sticky]"
                                    value="1"
                                    :checked="(bool) data_get($headerSettings, 'controls.sticky', true)"
                                />
                            </div>

                            <div class="flex items-center justify-between border-b pb-3 dark:border-gray-800">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">
                                        @lang('portal::app.admin.navigation.header-controls.show-dark-mode')
                                    </p>
                                </div>
                                <input type="hidden" name="header_settings[controls][show_dark_mode]" value="0" />
                                <x-admin::form.control-group.control
                                    type="switch"
                                    id="header_controls_show_dark_mode"
                                    name="header_settings[controls][show_dark_mode]"
                                    value="1"
                                    :checked="(bool) data_get($headerSettings, 'controls.show_dark_mode', true)"
                                />
                            </div>

                            <div class="flex items-center justify-between border-b pb-3 dark:border-gray-800">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">
                                        @lang('portal::app.admin.navigation.header-controls.show-locales')
                                    </p>
                                </div>
                                <input type="hidden" name="header_settings[controls][show_locales]" value="0" />
                                <x-admin::form.control-group.control
                                    type="switch"
                                    id="header_controls_show_locales"
                                    name="header_settings[controls][show_locales]"
                                    value="1"
                                    :checked="(bool) data_get($headerSettings, 'controls.show_locales', true)"
                                />
                            </div>

                            <div class="flex items-center justify-between pb-1">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white">
                                        @lang('portal::app.admin.navigation.header-controls.show-auth')
                                    </p>
                                </div>
                                <input type="hidden" name="header_settings[controls][show_auth]" value="0" />
                                <x-admin::form.control-group.control
                                    type="switch"
                                    id="header_controls_show_auth"
                                    name="header_settings[controls][show_auth]"
                                    value="1"
                                    :checked="(bool) data_get($headerSettings, 'controls.show_auth', true)"
                                />
                            </div>
                        </x-slot:content>
                    </x-admin::accordion>
                @endif

                <!-- Header Slots Section -->
                <x-admin::accordion :is-active="in_array($activeTab ?? 'all', ['all', 'header'])">
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

                @if (in_array($activeTab ?? 'all', ['all', 'footer']))
                    <!-- Footer Social Media Links Accordion -->
                    <x-admin::accordion :is-active="true">
                        <x-slot:header class="p-3">
                            <div class="flex items-center gap-2">
                                <span class="icon-promotion text-xl text-blue-500"></span>
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('portal::app.admin.navigation.social-links.title')
                                </p>
                            </div>
                        </x-slot>

                        <x-slot:content class="p-4 space-y-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('portal::app.admin.navigation.social-links.description')
                            </p>

                            <div class="flex items-center justify-between border-b pb-3 dark:border-gray-800">
                                <x-admin::form.control-group.label class="!mb-0 text-xs">
                                    @lang('portal::app.admin.navigation.social-links.status')
                                </x-admin::form.control-group.label>

                                <input type="hidden" name="footer_settings[social][status]" value="0" />
                                <x-admin::form.control-group.control
                                    type="switch"
                                    id="footer_social_status"
                                    name="footer_settings[social][status]"
                                    value="1"
                                    :checked="(bool) data_get($footerSettings, 'social.status', true)"
                                />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label>
                                        @lang('portal::app.admin.navigation.social-links.twitter')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="footer_settings[social][platforms][twitter]"
                                        :value="data_get($footerSettings, 'social.platforms.twitter', '')"
                                        placeholder="https://x.com/username"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label>
                                        @lang('portal::app.admin.navigation.social-links.instagram')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="footer_settings[social][platforms][instagram]"
                                        :value="data_get($footerSettings, 'social.platforms.instagram', '')"
                                        placeholder="https://instagram.com/username"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label>
                                        @lang('portal::app.admin.navigation.social-links.facebook')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="footer_settings[social][platforms][facebook]"
                                        :value="data_get($footerSettings, 'social.platforms.facebook', '')"
                                        placeholder="https://facebook.com/username"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label>
                                        @lang('portal::app.admin.navigation.social-links.youtube')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="footer_settings[social][platforms][youtube]"
                                        :value="data_get($footerSettings, 'social.platforms.youtube', '')"
                                        placeholder="https://youtube.com/@channel"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label>
                                        @lang('portal::app.admin.navigation.social-links.linkedin')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="footer_settings[social][platforms][linkedin]"
                                        :value="data_get($footerSettings, 'social.platforms.linkedin', '')"
                                        placeholder="https://linkedin.com/company/name"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label>
                                        @lang('portal::app.admin.navigation.social-links.whatsapp')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="footer_settings[social][platforms][whatsapp]"
                                        :value="data_get($footerSettings, 'social.platforms.whatsapp', '')"
                                        placeholder="+966500000000"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label>
                                        @lang('portal::app.admin.navigation.social-links.telegram')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="footer_settings[social][platforms][telegram]"
                                        :value="data_get($footerSettings, 'social.platforms.telegram', '')"
                                        placeholder="https://t.me/channel"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label>
                                        @lang('portal::app.admin.navigation.social-links.tiktok')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="footer_settings[social][platforms][tiktok]"
                                        :value="data_get($footerSettings, 'social.platforms.tiktok', '')"
                                        placeholder="https://tiktok.com/@username"
                                    />
                                </x-admin::form.control-group>
                            </div>
                        </x-slot:content>
                    </x-admin::accordion>

                    <!-- Footer Brand & Contact Information Accordion -->
                    <x-admin::accordion :is-active="true">
                        <x-slot:header class="p-3">
                            <div class="flex items-center gap-2">
                                <span class="icon-mail text-xl text-green-600"></span>
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('portal::app.admin.navigation.contact-info.title')
                                </p>
                            </div>
                        </x-slot>

                        <x-slot:content class="p-4 space-y-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('portal::app.admin.navigation.contact-info.description')
                            </p>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.navigation.contact-info.bio')
                                    <span class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" v-pre>
                                        {{ $currentLocale->name }}
                                    </span>
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    id="footer_contact_bio"
                                    name="footer_settings[contact][bio]"
                                    :value="data_get($footerSettings, 'contact.bio', '')"
                                    :placeholder="trans('portal::app.admin.navigation.contact-info.bio-placeholder')"
                                    rows="3"
                                />
                            </x-admin::form.control-group>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label>
                                        @lang('portal::app.admin.navigation.contact-info.phone')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="footer_settings[contact][phone]"
                                        :value="data_get($footerSettings, 'contact.phone', '')"
                                        :placeholder="trans('portal::app.admin.navigation.contact-info.phone-placeholder')"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label>
                                        @lang('portal::app.admin.navigation.contact-info.email')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="footer_settings[contact][email]"
                                        :value="data_get($footerSettings, 'contact.email', '')"
                                        :placeholder="trans('portal::app.admin.navigation.contact-info.email-placeholder')"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="!mb-0">
                                    <x-admin::form.control-group.label>
                                        @lang('portal::app.admin.navigation.contact-info.address')
                                    </x-admin::form.control-group.label>
                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="footer_settings[contact][address]"
                                        :value="data_get($footerSettings, 'contact.address', '')"
                                        :placeholder="trans('portal::app.admin.navigation.contact-info.address-placeholder')"
                                    />
                                </x-admin::form.control-group>
                            </div>
                        </x-slot:content>
                    </x-admin::accordion>

                    <!-- Footer Copyright Accordion -->
                    <x-admin::accordion :is-active="true">
                        <x-slot:header class="p-3">
                            <div class="flex items-center gap-2">
                                <span class="icon-checked text-xl text-purple-600"></span>
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('portal::app.admin.navigation.copyright.title')
                                </p>
                            </div>
                        </x-slot>

                        <x-slot:content class="p-4 space-y-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('portal::app.admin.navigation.copyright.description')
                            </p>

                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.navigation.copyright.text')
                                    <span class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" v-pre>
                                        {{ $currentLocale->name }}
                                    </span>
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="footer_copyright_text"
                                    name="footer_settings[copyright][text]"
                                    :value="data_get($footerSettings, 'copyright.text', '')"
                                    :placeholder="trans('portal::app.admin.navigation.copyright.text-placeholder')"
                                />
                                <p class="mt-1 text-xs text-gray-400">
                                    @lang('portal::app.admin.navigation.copyright.hint')
                                </p>
                            </x-admin::form.control-group>
                        </x-slot:content>
                    </x-admin::accordion>
                @endif

                <!-- Footer Slots Section -->
                <x-admin::accordion :is-active="in_array($activeTab ?? 'all', ['all', 'footer'])">
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
                    <x-admin::accordion :is-active="($activeTab ?? 'all') === 'all'">
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
