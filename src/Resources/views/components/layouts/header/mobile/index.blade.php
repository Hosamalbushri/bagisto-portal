{!! view_render_event('bagisto.portal.layout.header.mobile.before') !!}

@php
    $isShopMode = function_exists('portal_shop_enabled') && portal_shop_enabled();

    // Build hierarchical items from assigned menu slot 'header_mobile' or 'header_main'
    $assignedMenu = portal_menu_slot('header_mobile') ?: portal_menu_slot('header_main');
    $navigationItems = [];

    if ($assignedMenu && $assignedMenu->rootItems && $assignedMenu->rootItems->isNotEmpty()) {
        $buildTree = function ($items) use (&$buildTree) {
            $result = [];
            foreach ($items as $item) {
                if (! $item->status) continue;
                $activeChildren = $item->children ? $item->children->where('status', 1) : collect();
                $result[] = [
                    'id' => $item->id,
                    'name' => $item->getTitle(),
                    'url' => $item->getUrl() ?: '#',
                    'description' => null,
                    'is_active' => $item->isActive(),
                    'children' => $activeChildren->isNotEmpty() ? $buildTree($activeChildren) : [],
                ];
            }
            return $result;
        };

        $navigationItems = $buildTree($assignedMenu->rootItems);
    }

    if (empty($navigationItems) && ! ($isShopMode && Route::has('shop.api.categories.tree'))) {
        $navigationItems = [
            [
                'id' => 'home',
                'name' => trans('portal::app.home.nav.home'),
                'url' => portal_home_url(),
                'is_active' => request()->routeIs('portal.home') || request()->is('/'),
                'children' => [],
            ],
        ];
    }
@endphp

<div class="flex lg:hidden items-center justify-between h-16 px-4">
    {{-- Left/Right: Bagisto Drawer (<x-portal::drawer>) + Logo --}}
    <div class="flex items-center gap-3">
        {!! view_render_event('bagisto.portal.layout.header.mobile.drawer.before') !!}

        <x-portal::drawer
            position="{{ core()->getCurrentLocale()->direction === 'rtl' ? 'right' : 'left' }}"
            width="85%"
        >
            <x-slot:toggle>
                <button
                    type="button"
                    class="w-10 h-10 inline-flex items-center justify-center rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                    aria-label="فتح القائمة"
                >
                    <span class="icon-hamburger text-xl"></span>
                </button>
            </x-slot:toggle>

            <x-slot:header>
                <div class="flex items-center justify-between py-2">
                    <a href="{{ portal_home_url() }}">
                        <img
                            src="{{ core()->getCurrentChannel()->logo_url ?? (function_exists('bagisto_asset') ? bagisto_asset('images/logo.svg') : (function_exists('portal_asset') ? portal_asset('images/logo.svg') : '')) }}"
                            alt="{{ config('app.name') }}"
                            class="h-6 w-auto"
                        >
                    </a>
                </div>
            </x-slot:header>

            <x-slot:content class="!p-3">
                {{-- Slot declaration for MenuSlotScanner --}}
                {{-- @portalMenuSlot('header_mobile', 'Mobile Navigation', 'header') --}}

                <v-mobile-category :initial-categories='@json($navigationItems)'></v-mobile-category>
            </x-slot:content>
        </x-portal::drawer>

        {!! view_render_event('bagisto.portal.layout.header.mobile.drawer.after') !!}

        <a href="{{ portal_home_url() }}" class="font-semibold text-[15px] text-slate-900 dark:text-white">
            {{ config('app.name') }}
        </a>
    </div>
</div>

{!! view_render_event('bagisto.portal.layout.header.mobile.after') !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-mobile-category-template"
    >
        <nav aria-label="قائمة الجوال">
            <ul role="list" class="space-y-1">
                <li
                    v-for="category in categories"
                    :key="category.id"
                    class="border-b border-slate-100 dark:border-slate-800/70 pb-1"
                >
                    <div class="flex items-center justify-between">
                        <a
                            :href="sanitizeUrl(category.url)"
                            class="flex-1 py-2.5 px-3 text-[14px] font-semibold text-slate-900 dark:text-white"
                            :class="{ '!text-[var(--brand-primary)] font-bold': category.is_active || isActiveUrl(category.url) }"
                            v-text="category.name"
                        ></a>

                        <button
                            v-if="category.children && category.children.length"
                            type="button"
                            @click="toggle(category.id)"
                            :aria-expanded="isOpen(category.id) ? 'true' : 'false'"
                            class="w-10 h-10 inline-flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                        >
                            <span
                                class="icon-arrow-down text-sm transition-transform duration-150"
                                :class="{ 'rotate-180': isOpen(category.id) }"
                            ></span>
                        </button>
                    </div>

                    {{-- Level 2 Accordion --}}
                    <ul
                        v-if="category.children && category.children.length"
                        v-show="isOpen(category.id)"
                        role="list"
                        class="ps-3 pb-2 space-y-1"
                    >
                        <li
                            v-for="secondLevel in category.children"
                            :key="secondLevel.id"
                        >
                            <div class="flex items-center justify-between">
                                <a
                                    :href="sanitizeUrl(secondLevel.url)"
                                    class="flex-1 py-2 px-3 text-[13px] font-medium text-slate-700 dark:text-slate-300"
                                    :class="{ '!text-[var(--brand-primary)] font-bold': secondLevel.is_active || isActiveUrl(secondLevel.url) }"
                                    v-text="secondLevel.name"
                                ></a>

                                <button
                                    v-if="secondLevel.children && secondLevel.children.length"
                                    type="button"
                                    @click="toggle(secondLevel.id)"
                                    :aria-expanded="isOpen(secondLevel.id) ? 'true' : 'false'"
                                    class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-50"
                                >
                                    <span
                                        class="icon-arrow-down text-xs transition-transform duration-150"
                                        :class="{ 'rotate-180': isOpen(secondLevel.id) }"
                                    ></span>
                                </button>
                            </div>

                            {{-- Level 3 Accordion --}}
                            <ul
                                v-if="secondLevel.children && secondLevel.children.length"
                                v-show="isOpen(secondLevel.id)"
                                role="list"
                                class="ms-3 border-s border-slate-200 dark:border-slate-800 mt-0.5 space-y-0.5"
                            >
                                <li
                                    v-for="thirdLevel in secondLevel.children"
                                    :key="thirdLevel.id"
                                >
                                    <a
                                        :href="sanitizeUrl(thirdLevel.url)"
                                        class="block py-2 ps-4 pe-2 text-[13px] text-slate-600 dark:text-slate-400 hover:text-slate-900"
                                        :class="{ '!text-[var(--brand-primary)] font-bold': thirdLevel.is_active || isActiveUrl(thirdLevel.url) }"
                                        v-text="thirdLevel.name"
                                    ></a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </script>

    <script type="module">
        app.component('v-mobile-category', {
            template: '#v-mobile-category-template',

            props: {
                initialCategories: {
                    type: Array,
                    default: () => [],
                },
            },

            data() {
                return {
                    categories: (this.initialCategories && this.initialCategories.length) ? this.initialCategories : [],
                    openIds: {},
                };
            },

            mounted() {
                if (! this.categories || ! this.categories.length) {
                    this.getCategories();
                }
            },

            methods: {
                getCategories() {
                    const url = "{{ ($isShopMode && Route::has('shop.api.categories.tree')) ? route('shop.api.categories.tree') : '' }}";
                    if (! url) return;

                    this.$axios.get(url)
                        .then(response => {
                            this.categories = response.data.data;
                        })
                        .catch(error => {
                            console.error(error);
                        });
                },

                sanitizeUrl(url) {
                    if (! url || typeof url !== 'string') {
                        return '#';
                    }

                    const trimmed = url.trim();

                    return /^(javascript|data|vbscript):/i.test(trimmed) ? '#' : trimmed;
                },

                isActiveUrl(url) {
                    if (! url || url === '#' || url.startsWith('javascript:')) return false;
                    try {
                        const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
                        const targetUrl = new URL(url, window.location.origin);
                        const targetPath = targetUrl.pathname.replace(/\/+$/, '') || '/';
                        return currentPath === targetPath;
                    } catch (e) {
                        return false;
                    }
                },

                toggle(id) {
                    this.openIds[id] = ! this.openIds[id];
                },

                isOpen(id) {
                    return Boolean(this.openIds[id]);
                },
            },
        });
    </script>
@endPushOnce
