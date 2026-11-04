<x-admin::layouts>
    <x-slot:title>
        @lang('portal::app.admin.themes.title')
    </x-slot>

    <!-- Page Header (Exact Bagisto Core Pattern) -->
    <div class="grid gap-1.5">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('portal::app.admin.themes.title')
        </p>

        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
            @lang('portal::app.admin.themes.description')
        </p>
    </div>

    <v-portal-themes
        :themes='@json($themes)'
        :channels='@json($channels->map(fn ($channel) => ['id' => $channel->id, 'name' => $channel->name])->values())'
    >
        <x-admin::shimmer.image class="mt-8 h-[300px] w-full rounded" />
    </v-portal-themes>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-portal-themes-template"
        >
            <div class="mt-8 flex flex-col gap-8">
                <div
                    v-for="group in groups"
                    :key="group.key"
                >
                    <!-- Group Heading -->
                    <div class="flex items-center gap-2">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @{{ group.label }}
                        </p>

                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-950 dark:text-gray-300">
                            @{{ group.themes.length }}
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-[repeat(auto-fill,minmax(300px,1fr))] gap-6">
                        <div
                            class="box-shadow flex flex-col overflow-hidden rounded bg-white dark:bg-gray-900"
                            v-for="theme in group.themes"
                            :key="theme.code"
                        >
                            <!-- Screenshot -->
                            <div class="relative flex h-[190px] items-center justify-center bg-gray-100 dark:bg-gray-950">
                                <img
                                    class="h-full w-full object-cover"
                                    :src="theme.screenshot"
                                    :alt="theme.name"
                                    v-if="theme.screenshot && ! failed[theme.code]"
                                    v-on:error="failed[theme.code] = true"
                                />

                                <span
                                    class="icon-image text-5xl text-gray-400"
                                    v-else
                                ></span>

                                <!-- Active Badge -->
                                <span
                                    class="label-active absolute top-3 ltr:left-3 rtl:right-3"
                                    v-if="theme.status === 'active'"
                                >
                                    @lang('portal::app.admin.themes.active-badge')
                                </span>
                            </div>

                            <!-- Body -->
                            <div class="flex flex-1 flex-col gap-2 p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                                        @{{ theme.name }}
                                    </p>
                                </div>

                                <p class="text-xs text-gray-500 dark:text-gray-300">
                                    <template v-if="theme.author">
                                        @lang('portal::app.admin.themes.author'): @{{ theme.author }}
                                    </template>

                                    <template v-if="theme.version">
                                        · v@{{ theme.version }}
                                    </template>
                                </p>

                                <p
                                    class="line-clamp-3 text-xs text-gray-600 dark:text-gray-300"
                                    v-if="theme.description"
                                >
                                    @{{ theme.description }}
                                </p>

                                <!-- Active Channels -->
                                <p
                                    class="mt-1 text-xs font-medium text-green-600"
                                    v-if="isEverywhere(theme)"
                                >
                                    @lang('admin::app.appearance.themes.index.active-on-all')
                                </p>

                                <p
                                    class="mt-1 text-xs font-medium text-green-600"
                                    v-else-if="theme.active_on.length"
                                >
                                    @lang('admin::app.appearance.themes.index.active-on')
                                    @{{ theme.active_on.map(channel => channel.name).join(', ') }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-gray-400"
                                    v-else-if="theme.is_installed"
                                >
                                    @lang('admin::app.appearance.themes.index.not-in-use')
                                </p>

                                <!-- Actions -->
                                <div class="mt-auto flex w-full flex-wrap items-center gap-2 pt-3">
                                    <button
                                        type="button"
                                        class="primary-button"
                                        v-if="theme.is_installed && availableChannels(theme).length"
                                        @click="openActivate(theme)"
                                    >
                                        @lang('portal::app.admin.themes.activate-btn')
                                    </button>

                                    <a
                                        class="secondary-button"
                                        :href="'{{ route('admin.portal.themes.customize', '__CODE__') }}'.replace('__CODE__', theme.code)"
                                        v-if="theme.status === 'active'"
                                    >
                                        @lang('admin::app.appearance.themes.index.customize-btn')
                                    </a>

                                    <a
                                        class="secondary-button"
                                        :href="'{{ route('admin.portal.themes.tokens.customize', '__CODE__') }}'.replace('__CODE__', theme.code)"
                                        v-if="theme.status === 'active'"
                                    >
                                        @lang('portal::app.admin.themes.customize-tokens-btn')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activate Confirmation Modal (Exact Bagisto Core Pattern) -->
                <Teleport to="body">
                    <x-admin::form
                        v-slot="{ meta, errors, handleSubmit }"
                        as="div"
                    >
                        <form @submit="handleSubmit($event, activate)">
                            <x-admin::modal ref="activateModal">
                                <x-slot:header>
                                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                                        @lang('admin::app.appearance.themes.index.activate.title')
                                    </p>
                                </x-slot>

                                <x-slot:content>
                                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">
                                        @lang('admin::app.appearance.themes.index.activate.info', ['theme' => '@{{ selected.name }}'])
                                    </p>

                                    <!-- Channels -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('admin::app.appearance.themes.index.activate.channels')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="multiselect"
                                            name="channel_ids"
                                            class="cursor-pointer"
                                            rules="required"
                                            v-model="channelIds"
                                            :label="trans('admin::app.appearance.themes.index.activate.channels')"
                                        >
                                            <option
                                                v-for="channel in availableChannels(selected)"
                                                :key="channel.id"
                                                :value="channel.id"
                                                v-text="channel.name"
                                            ></option>
                                        </x-admin::form.control-group.control>

                                        <x-admin::form.control-group.error control-name="channel_ids" />

                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">
                                            @lang('admin::app.appearance.themes.index.activate.channels-hint')
                                        </p>
                                    </x-admin::form.control-group>
                                </x-slot>

                                <x-slot:footer>
                                    <x-admin::button
                                        button-type="submit"
                                        class="primary-button"
                                        :title="trans('admin::app.appearance.themes.index.activate.confirm-btn')"
                                        ::loading="isLoading"
                                        ::disabled="isLoading"
                                    />
                                </x-slot>
                            </x-admin::modal>
                        </form>
                    </x-admin::form>
                </Teleport>
            </div>
        </script>

        <script type="module">
            app.component('v-portal-themes', {
                template: '#v-portal-themes-template',

                props: ['themes', 'channels'],

                data() {
                    return {
                        selected: {},

                        channelIds: [],

                        failed: {},

                        isLoading: false,
                    };
                },

                computed: {
                    groups() {
                        return [
                            {
                                key: 'installed',
                                label: "@lang('admin::app.appearance.themes.index.my-themes')",
                                themes: this.themes.filter(theme => theme.is_installed),
                            },
                        ].filter(group => group.themes.length);
                    },
                },

                methods: {
                    availableChannels(theme) {
                        if (! theme?.active_on) {
                            return this.channels;
                        }

                        const live = theme.active_on.map(channel => channel.id);

                        return this.channels.filter(channel => ! live.includes(channel.id));
                    },

                    isEverywhere(theme) {
                        return theme.active_on.length > 0 && ! this.availableChannels(theme).length;
                    },

                    openActivate(theme) {
                        this.selected = theme;

                        const available = this.availableChannels(theme);

                        this.channelIds = available.length === 1 ? [available[0].id] : [];

                        this.$refs.activateModal.open();
                    },

                    activate() {
                        this.isLoading = true;

                        this.$axios.post("{{ route('admin.portal.themes.activate') }}", {
                                channel_ids: this.channelIds,
                                theme_code: this.selected.code,
                            })
                            .then(response => {
                                window.location.reload();
                            })
                            .catch(error => {
                                this.isLoading = false;

                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message ?? error.message,
                                });
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
