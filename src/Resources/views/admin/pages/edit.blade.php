@php
    $currentLocale = core()->getRequestedLocale();
@endphp

<x-admin::layouts>
    <x-slot:title>
        @lang('portal::app.admin.pages.edit.title', ['title' => $page->title])
    </x-slot>

    {!! view_render_event('bagisto.admin.portal.pages.edit.before') !!}

    <x-admin::form
        :action="route('admin.portal.pages.update', $page->id)"
        method="PUT"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('portal::app.admin.pages.edit.title', ['title' => $page->title])
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.portal.pages.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('portal::app.admin.pages.create.cancel-btn')
                </a>

                <a
                    href="{{ route('admin.portal.pages.customize', $page->id) }}"
                    class="secondary-button"
                >
                    @lang('portal::app.admin.pages.edit.customize-btn')
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('portal::app.admin.pages.edit.save-btn')
                </button>
            </div>
        </div>

        <!-- Locale Switcher -->
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

        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left sub-component: SEO & Details -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('portal::app.admin.pages.create.seo')
                    </p>

                    <!-- SEO Title & Description Blade Component -->
                    <x-admin::seo
                        slug="pages"
                        meta-title-field="meta_title"
                        url-key-field="slug"
                        meta-description-field="meta_description"
                    />

                    <!-- Slug / URL Key -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('portal::app.admin.pages.create.slug')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="slug"
                            name="slug"
                            rules="required"
                            :value="old('slug', $page->slug)"
                            :label="trans('portal::app.admin.pages.create.slug')"
                            :placeholder="trans('portal::app.admin.pages.create.slug-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="slug" />
                    </x-admin::form.control-group>

                    <!-- Meta Title -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('portal::app.admin.pages.create.meta-title')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="meta_title"
                            name="{{ $currentLocale->code }}[meta_title]"
                            :value="old($currentLocale->code)['meta_title'] ?? ($page->translate($currentLocale->code)['meta_title'] ?? '')"
                            :label="trans('portal::app.admin.pages.create.meta-title')"
                        />

                        <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[meta_title]" />
                    </x-admin::form.control-group>

                    <!-- Meta Keywords -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('portal::app.admin.pages.create.meta-keywords')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            id="meta_keywords"
                            name="{{ $currentLocale->code }}[meta_keywords]"
                            :value="old($currentLocale->code)['meta_keywords'] ?? ($page->translate($currentLocale->code)['meta_keywords'] ?? '')"
                            :label="trans('portal::app.admin.pages.create.meta-keywords')"
                        />

                        <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[meta_keywords]" />
                    </x-admin::form.control-group>

                    <!-- Meta Description -->
                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('portal::app.admin.pages.create.meta-description')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            id="meta_description"
                            name="{{ $currentLocale->code }}[meta_description]"
                            :value="old($currentLocale->code)['meta_description'] ?? ($page->translate($currentLocale->code)['meta_description'] ?? '')"
                            :label="trans('portal::app.admin.pages.create.meta-description')"
                        />

                        <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[meta_description]" />
                    </x-admin::form.control-group>
                </div>
            </div>

            <!-- Right sub-component: General Settings & Channel -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.pages.create.general')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <!-- Page Title -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('portal::app.admin.pages.create.page-title') ({{ $currentLocale->name }})
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="title"
                                name="{{ $currentLocale->code }}[title]"
                                rules="required"
                                :value="old($currentLocale->code)['title'] ?? ($page->translate($currentLocale->code)['title'] ?? '')"
                                :label="trans('portal::app.admin.pages.create.page-title')"
                                :placeholder="trans('portal::app.admin.pages.create.page-title-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[title]" />
                        </x-admin::form.control-group>

                        <!-- Select Channel -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('portal::app.admin.pages.create.channel')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                id="channel_id"
                                name="channel_id"
                                rules="required"
                                :value="old('channel_id', $page->channel_id)"
                                :label="trans('portal::app.admin.pages.create.channel')"
                            >
                                @foreach ($channels as $channel)
                                    <option value="{{ $channel->id }}" {{ $channel->id === (int) old('channel_id', $page->channel_id) ? 'selected' : '' }}>
                                        {{ core()->getChannelName($channel) }}
                                    </option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="channel_id" />
                        </x-admin::form.control-group>

                        <!-- Status Switch -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('portal::app.admin.pages.create.status')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                id="status"
                                name="status"
                                :value="1"
                                :checked="(bool) old('status', $page->status)"
                            />
                        </x-admin::form.control-group>

                        <!-- Set as Home Page Switch -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('portal::app.admin.pages.create.is-home-page')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                id="is_home_page"
                                name="is_home_page"
                                :value="1"
                                :checked="(bool) old('is_home_page', $page->is_home_page)"
                            />

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @lang('portal::app.admin.pages.create.is-home-page-info')
                            </p>
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('bagisto.admin.portal.pages.edit.after') !!}

    @push('scripts')
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                const statusInput = document.getElementById('status');
                const homePageInput = document.getElementById('is_home_page');

                if (statusInput && homePageInput) {
                    homePageInput.addEventListener('change', () => {
                        if (homePageInput.checked && !statusInput.checked) {
                            statusInput.checked = true;
                            statusInput.dispatchEvent(new Event('change'));
                        }
                    });

                    statusInput.addEventListener('change', () => {
                        if (!statusInput.checked && homePageInput.checked) {
                            homePageInput.checked = false;
                            homePageInput.dispatchEvent(new Event('change'));
                        }
                    });
                }
            });
        </script>
    @endpush
</x-admin::layouts>
