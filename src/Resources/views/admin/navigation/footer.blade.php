<x-admin::layouts>
    <x-slot:title>
        @lang('portal::app.admin.navigation.footer-title')
    </x-slot>

    {!! view_render_event('bagisto.admin.portal.navigation.footer.before') !!}

    <x-admin::form :action="route('admin.portal.navigation.footer.update', ['channel' => $currentChannel->code, 'locale' => $currentLocale->code])">
        <input type="hidden" name="channel" value="{{ $currentChannel->code }}" />
        <input type="hidden" name="locale" value="{{ $currentLocale->code }}" />
        <input type="hidden" name="theme_code" value="{{ $themeCode }}" />

        <!-- Page Header -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('portal::app.admin.navigation.footer-title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.portal.navigation.header', ['channel' => $currentChannel->code, 'locale' => $currentLocale->code]) }}"
                    class="secondary-button"
                >
                    <span class="icon-arrow-up text-lg"></span>
                    @lang('portal::app.admin.navigation.switch-to-header')
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
                <!-- 1. Footer Navigation Slots Accordion (Placed First) -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header class="p-4">
                        <div class="flex items-center gap-2">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('portal::app.admin.navigation.footer-slots')
                            </p>
                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                {{ count($slots) }}
                            </span>
                        </div>
                    </x-slot>

                    <x-slot:content class="p-5 pt-0 space-y-4">
                        @forelse ($slots as $slotId => $slotInfo)
                            @php
                                $assignment = $assignments[$slotId] ?? null;
                                $selectedMenuId = old("slots.{$slotId}.portal_menu_id", data_get($assignment, 'portal_menu_id', ''));
                                $selectedTitle = old("slots.{$slotId}.title", data_get($assignment, 'title', ''));
                                $slotStatus = old("slots.{$slotId}.status", data_get($assignment, 'status', true));
                                $slotLabel = Lang::has('portal::app.admin.navigation.slots.' . $slotId)
                                    ? trans('portal::app.admin.navigation.slots.' . $slotId)
                                    : $slotInfo['label'];
                            @endphp

                            <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 transition-colors hover:border-gray-300 dark:border-gray-800 dark:bg-gray-950/40 dark:hover:border-gray-700">
                                <!-- Slot Header -->
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
                                    value="footer"
                                />

                                <!-- Fields Grid: Column Title & Menu Selector -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Column Title Input -->
                                    <x-admin::form.control-group class="!mb-0">
                                        <x-admin::form.control-group.label>
                                            @lang('portal::app.admin.navigation.slot-title')

                                            <span
                                                class="rounded border border-gray-200 bg-white px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                                v-pre
                                            >
                                                {{ $currentLocale->name }}
                                            </span>
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="text"
                                            id="slot_title_{{ $slotId }}"
                                            name="slots[{{ $slotId }}][title]"
                                            :value="$selectedTitle"
                                            :placeholder="trans('portal::app.admin.navigation.slot-title-placeholder')"
                                        />
                                    </x-admin::form.control-group>

                                    <!-- Assigned Menu Dropdown -->
                                    <x-admin::form.control-group class="!mb-0">
                                        <x-admin::form.control-group.label>
                                            @lang('portal::app.admin.navigation.assigned-menu')
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
                            </div>
                        @empty
                            <div class="py-8 text-center text-sm text-gray-500">
                                @lang('portal::app.admin.navigation.no-slots-found')
                            </div>
                        @endforelse
                    </x-slot:content>
                </x-admin::accordion>

                <!-- 2. Social Media Links Accordion -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header class="p-4">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.navigation.social-links.title')
                        </p>
                    </x-slot>

                    <x-slot:content class="p-5 pt-0 space-y-4">
                        <div class="flex items-center justify-between border-b pb-3 dark:border-gray-800">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                @lang('portal::app.admin.navigation.social-links.status')
                            </span>

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
                    </x-slot>
                </x-admin::accordion>

                <!-- 3. Brand & Contact Information Accordion -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header class="p-4">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.navigation.contact-info.title')
                        </p>
                    </x-slot>

                    <x-slot:content class="p-5 pt-0 space-y-4">
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('portal::app.admin.navigation.contact-info.bio')
                                <span class="rounded border border-gray-200 bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" v-pre>
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
                    </x-slot>
                </x-admin::accordion>

                <!-- 4. Copyright Notice Accordion -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header class="p-4">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.navigation.copyright.title')
                        </p>
                    </x-slot>

                    <x-slot:content class="p-5 pt-0 space-y-4">
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('portal::app.admin.navigation.copyright.text')
                                <span class="rounded border border-gray-200 bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" v-pre>
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
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">
                                @lang('portal::app.admin.navigation.copyright.hint')
                            </p>
                        </x-admin::form.control-group>
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
                            <span class="font-bold text-green-600">
                                {{ count($slots) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2.5">
                        <a
                            href="{{ route('admin.portal.navigation.header', ['channel' => $currentChannel->code, 'locale' => $currentLocale->code]) }}"
                            class="secondary-button flex w-full items-center justify-center gap-1.5 py-2.5 text-xs font-semibold"
                        >
                            <span class="icon-arrow-up text-base"></span>
                            <span>@lang('portal::app.admin.navigation.switch-to-header')</span>
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

    {!! view_render_event('bagisto.admin.portal.navigation.footer.after') !!}
</x-admin::layouts>
