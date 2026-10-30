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
                    </x-slot>
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
            <!-- Left Container: Menu Structure Builder (Family-Style) -->
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

                            <x-admin::form.control-group.control
                                type="switch"
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

    @pushOnce('scripts')
        <script type="text/x-template" id="v-menu-builder-template">
            <div>
                <!-- Panel Header -->
                <div class="mb-2.5 flex flex-wrap justify-between items-center gap-2.5 p-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.menus.builder.structure-title')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('portal::app.admin.menus.builder.structure-hint')
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-x-2">
                        <div
                            class="secondary-button cursor-pointer flex items-center gap-1"
                            @click="$refs.addPagesModal.open()"
                        >
                            <span class="icon-attribute text-lg"></span>
                            @lang('portal::app.admin.menus.builder.panels.portal-pages')
                        </div>

                        <div
                            class="secondary-button cursor-pointer flex items-center gap-1"
                            @click="$refs.addCustomModal.open()"
                        >
                            <span class="icon-folder text-lg"></span>
                            @lang('portal::app.admin.menus.builder.panels.custom-links')
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="flatTree.length === 0"
                    class="p-12 text-center"
                >
                    <span class="icon-menu text-5xl text-gray-300 dark:text-gray-600 mb-2 inline-block"></span>
                    <p class="text-base font-semibold text-gray-700 dark:text-gray-300">
                        @lang('portal::app.admin.menus.builder.empty-title')
                    </p>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                        @lang('portal::app.admin.menus.builder.empty-desc')
                    </p>
                </div>

                <!-- Draggable Hierarchical Tree -->
                <div v-else class="p-4 space-y-2">
                    <draggable
                        ghost-class="draggable-ghost"
                        handle=".icon-drag"
                        v-bind="{animation: 200}"
                        :list="flatTree"
                        item-key="_uid"
                    >
                        <template #item="{ element, index }">
                            <div
                                class="group flex items-center justify-between gap-3 rounded border border-gray-200 dark:border-gray-800 p-2.5 bg-white dark:bg-gray-950 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-all"
                                :style="isRtl ? { marginRight: (element.depth * 32) + 'px' } : { marginLeft: (element.depth * 32) + 'px' }"
                            >
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <i class="icon-drag cursor-grab text-xl text-gray-400 group-hover:text-gray-800 dark:group-hover:text-white transition-all"></i>

                                    <span v-if="element.depth > 0" class="text-blue-600 font-bold text-sm ltr:mr-1 rtl:ml-1">
                                        └─
                                    </span>

                                    <i
                                        class="text-xl"
                                        :class="element.type === 'page' ? 'icon-attribute text-purple-600' : 'icon-folder text-blue-600'"
                                    ></i>

                                    <span class="text-sm font-semibold text-gray-800 dark:text-white truncate">
                                        @{{ getItemLabel(element) }}
                                    </span>

                                    <span class="label-info text-xs">
                                        @{{ element.type }}
                                    </span>

                                    <span v-if="element.depth > 0" class="label-pending text-xs">
                                        @lang('portal::app.admin.menus.builder.sub-item')
                                    </span>
                                </div>

                                <!-- Action Controls -->
                                <div class="flex items-center gap-1 shrink-0">
                                    <!-- Outdent -->
                                    <i
                                        class="icon-arrow-left text-xl cursor-pointer p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-800 rtl:rotate-180 text-gray-600 dark:text-gray-300 transition-all"
                                        :class="{'opacity-30 cursor-not-allowed': element.depth === 0}"
                                        @click="outdentItem(element)"
                                        title="@lang('portal::app.admin.menus.builder.outdent')"
                                    ></i>

                                    <!-- Indent -->
                                    <i
                                        class="icon-arrow-right text-xl cursor-pointer p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-800 rtl:rotate-180 text-gray-600 dark:text-gray-300 transition-all"
                                        :class="{'opacity-30 cursor-not-allowed': !canIndent(index)}"
                                        @click="indentItem(index)"
                                        title="@lang('portal::app.admin.menus.builder.indent')"
                                    ></i>

                                    <!-- Edit in Modal -->
                                    <i
                                        class="icon-edit text-xl cursor-pointer p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 transition-all"
                                        @click="openEditModal(element)"
                                        title="@lang('portal::app.admin.menus.builder.edit')"
                                    ></i>

                                    <!-- Delete -->
                                    <i
                                        class="icon-delete text-xl cursor-pointer p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-800 text-red-600 transition-all"
                                        @click="removeItem(element)"
                                        title="@lang('portal::app.admin.menus.builder.remove-item')"
                                    ></i>
                                </div>
                            </div>
                        </template>
                    </draggable>
                </div>

                <!-- Hidden Input with Serialized Items Tree for Form Submission -->
                <input
                    type="hidden"
                    name="items_json"
                    :value="JSON.stringify(serializeTree())"
                />

                <!-- Modal 1: Add Portal Pages -->
                <x-admin::modal ref="addPagesModal">
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.menus.builder.panels.portal-pages')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <div v-if="portalPages.length === 0" class="text-xs text-gray-400 text-center py-6">
                            @lang('portal::app.admin.menus.builder.no-pages')
                        </div>

                        <div v-else class="max-h-[300px] overflow-y-auto space-y-2 pe-1">
                            <label
                                v-for="page in portalPages"
                                :key="page.id"
                                class="flex items-center gap-2.5 p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer text-sm text-gray-700 dark:text-gray-300"
                            >
                                <input
                                    type="checkbox"
                                    :value="page.id"
                                    v-model="selectedPageIds"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <span class="truncate font-medium">@{{ page.title }}</span>
                                <span class="text-xs text-gray-400 font-mono">/@{{ page.slug }}</span>
                            </label>
                        </div>
                    </x-slot>

                    <x-slot:footer>
                        <div class="flex items-center justify-between w-full">
                            <button
                                type="button"
                                class="text-xs text-blue-600 hover:underline"
                                @click="toggleSelectAllPages"
                            >
                                <span v-if="portalPages.length > 0 && selectedPageIds.length === portalPages.length">
                                    @lang('portal::app.admin.menus.builder.deselect-all')
                                </span>
                                <span v-else>
                                    @lang('portal::app.admin.menus.builder.select-all')
                                </span>
                            </button>

                            <button
                                type="button"
                                class="primary-button"
                                :disabled="selectedPageIds.length === 0"
                                @click="addSelectedPages"
                            >
                                @lang('portal::app.admin.menus.builder.add-to-menu')
                            </button>
                        </div>
                    </x-slot>
                </x-admin::modal>

                <!-- Modal 2: Add Custom Link -->
                <x-admin::modal ref="addCustomModal">
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.menus.builder.panels.custom-links')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                @lang('portal::app.admin.menus.builder.url')
                            </label>
                            <input
                                type="text"
                                v-model="newCustomLink.url"
                                placeholder="https://... or /about"
                                class="w-full rounded border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            />
                        </div>

                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                @lang('portal::app.admin.menus.builder.link-text') ({{ $currentLocale->name }})
                            </label>
                            <input
                                type="text"
                                v-model="newCustomLink.title"
                                class="w-full rounded border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                            />
                        </div>
                    </x-slot>

                    <x-slot:footer>
                        <button
                            type="button"
                            class="primary-button"
                            @click="addCustomLink"
                        >
                            @lang('portal::app.admin.menus.builder.add-to-menu')
                        </button>
                    </x-slot>
                </x-admin::modal>

                <!-- Modal 3: Edit Item Details in Current Locale -->
                <x-admin::modal ref="editItemModal">
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.menus.builder.edit')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <div v-if="activeItem" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    @lang('portal::app.admin.menus.builder.link-text') ({{ $currentLocale->name }})
                                </label>
                                <input
                                    type="text"
                                    v-model="activeItem.translations[currentLocaleCode].title"
                                    class="w-full rounded border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                />
                            </div>

                            <div v-if="activeItem.type === 'custom'">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    @lang('portal::app.admin.menus.builder.url')
                                </label>
                                <input
                                    type="text"
                                    v-model="activeItem.url"
                                    class="w-full rounded border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                />
                            </div>

                            <div class="pt-2">
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                                    <input
                                        type="checkbox"
                                        :checked="activeItem.target === '_blank'"
                                        @change="activeItem.target = $event.target.checked ? '_blank' : '_self'"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    <span>@lang('portal::app.admin.menus.builder.open-in-new-tab')</span>
                                </label>
                            </div>
                        </div>
                    </x-slot>

                    <x-slot:footer>
                        <button
                            type="button"
                            class="primary-button"
                            @click="$refs.editItemModal.close()"
                        >
                            @lang('portal::app.admin.menus.edit.save-btn')
                        </button>
                    </x-slot>
                </x-admin::modal>
            </div>
        </script>

        <script type="module">
            app.component('v-menu-builder', {
                template: '#v-menu-builder-template',

                props: {
                    initialItems: { type: Array, default: () => [] },
                    portalPages: { type: Array, default: () => [] },
                    currentLocaleCode: { type: String, required: true },
                    currentLocaleName: { type: String, required: true },
                },

                data() {
                    return {
                        treeItems: [],
                        activeItem: null,
                        selectedPageIds: [],
                        newCustomLink: {
                            url: '',
                            title: '',
                        },
                        uidCounter: 1,
                    };
                },

                computed: {
                    isRtl() {
                        return document.documentElement.dir === 'rtl';
                    },

                    flatTree() {
                        const result = [];
                        const traverse = (items, depth = 0, parentUid = null) => {
                            for (const item of items) {
                                item.depth = depth;
                                item.parentUid = parentUid;
                                result.push(item);
                                if (item.children && item.children.length > 0) {
                                    traverse(item.children, depth + 1, item._uid);
                                }
                            }
                        };
                        traverse(this.treeItems, 0, null);
                        return result;
                    },
                },

                created() {
                    this.treeItems = this.normalizeInitialItems(this.initialItems);
                },

                methods: {
                    normalizeInitialItems(items) {
                        return (items || []).map(item => {
                            const translationsMap = {};
                            (item.translations || []).forEach(tr => {
                                translationsMap[tr.locale] = { title: tr.title || '' };
                            });

                            if (!translationsMap[this.currentLocaleCode]) {
                                translationsMap[this.currentLocaleCode] = {
                                    title: item.title || '',
                                };
                            }

                            return {
                                _uid: 'node_' + (this.uidCounter++),
                                id: item.id || null,
                                type: item.type || 'custom',
                                portal_page_id: item.portal_page_id || null,
                                url: item.url || '',
                                target: item.target || '_self',
                                status: item.status !== undefined ? Boolean(item.status) : true,
                                page: item.page || null,
                                depth: item.depth || 0,
                                translations: translationsMap,
                                children: this.normalizeInitialItems(item.children || []),
                            };
                        });
                    },

                    getItemLabel(item) {
                        if (item.translations && item.translations[this.currentLocaleCode]?.title) {
                            return item.translations[this.currentLocaleCode].title;
                        }

                        if (item.translations) {
                            for (const code in item.translations) {
                                if (item.translations[code]?.title) {
                                    return item.translations[code].title;
                                }
                            }
                        }

                        if (item.type === 'page' && item.page) {
                            const pageTr = (item.page.translations || []).find(t => t.locale === this.currentLocaleCode)
                                || (item.page.translations || [])[0];
                            if (pageTr?.title) return pageTr.title;
                            if (item.page.title) return item.page.title;
                        }

                        return item.url || '#';
                    },

                    toggleSelectAllPages() {
                        if (this.selectedPageIds.length === this.portalPages.length) {
                            this.selectedPageIds = [];
                        } else {
                            this.selectedPageIds = this.portalPages.map(p => p.id);
                        }
                    },

                    addSelectedPages() {
                        if (this.selectedPageIds.length === 0) return;

                        const selected = this.portalPages.filter(p => this.selectedPageIds.includes(p.id));

                        selected.forEach(page => {
                            const translationsMap = {};
                            translationsMap[this.currentLocaleCode] = {
                                title: page.title,
                            };

                            this.treeItems.push({
                                _uid: 'node_' + (this.uidCounter++),
                                id: null,
                                type: 'page',
                                portal_page_id: page.id,
                                page: page,
                                url: null,
                                target: '_self',
                                status: true,
                                depth: 0,
                                translations: translationsMap,
                                children: [],
                            });
                        });

                        this.selectedPageIds = [];
                        this.$refs.addPagesModal.close();
                    },

                    addCustomLink() {
                        if (!this.newCustomLink.url || !this.newCustomLink.url.trim()) {
                            return;
                        }

                        const translationsMap = {};
                        translationsMap[this.currentLocaleCode] = {
                            title: this.newCustomLink.title.trim() || this.newCustomLink.url.trim(),
                        };

                        this.treeItems.push({
                            _uid: 'node_' + (this.uidCounter++),
                            id: null,
                            type: 'custom',
                            portal_page_id: null,
                            page: null,
                            url: this.newCustomLink.url.trim(),
                            target: '_self',
                            status: true,
                            depth: 0,
                            translations: translationsMap,
                            children: [],
                        });

                        this.newCustomLink.url = '';
                        this.newCustomLink.title = '';
                        this.$refs.addCustomModal.close();
                    },

                    canIndent(index) {
                        if (index === 0) return false;
                        const item = this.flatTree[index];
                        const prev = this.flatTree[index - 1];
                        return prev && prev.depth >= item.depth;
                    },

                    indentItem(index) {
                        if (!this.canIndent(index)) return;
                        const item = this.flatTree[index];
                        const prev = this.flatTree[index - 1];

                        this.removeFromTree(item._uid);

                        if (!prev.children) prev.children = [];
                        prev.children.push(item);
                    },

                    outdentItem(item) {
                        if (item.depth === 0) return;

                        const parent = this.findInTree(this.treeItems, item.parentUid);
                        if (!parent) return;

                        this.removeFromTree(item._uid);

                        if (parent.parentUid) {
                            const grandParent = this.findInTree(this.treeItems, parent.parentUid);
                            if (grandParent) {
                                const idx = grandParent.children.findIndex(c => c._uid === parent._uid);
                                grandParent.children.splice(idx + 1, 0, item);
                            }
                        } else {
                            const idx = this.treeItems.findIndex(c => c._uid === parent._uid);
                            this.treeItems.splice(idx + 1, 0, item);
                        }
                    },

                    removeFromTree(uid) {
                        const remove = (list) => {
                            for (let i = 0; i < list.length; i++) {
                                if (list[i]._uid === uid) {
                                    list.splice(i, 1);
                                    return true;
                                }
                                if (list[i].children && remove(list[i].children)) {
                                    return true;
                                }
                            }
                            return false;
                        };
                        remove(this.treeItems);
                    },

                    findInTree(list, uid) {
                        for (const item of list) {
                            if (item._uid === uid) return item;
                            if (item.children) {
                                const found = this.findInTree(item.children, uid);
                                if (found) return found;
                            }
                        }
                        return null;
                    },

                    openEditModal(item) {
                        if (!item.translations) item.translations = {};
                        if (!item.translations[this.currentLocaleCode]) {
                            item.translations[this.currentLocaleCode] = { title: '' };
                        }
                        this.activeItem = item;
                        this.$refs.editItemModal.open();
                    },

                    removeItem(item) {
                        this.removeFromTree(item._uid);
                    },

                    serializeItems(list) {
                        return list.map((item, idx) => ({
                            id: item.id || null,
                            type: item.type,
                            portal_page_id: item.portal_page_id || null,
                            url: item.url || null,
                            target: item.target || '_self',
                            status: item.status ? 1 : 0,
                            sort_order: idx,
                            translations: item.translations,
                            children: item.children ? this.serializeItems(item.children) : [],
                        }));
                    },

                    serializeTree() {
                        return this.serializeItems(this.treeItems);
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
