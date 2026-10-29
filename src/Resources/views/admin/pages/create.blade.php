<x-admin::layouts>
    <x-slot:title>
        @lang('portal::app.admin.pages.create.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.portal.pages.create.before') !!}

    <x-admin::form :action="route('admin.portal.pages.store')">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('portal::app.admin.pages.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.portal.pages.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('portal::app.admin.pages.create.cancel-btn')
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('portal::app.admin.pages.create.save-btn')
                </button>
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
                            :value="old('slug')"
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
                            name="meta_title"
                            :value="old('meta_title')"
                            :label="trans('portal::app.admin.pages.create.meta-title')"
                        />

                        <x-admin::form.control-group.error control-name="meta_title" />
                    </x-admin::form.control-group>

                    <!-- Meta Keywords -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('portal::app.admin.pages.create.meta-keywords')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            id="meta_keywords"
                            name="meta_keywords"
                            :value="old('meta_keywords')"
                            :label="trans('portal::app.admin.pages.create.meta-keywords')"
                        />

                        <x-admin::form.control-group.error control-name="meta_keywords" />
                    </x-admin::form.control-group>

                    <!-- Meta Description -->
                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('portal::app.admin.pages.create.meta-description')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            id="meta_description"
                            name="meta_description"
                            :value="old('meta_description')"
                            :label="trans('portal::app.admin.pages.create.meta-description')"
                        />

                        <x-admin::form.control-group.error control-name="meta_description" />
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
                                @lang('portal::app.admin.pages.create.page-title')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="title"
                                name="title"
                                rules="required"
                                :value="old('title')"
                                :label="trans('portal::app.admin.pages.create.page-title')"
                                :placeholder="trans('portal::app.admin.pages.create.page-title-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="title" />
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
                                :value="old('channel_id', core()->getCurrentChannel()->id)"
                                :label="trans('portal::app.admin.pages.create.channel')"
                            >
                                @foreach ($channels as $channel)
                                    <option value="{{ $channel->id }}">
                                        {{ core()->getChannelName($channel) }}
                                    </option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="channel_id" />
                        </x-admin::form.control-group>

                        <!-- Status Switch -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('portal::app.admin.pages.create.status')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="switch"
                                id="status"
                                name="status"
                                :value="1"
                                :checked="(bool) old('status', true)"
                            />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>
    </x-admin::form>

    {!! view_render_event('bagisto.admin.portal.pages.create.after') !!}
</x-admin::layouts>
