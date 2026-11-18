<x-admin::layouts>
    <x-slot:title>
        @lang('portal::app.admin.menus.edit.title', ['name' => $menu->name])
    </x-slot>

    {!! view_render_event('bagisto.admin.portal.menus.edit.before') !!}

    <x-admin::form
        method="PUT"
        :action="route('admin.portal.menus.update', $menu->id)"
    >
        <!-- Page Header -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('portal::app.admin.menus.edit.title', ['name' => $menu->name])
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Locale Switcher Dropdown -->
                <x-admin::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'left' : 'right' }}">
                    <x-slot:toggle>
                        <button
                            type="button"
                            class="transparent-button px-2 py-1.5 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800 flex items-center gap-1.5"
                        >
                            <span class="icon-language text-2xl"></span>
                            <span v-pre>{{ $currentLocale->name }}</span>
                            <input type="hidden" name="locale" value="{{ $currentLocale->code }}" />
                            <span class="icon-sort-down text-2xl"></span>
                        </button>
                    </x-slot>

                    <x-slot:content class="!p-0">
                        @foreach ($locales as $locale)
                            <a
                                href="?{{ Arr::query(['locale' => $locale->code]) }}"
                                class="flex gap-2.5 px-5 py-2 text-base cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-950 dark:text-white {{ $locale->code == $currentLocale->code ? 'bg-gray-100 dark:bg-gray-950' : '' }}"
                                v-pre
                            >
                                {{ $locale->name }}
                            </a>
                        @endforeach
                    </x-slot:content>
                </x-admin::dropdown>

                <a
                    href="{{ route('admin.portal.menus.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('portal::app.admin.menus.edit.cancel-btn')
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('portal::app.admin.menus.edit.save-btn')
                </button>
            </div>
        </div>

        <!-- Main Container -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Container: Menu Structure Builder -->
            <div class="box-shadow flex flex-1 flex-col gap-2 rounded bg-white dark:bg-gray-900 max-xl:flex-auto">
                <v-menu-builder
                    :initial-items='@json($items)'
                    :portal-pages='@json($portalPages)'
                    current-locale-code="{{ $currentLocale->code }}"
                    current-locale-name="{{ $currentLocale->name }}"
                >
                    <x-admin::shimmer.accordion class="h-[271px] w-[360px]" />
                </v-menu-builder>
            </div>

            <!-- Right Container: General Settings Accordion -->
            <div class="flex w-[360px] max-w-full select-none flex-col gap-2">
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.menus.edit.general')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <!-- Name -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required !text-gray-800 dark:!text-white">
                                @lang('portal::app.admin.menus.edit.name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="name"
                                name="name"
                                rules="required"
                                :value="old('name', $menu->name)"
                                :label="trans('portal::app.admin.menus.edit.name')"
                                :placeholder="trans('portal::app.admin.menus.edit.name-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="name" />
                        </x-admin::form.control-group>

                        <!-- Slug -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required !text-gray-800 dark:!text-white">
                                @lang('portal::app.admin.menus.edit.slug')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="slug"
                                name="slug"
                                rules="required"
                                :value="old('slug', $menu->slug)"
                                :label="trans('portal::app.admin.menus.edit.slug')"
                                :placeholder="trans('portal::app.admin.menus.edit.slug-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="slug" />
                        </x-admin::form.control-group>

                        <!-- Channel -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="!text-gray-800 dark:!text-white">
                                @lang('portal::app.admin.menus.edit.channel')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                id="channel_id"
                                name="channel_id"
                                :value="old('channel_id', $menu->channel_id)"
                                :label="trans('portal::app.admin.menus.edit.channel')"
                            >
                                <option value="">
                                    @lang('portal::app.admin.menus.edit.all-channels')
                                </option>

                                @foreach ($channels as $channel)
                                    <option value="{{ $channel->id }}" {{ old('channel_id', $menu->channel_id) == $channel->id ? 'selected' : '' }}>
                                        {{ core()->getChannelName($channel) }}
                                    </option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="channel_id" />
                        </x-admin::form.control-group>

                        <!-- Status Switch -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label class="!text-gray-800 dark:!text-white">
                                @lang('portal::app.admin.menus.edit.status')
                            </x-admin::form.control-group.label>

                            <input type="hidden" name="status" value="0" />

                            <x-admin::form.control-group.control
                                type="switch"
                                id="status"
                                name="status"
                                :value="1"
                                :checked="(bool) old('status', $menu->status)"
                            />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('bagisto.admin.portal.menus.edit.after') !!}

    @include('portal::admin.menus.builder.scripts')
</x-admin::layouts>
