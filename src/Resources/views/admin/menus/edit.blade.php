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

    @pushOnce('scripts')
        <!-- Recursive Menu Item Template -->
        <script type="text/x-template" id="v-menu-tree-item-template">
            <div class="flex flex-col gap-2 rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 p-2.5 transition-all">
                <!-- Item Header Row -->
                <div class="flex items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="icon-drag cursor-grab text-xl text-gray-400 hover:text-gray-800 dark:hover:text-white transition-all"></i>

                        <span v-if="depth > 0" class="text-blue-500 font-bold text-sm ltr:mr-0.5 rtl:ml-0.5 select-none">
                            └─
                        </span>

                        <i
                            class="text-xl shrink-0"
                            :class="item.type === 'page' ? 'icon-attribute text-purple-600' : 'icon-folder text-blue-600'"
                        ></i>

                        <span class="text-sm font-semibold text-gray-800 dark:text-white truncate">
                            @{{ getItemLabel() }}
                        </span>

                        <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            @{{ item.type }}
                        </span>

                        <span
                            v-if="item.children && item.children.length > 0"
                            class="rounded bg-blue-50 px-1.5 py-0.5 text-[10px] font-semibold text-blue-600 dark:bg-blue-900/40 dark:text-blue-300"
                        >
                            @{{ item.children.length }} @lang('portal::app.admin.menus.builder.sub-item')
                        </span>
                    </div>

                    <!-- Action Controls -->
                    <div class="flex items-center gap-1 shrink-0">
                        <!-- Move Up Button -->
                        <i
                            class="icon-sort-up text-xl p-1 rounded transition-all select-none"
                            :class="index === 0 ? 'opacity-20 cursor-not-allowed text-gray-400' : 'cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300'"
                            @click="moveUp"
                            title="@lang('portal::app.admin.menus.builder.move-up')"
                        ></i>

                        <!-- Move Down Button -->
                        <i
                            class="icon-sort-down text-xl p-1 rounded transition-all select-none"
                            :class="index === parentItems.length - 1 ? 'opacity-20 cursor-not-allowed text-gray-400' : 'cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300'"
                            @click="moveDown"
                            title="@lang('portal::app.admin.menus.builder.move-down')"
                        ></i>

                        <!-- Outdent Button (Make Parent) -->
                        <i
                            class="icon-arrow-left text-xl p-1 rounded transition-all rtl:rotate-180 select-none"
                            :class="depth === 0 ? 'opacity-20 cursor-not-allowed text-gray-400' : 'cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300'"
                            @click="outdent"
                            title="@lang('portal::app.admin.menus.builder.outdent')"
                        ></i>

                        <!-- Indent Button (Make Child) -->
                        <i
                            class="icon-arrow-right text-xl p-1 rounded transition-all rtl:rotate-180 select-none"
                            :class="index === 0 ? 'opacity-20 cursor-not-allowed text-gray-400' : 'cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300'"
                            @click="indent"
                            title="@lang('portal::app.admin.menus.builder.indent')"
                        ></i>

                        <!-- Edit Button -->
                        <i
                            class="icon-edit text-xl cursor-pointer p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 transition-all"
                            @click="$emit('edit', item)"
                            title="@lang('portal::app.admin.menus.builder.edit')"
                        ></i>

                        <!-- Delete Button -->
                        <i
                            class="icon-delete text-xl cursor-pointer p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-800 text-red-600 transition-all"
                            @click="$emit('delete', { item, parentItems, index })"
                            title="@lang('portal::app.admin.menus.builder.remove-item')"
                        ></i>
                    </div>
                </div>

                <!-- Nested Children Draggable Container -->
                <div v-if="item.children" class="mt-1 ps-4 border-s-2 border-blue-200 dark:border-blue-900 ms-3">
                    <draggable
                        :list="item.children"
                        group="menu-hierarchy"
                        handle=".icon-drag"
                        item-key="_uid"
                        ghost-class="draggable-ghost"
                        :move="checkMove"
                        @end="onDragEnd"
                        v-bind="{animation: 200}"
                        class="space-y-2 min-h-[10px]"
                    >
                        <template #item="{ element, index: childIndex }">
                            <v-menu-tree-item
                                :item="element"
                                :index="childIndex"
                                :parent-items="item.children"
                                :parent-item="item"
                                :depth="depth + 1"
                                :current-locale-code="currentLocaleCode"
                                @edit="$emit('edit', $event)"
                                @delete="$emit('delete', $event)"
                                @tree-change="$emit('tree-change')"
                            />
                        </template>
                    </draggable>
                </div>
            </div>
        </script>

        <!-- Main Menu Builder Template -->
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
                    v-if="treeItems.length === 0"
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

                <!-- Root Draggable List -->
                <div v-else class="p-4">
                    <draggable
                        :list="treeItems"
                        group="menu-hierarchy"
                        handle=".icon-drag"
                        item-key="_uid"
                        ghost-class="draggable-ghost"
                        :move="checkMove"
                        @end="onDragEnd"
                        v-bind="{animation: 200}"
                        class="space-y-2 min-h-[50px]"
                    >
                        <template #item="{ element, index }">
                            <v-menu-tree-item
                                :item="element"
                                :index="index"
                                :parent-items="treeItems"
                                :parent-item="null"
                                :depth="0"
                                :current-locale-code="currentLocaleCode"
                                @edit="openEditModal"
                                @delete="removeItem"
                                @tree-change="onTreeChange"
                            />
                        </template>
                    </draggable>
                </div>

                <!-- Form Field with Serialized JSON Tree -->
                <input
                    type="hidden"
                    name="items_json"
                    id="portal_menu_items_json"
                    :value="serializedJson"
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
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    @lang('portal::app.admin.menus.builder.url')
                                </label>
                                <input
                                    type="text"
                                    v-model="newCustomLink.url"
                                    placeholder="@lang('portal::app.admin.menus.builder.url-placeholder')"
                                    class="w-full rounded border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                    @lang('portal::app.admin.menus.builder.link-text')
                                </label>
                                <input
                                    type="text"
                                    v-model="newCustomLink.title"
                                    placeholder="@lang('portal::app.admin.menus.builder.link-text-placeholder')"
                                    class="w-full rounded border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                />
                            </div>
                        </div>
                    </x-slot>

                    <x-slot:footer>
                        <button
                            type="button"
                            class="primary-button"
                            :disabled="!newCustomLink.url || !newCustomLink.url.trim()"
                            @click="addCustomLink"
                        >
                            @lang('portal::app.admin.menus.builder.add-to-menu')
                        </button>
                    </x-slot>
                </x-admin::modal>

                <!-- Modal 3: Edit Item Details -->
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
                                    @lang('portal::app.admin.menus.builder.title-label', ['locale' => $currentLocale->name])
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
                            @click="$refs.editItemModal.close(); syncSerializedJson()"
                        >
                            @lang('portal::app.admin.menus.edit.save-btn')
                        </button>
                    </x-slot>
                </x-admin::modal>
            </div>
        </script>

        <script type="module">
            app.component('v-menu-tree-item', {
                name: 'v-menu-tree-item',

                template: '#v-menu-tree-item-template',

                props: {
                    item: { type: Object, required: true },
                    index: { type: Number, required: true },
                    parentItems: { type: Array, required: true },
                    parentItem: { type: Object, default: null },
                    depth: { type: Number, default: 0 },
                    currentLocaleCode: { type: String, required: true },
                },

                emits: ['edit', 'delete', 'tree-change'],

                methods: {
                    getItemLabel() {
                        const tr = this.item.translations && this.item.translations[this.currentLocaleCode];
                        if (tr && tr.title) return tr.title;

                        if (this.item.translations) {
                            for (const code in this.item.translations) {
                                if (this.item.translations[code]?.title) {
                                    return this.item.translations[code].title;
                                }
                            }
                        }

                        if (this.item.type === 'page' && this.item.page) {
                            const pageTr = (this.item.page.translations || []).find(t => t.locale === this.currentLocaleCode)
                                || (this.item.page.translations || [])[0];
                            if (pageTr?.title) return pageTr.title;
                            if (this.item.page.title) return this.item.page.title;
                        }

                        return this.item.url || '#';
                    },

                    moveUp() {
                        if (this.index === 0) return;
                        const item = this.parentItems.splice(this.index, 1)[0];
                        this.parentItems.splice(this.index - 1, 0, item);
                        this.$emit('tree-change');
                    },

                    moveDown() {
                        if (this.index === this.parentItems.length - 1) return;
                        const item = this.parentItems.splice(this.index, 1)[0];
                        this.parentItems.splice(this.index + 1, 0, item);
                        this.$emit('tree-change');
                    },

                    indent() {
                        if (this.index === 0) return;
                        const prevSibling = this.parentItems[this.index - 1];
                        if (!prevSibling.children) {
                            prevSibling.children = [];
                        }
                        const item = this.parentItems.splice(this.index, 1)[0];
                        prevSibling.children.push(item);
                        this.$emit('tree-change');
                    },

                    outdent() {
                        if (this.depth === 0 || !this.parentItem) return;
                        this.$emit('delete', {
                            item: this.item,
                            parentItems: this.parentItems,
                            index: this.index,
                            reinsertAfter: this.parentItem
                        });
                        this.$emit('tree-change');
                    },

                    onDragEnd() {
                        this.$emit('tree-change');
                    },

                    checkMove(evt) {
                        if (!evt.dragged || !evt.to) return true;
                        // Prevent dropping an element inside itself or its children
                        if (evt.dragged === evt.to || evt.dragged.contains(evt.to)) {
                            return false;
                        }
                        return true;
                    },
                },
            });

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
                        serializedJson: '[]',
                        activeItem: null,
                        selectedPageIds: [],
                        newCustomLink: {
                            url: '',
                            title: '',
                        },
                        uidCounter: 1,
                    };
                },

                watch: {
                    treeItems: {
                        deep: true,
                        handler() {
                            this.syncSerializedJson();
                        },
                    },
                },

                created() {
                    this.treeItems = this.normalizeInitialItems(this.initialItems);
                    this.syncSerializedJson();
                },

                mounted() {
                    this.syncSerializedJson();

                    const form = this.$el.closest('form');
                    if (form) {
                        form.addEventListener('submit', () => {
                            this.syncSerializedJson();
                        });
                    }
                },

                methods: {
                    syncSerializedJson() {
                        const json = JSON.stringify(this.serializeItems(this.treeItems));
                        this.serializedJson = json;

                        const input = document.getElementById('portal_menu_items_json')
                            || (this.$el ? this.$el.querySelector('input[name="items_json"]') : null);
                        if (input) {
                            input.value = json;
                        }
                    },

                    onTreeChange() {
                        this.syncSerializedJson();
                    },

                    onDragEnd() {
                        this.syncSerializedJson();
                    },

                    checkMove(evt) {
                        if (!evt.dragged || !evt.to) return true;
                        // Prevent dropping an element inside itself or its children
                        if (evt.dragged === evt.to || evt.dragged.contains(evt.to)) {
                            return false;
                        }
                        return true;
                    },
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
                                translations: translationsMap,
                                children: this.normalizeInitialItems(item.children || []),
                            };
                        });
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
                                translations: translationsMap,
                                children: [],
                            });
                        });

                        this.selectedPageIds = [];
                        this.$refs.addPagesModal.close();
                        this.syncSerializedJson();
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
                            translations: translationsMap,
                            children: [],
                        });

                        this.newCustomLink.url = '';
                        this.newCustomLink.title = '';
                        this.$refs.addCustomModal.close();
                        this.syncSerializedJson();
                    },

                    openEditModal(item) {
                        if (!item.translations) item.translations = {};
                        if (!item.translations[this.currentLocaleCode]) {
                            item.translations[this.currentLocaleCode] = { title: '' };
                        }
                        this.activeItem = item;
                        this.$refs.editItemModal.open();
                    },

                    removeItem(payload) {
                        const { item, parentItems, index, reinsertAfter } = payload;
                        if (reinsertAfter) {
                            // Find reinsertAfter in tree and insert item after it
                            const removed = parentItems.splice(index, 1)[0];
                            this.insertAfter(this.treeItems, reinsertAfter._uid, removed);
                            this.syncSerializedJson();
                            return;
                        }
                        parentItems.splice(index, 1);
                        this.syncSerializedJson();
                    },

                    insertAfter(list, targetUid, itemToInsert) {
                        for (let i = 0; i < list.length; i++) {
                            if (list[i]._uid === targetUid) {
                                list.splice(i + 1, 0, itemToInsert);
                                return true;
                            }
                            if (list[i].children && this.insertAfter(list[i].children, targetUid, itemToInsert)) {
                                return true;
                            }
                        }
                        return false;
                    },

                    serializeItems(list) {
                        return (list || []).map((item, idx) => ({
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
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
