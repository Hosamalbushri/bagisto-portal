<x-admin::layouts>
    <x-slot:title>
        @lang('portal::app.admin.tokens.title') - {{ ucfirst($themeCode) }}
    </x-slot>

    <!-- Main Token Customization Form using Native Bagisto Form Component -->
    <x-admin::form
        :action="route('admin.portal.themes.tokens')"
        method="POST"
        class="h-full"
    >
        <input type="hidden" name="channel_id" value="{{ $currentChannel->id }}" />
        <input type="hidden" name="theme_code" value="{{ $themeCode }}" />

        <div class="flex h-[calc(100vh-140px)] min-h-[480px] flex-col">
            <!-- Page Header (Exact Appearance Sections Customizer Pattern) -->
            <div class="flex shrink-0 items-center justify-between gap-4 max-sm:flex-wrap">
                <div class="grid gap-1.5">
                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('admin.portal.themes.index') }}"
                            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                        >
                            <span class="icon-arrow-left text-2xl"></span>
                        </a>

                        <p class="text-xl font-bold text-gray-800 dark:text-white">
                            @lang('portal::app.admin.tokens.title') - <span class="text-blue-600">{{ ucfirst($themeCode) }}</span>
                        </p>
                    </div>

                    <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                        @lang('admin::app.appearance.sections.index.scoped-to', [
                            'theme'   => ucfirst($themeCode),
                            'channel' => $currentChannel->name,
                        ])
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2.5 max-sm:w-full max-sm:flex-wrap">
                    <!-- Channel Switcher Dropdown (Native Bagisto Dropdown Component) -->
                    <x-admin::dropdown :class="$channels->count() <= 1 ? 'hidden' : ''">
                        <x-slot:toggle>
                            <button
                                type="button"
                                class="transparent-button px-2.5 py-1.5 hover:bg-gray-200 focus:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                            >
                                <span class="icon-store text-xl"></span>
                                <span class="text-xs font-semibold">{{ $currentChannel->name }}</span>
                                <span class="icon-sort-down text-xl"></span>
                            </button>
                        </x-slot>

                        <x-slot:content class="!p-0">
                            @foreach ($channels as $channel)
                                <a
                                    href="?{{ Arr::query(['channel_id' => $channel->id]) }}"
                                    class="flex cursor-pointer items-center gap-2 px-4 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-950 {{ $channel->id === $currentChannel->id ? 'bg-gray-100 dark:bg-gray-950 font-bold text-blue-600' : '' }}"
                                >
                                    <span class="icon-store text-base"></span>
                                    {{ $channel->name }}
                                </a>
                            @endforeach
                        </x-slot:content>
                    </x-admin::dropdown>

                    <!-- Reset Tokens Button -->
                    <button
                        type="button"
                        class="secondary-button"
                        onclick="if (confirm('هل أنت متأكد من استعادة ألوان القالب الافتراضية؟')) { document.getElementById('portal-reset-tokens-form').submit(); }"
                    >
                        @lang('portal::app.admin.themes.reset-tokens-btn')
                    </button>

                    <!-- Save Tokens Button -->
                    <button
                        type="submit"
                        class="primary-button"
                    >
                        @lang('portal::app.admin.themes.save-tokens-btn')
                    </button>
                </div>
            </div>

            <!-- Workspace: Sidebar (Controls) + Preview (Stage) -->
            <div class="mt-4 flex min-h-0 min-w-0 flex-1">
                <div class="flex min-h-0 min-w-0 flex-1 gap-4 max-lg:flex-col">

                    <!-- Controls Sidebar (Scrollable internally) -->
                    <div class="box-shadow flex w-[380px] shrink-0 flex-col overflow-hidden rounded bg-white dark:bg-gray-900 max-lg:w-full">
                        <div class="flex shrink-0 items-center justify-between border-b p-4 dark:border-gray-800">
                            <p class="text-base font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                                <span class="icon-theme text-xl text-blue-600"></span>
                                @lang('portal::app.admin.tokens.title')
                            </p>
                        </div>

                        <!-- Scrollable Accordions Area -->
                        <div class="min-h-0 flex-1 overflow-y-auto p-3 space-y-3">

                <!-- 1. Presets Accordion -->
                <x-admin::accordion :is-active="true">
                    <x-slot:header>
                        <div class="flex items-center gap-2 p-1.5">
                            <span class="icon-theme text-xl text-blue-600"></span>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('portal::app.admin.tokens.presets-title')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('portal::app.admin.tokens.tab-presets')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="preset"
                                class="cursor-pointer text-xs"
                                onchange="applyPresetLive(this);"
                            >
                                <option value="">-- @lang('portal::app.admin.tokens.apply-preset') --</option>
                                @foreach ($presets as $key => $preset)
                                    <option value="{{ $key }}">
                                        {{ trans($preset['name']) }}
                                    </option>
                                @endforeach
                            </x-admin::form.control-group.control>
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>

                <!-- 2. Brand Identity Accordion -->
                <x-admin::accordion :is-active="true">
                    <x-slot:header>
                        <div class="flex items-center gap-2 p-1.5">
                            <span class="icon-attribute text-xl text-blue-600"></span>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('portal::app.admin.tokens.tab-brand')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1 pt-1">
                            <!-- Primary Color with Live WCAG Contrast Badge -->
                            <x-admin::form.control-group>
                                <div class="flex items-center justify-between">
                                    <x-admin::form.control-group.label class="required !mb-0">
                                        @lang('portal::app.admin.tokens.brand-primary')
                                    </x-admin::form.control-group.label>

                                    <span
                                        id="brand-contrast-badge"
                                        class="rounded px-2 py-0.5 text-[10px] font-bold transition-all bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300"
                                        title="مؤشر سهولة قراءة النصوص على هذا اللون الأساسي وفق معيار WCAG 2.1"
                                    >
                                        AAA 7.5:1
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 mt-1">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--brand-primary'] ?? '#1c66f6' }}"
                                        oninput="document.getElementById('input-brand-primary').value = this.value; updatePreviewToken('--brand-primary', this.value); updateContrastBadge();"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-brand-primary"
                                        name="tokens[--brand-primary]"
                                        :value="$tokens['--brand-primary'] ?? '#1c66f6'"
                                        placeholder="#1c66f6"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--brand-primary', this.value); updateContrastBadge();"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Accent Color -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('portal::app.admin.tokens.brand-accent')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--brand-accent'] ?? '#fe8312' }}"
                                        oninput="document.getElementById('input-brand-accent').value = this.value; updatePreviewToken('--brand-accent', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-brand-accent"
                                        name="tokens[--brand-accent]"
                                        :value="$tokens['--brand-accent'] ?? '#fe8312'"
                                        placeholder="#fe8312"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--brand-accent', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Primary Hover -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.brand-primary-hover')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--brand-primary-hover'] ?? '#1551c6' }}"
                                        oninput="document.getElementById('input-brand-primary-hover').value = this.value; updatePreviewToken('--brand-primary-hover', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-brand-primary-hover"
                                        name="tokens[--brand-primary-hover]"
                                        :value="$tokens['--brand-primary-hover'] ?? '#1551c6'"
                                        placeholder="#1551c6"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--brand-primary-hover', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Accent Hover -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.brand-accent-hover')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--brand-accent-hover'] ?? '#e0700a' }}"
                                        oninput="document.getElementById('input-brand-accent-hover').value = this.value; updatePreviewToken('--brand-accent-hover', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-brand-accent-hover"
                                        name="tokens[--brand-accent-hover]"
                                        :value="$tokens['--brand-accent-hover'] ?? '#e0700a'"
                                        placeholder="#e0700a"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--brand-accent-hover', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>
                        </div>
                    </x-slot>
                </x-admin::accordion>

                <!-- 3. Surfaces & Canvas Accordion -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header>
                        <div class="flex items-center gap-2 p-1.5">
                            <span class="icon-dashboard text-xl text-blue-600"></span>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('portal::app.admin.tokens.tab-surfaces')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1 pt-1">
                            <!-- App Background -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.bg-app')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--bg-app'] ?? '#f8fafc' }}"
                                        oninput="document.getElementById('input-bg-app').value = this.value; updatePreviewToken('--bg-app', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-bg-app"
                                        name="tokens[--bg-app]"
                                        :value="$tokens['--bg-app'] ?? '#f8fafc'"
                                        placeholder="#f8fafc"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--bg-app', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Surface Background -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.bg-surface')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--bg-surface'] ?? '#ffffff' }}"
                                        oninput="document.getElementById('input-bg-surface').value = this.value; updatePreviewToken('--bg-surface', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-bg-surface"
                                        name="tokens[--bg-surface]"
                                        :value="$tokens['--bg-surface'] ?? '#ffffff'"
                                        placeholder="#ffffff"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--bg-surface', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Elevated Surface -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.bg-surface-elevated')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--bg-surface-elevated'] ?? '#f1f5f9' }}"
                                        oninput="document.getElementById('input-bg-surface-elevated').value = this.value; updatePreviewToken('--bg-surface-elevated', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-bg-surface-elevated"
                                        name="tokens[--bg-surface-elevated]"
                                        :value="$tokens['--bg-surface-elevated'] ?? '#f1f5f9'"
                                        placeholder="#f1f5f9"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--bg-surface-elevated', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Dark App Canvas -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    الوضع الداكن: خلفية الصفحة (Dark Canvas)
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--dark-bg-app'] ?? '#080b11' }}"
                                        oninput="document.getElementById('input-dark-bg-app').value = this.value; updatePreviewToken('--dark-bg-app', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-dark-bg-app"
                                        name="tokens[--dark-bg-app]"
                                        :value="$tokens['--dark-bg-app'] ?? '#080b11'"
                                        placeholder="#080b11"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--dark-bg-app', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Dark Card Surface -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    الوضع الداكن: أسطح البطاقات (Dark Surface)
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--dark-bg-surface'] ?? '#0f141f' }}"
                                        oninput="document.getElementById('input-dark-bg-surface').value = this.value; updatePreviewToken('--dark-bg-surface', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-dark-bg-surface"
                                        name="tokens[--dark-bg-surface]"
                                        :value="$tokens['--dark-bg-surface'] ?? '#0f141f'"
                                        placeholder="#0f141f"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--dark-bg-surface', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>
                        </div>
                    </x-slot>
                </x-admin::accordion>

                <!-- 4. Typography & Texts Accordion -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header>
                        <div class="flex items-center gap-2 p-1.5">
                            <span class="icon-cms text-xl text-blue-600"></span>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('portal::app.admin.tokens.tab-typography')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1 pt-1">
                            <!-- Arabic Font -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.font-arabic')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="tokens[--font-arabic]"
                                    :value="$tokens['--font-arabic'] ?? '\'Cairo\', system-ui, sans-serif'"
                                    class="cursor-pointer text-xs"
                                    onchange="onFontSelected('--font-arabic', this.value);"
                                >
                                    <option value="'Cairo', system-ui, sans-serif" {{ str_contains($tokens['--font-arabic'] ?? '', 'Cairo') || empty($tokens['--font-arabic']) ? 'selected' : '' }}>Cairo (كايرو - الافتراضي)</option>
                                    <option value="'Tajawal', system-ui, sans-serif" {{ str_contains($tokens['--font-arabic'] ?? '', 'Tajawal') ? 'selected' : '' }}>Tajawal (تجوال - هندسي نظيف)</option>
                                    <option value="'Almarai', system-ui, sans-serif" {{ str_contains($tokens['--font-arabic'] ?? '', 'Almarai') ? 'selected' : '' }}>Almarai (المراعي - عريض للشركات)</option>
                                    <option value="'Readex Pro', system-ui, sans-serif" {{ str_contains($tokens['--font-arabic'] ?? '', 'Readex Pro') ? 'selected' : '' }}>Readex Pro (ريديكس برو - للشاشات)</option>
                                    <option value="'Alexandria', system-ui, sans-serif" {{ str_contains($tokens['--font-arabic'] ?? '', 'Alexandria') ? 'selected' : '' }}>Alexandria (الإسكندرية - عصري)</option>
                                    <option value="'IBM Plex Sans Arabic', system-ui, sans-serif" {{ str_contains($tokens['--font-arabic'] ?? '', 'IBM Plex') ? 'selected' : '' }}>IBM Plex Sans Arabic (تقني احترافي)</option>
                                    <option value="system-ui, -apple-system, sans-serif" {{ ($tokens['--font-arabic'] ?? '') === "system-ui, -apple-system, sans-serif" ? 'selected' : '' }}>خط النظام (System Sans)</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <!-- Latin Font -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.font-latin')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="tokens[--font-latin]"
                                    :value="$tokens['--font-latin'] ?? '\'Inter\', system-ui, sans-serif'"
                                    class="cursor-pointer text-xs"
                                    onchange="onFontSelected('--font-latin', this.value);"
                                >
                                    <option value="'Inter', system-ui, sans-serif" {{ str_contains($tokens['--font-latin'] ?? '', 'Inter') || empty($tokens['--font-latin']) ? 'selected' : '' }}>Inter (Clean Tech Standard)</option>
                                    <option value="'Plus Jakarta Sans', system-ui, sans-serif" {{ str_contains($tokens['--font-latin'] ?? '', 'Plus Jakarta') ? 'selected' : '' }}>Plus Jakarta Sans (Modern Geometric)</option>
                                    <option value="'Outfit', system-ui, sans-serif" {{ str_contains($tokens['--font-latin'] ?? '', 'Outfit') ? 'selected' : '' }}>Outfit (Futuristic Minimal)</option>
                                    <option value="'Poppins', system-ui, sans-serif" {{ str_contains($tokens['--font-latin'] ?? '', 'Poppins') ? 'selected' : '' }}>Poppins (Friendly Rounded)</option>
                                    <option value="'Roboto', system-ui, sans-serif" {{ str_contains($tokens['--font-latin'] ?? '', 'Roboto') ? 'selected' : '' }}>Roboto (Universal Google Sans)</option>
                                    <option value="system-ui, -apple-system, sans-serif" {{ ($tokens['--font-latin'] ?? '') === "system-ui, -apple-system, sans-serif" ? 'selected' : '' }}>System Default</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <!-- Primary Text Color -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.text-primary')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--text-primary'] ?? '#090d16' }}"
                                        oninput="document.getElementById('input-text-primary').value = this.value; updatePreviewToken('--text-primary', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-text-primary"
                                        name="tokens[--text-primary]"
                                        :value="$tokens['--text-primary'] ?? '#090d16'"
                                        placeholder="#090d16"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--text-primary', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Secondary Text Color -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.text-secondary')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--text-secondary'] ?? '#475569' }}"
                                        oninput="document.getElementById('input-text-secondary').value = this.value; updatePreviewToken('--text-secondary', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-text-secondary"
                                        name="tokens[--text-secondary]"
                                        :value="$tokens['--text-secondary'] ?? '#475569'"
                                        placeholder="#475569"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--text-secondary', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>
                        </div>
                    </x-slot>
                </x-admin::accordion>

                <!-- 5. Geometry & Radii Accordion -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header>
                        <div class="flex items-center gap-2 p-1.5">
                            <span class="icon-calendar text-xl text-blue-600"></span>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('portal::app.admin.tokens.tab-geometry')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('portal::app.admin.tokens.radius-base')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="tokens[--radius-base]"
                                :value="$tokens['--radius-base'] ?? '0.75rem'"
                                class="cursor-pointer text-xs"
                                onchange="updatePreviewToken('--radius-base', this.value);"
                            >
                                <option value="0rem" {{ ($tokens['--radius-base'] ?? '') === '0rem' ? 'selected' : '' }}>حواف حادة ومستقيمة (Sharp - 0rem)</option>
                                <option value="0.25rem" {{ ($tokens['--radius-base'] ?? '') === '0.25rem' ? 'selected' : '' }}>انحناء خفيف (Subtle - 0.25rem / 4px)</option>
                                <option value="0.5rem" {{ ($tokens['--radius-base'] ?? '') === '0.5rem' ? 'selected' : '' }}>انحناء متوسط (Medium - 0.5rem / 8px)</option>
                                <option value="0.75rem" {{ ($tokens['--radius-base'] ?? '') === '0.75rem' || empty($tokens['--radius-base']) ? 'selected' : '' }}>انحناء عصري ناعم (Smooth - 0.75rem / 12px)</option>
                                <option value="1rem" {{ ($tokens['--radius-base'] ?? '') === '1rem' ? 'selected' : '' }}>انحناء دائري عريض (Rounded - 1rem / 16px)</option>
                                <option value="1.5rem" {{ ($tokens['--radius-base'] ?? '') === '1.5rem' ? 'selected' : '' }}>انحناء بارز (Super Rounded - 1.5rem / 24px)</option>
                            </x-admin::form.control-group.control>
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>

                <!-- 6. Semantic Status Colors Accordion -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header>
                        <div class="flex items-center gap-2 p-1.5">
                            <span class="icon-notification text-xl text-blue-600"></span>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                ألوان الحالات والنظام (Semantic States)
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1 pt-1">
                            <!-- Success -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.color-success')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--color-success'] ?? '#10b981' }}"
                                        oninput="document.getElementById('input-color-success').value = this.value; updatePreviewToken('--color-success', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-color-success"
                                        name="tokens[--color-success]"
                                        :value="$tokens['--color-success'] ?? '#10b981'"
                                        placeholder="#10b981"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--color-success', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Warning -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.color-warning')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--color-warning'] ?? '#f59e0b' }}"
                                        oninput="document.getElementById('input-color-warning').value = this.value; updatePreviewToken('--color-warning', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-color-warning"
                                        name="tokens[--color-warning]"
                                        :value="$tokens['--color-warning'] ?? '#f59e0b'"
                                        placeholder="#f59e0b"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--color-warning', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Danger -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.color-danger')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--color-danger'] ?? '#ef4444' }}"
                                        oninput="document.getElementById('input-color-danger').value = this.value; updatePreviewToken('--color-danger', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-color-danger"
                                        name="tokens[--color-danger]"
                                        :value="$tokens['--color-danger'] ?? '#ef4444'"
                                        placeholder="#ef4444"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--color-danger', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Info -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.color-info')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--color-info'] ?? '#1c66f6' }}"
                                        oninput="document.getElementById('input-color-info').value = this.value; updatePreviewToken('--color-info', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-color-info"
                                        name="tokens[--color-info]"
                                        :value="$tokens['--color-info'] ?? '#1c66f6'"
                                        placeholder="#1c66f6"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--color-info', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>
                        </div>
                    </x-slot>
                </x-admin::accordion>

                <!-- 7. Components & Structural Styling Accordion -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header>
                        <div class="flex items-center gap-2 p-1.5">
                            <span class="icon-menu text-xl text-blue-600"></span>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('portal::app.admin.tokens.tab-components')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1 pt-1">
                            <!-- Header Background -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.header-bg')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--header-bg'] ?? '#ffffff' }}"
                                        oninput="document.getElementById('input-header-bg').value = this.value; updatePreviewToken('--header-bg', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-header-bg"
                                        name="tokens[--header-bg]"
                                        :value="$tokens['--header-bg'] ?? '#ffffff'"
                                        placeholder="#ffffff"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--header-bg', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Header Text -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.header-text')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--header-text'] ?? '#090d16' }}"
                                        oninput="document.getElementById('input-header-text').value = this.value; updatePreviewToken('--header-text', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-header-text"
                                        name="tokens[--header-text]"
                                        :value="$tokens['--header-text'] ?? '#090d16'"
                                        placeholder="#090d16"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--header-text', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Footer Background -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.footer-bg')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--footer-bg'] ?? '#0f172a' }}"
                                        oninput="document.getElementById('input-footer-bg').value = this.value; updatePreviewToken('--footer-bg', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-footer-bg"
                                        name="tokens[--footer-bg]"
                                        :value="$tokens['--footer-bg'] ?? '#0f172a'"
                                        placeholder="#0f172a"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--footer-bg', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Footer Text -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.footer-text')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--footer-text'] ?? '#94a3b8' }}"
                                        oninput="document.getElementById('input-footer-text').value = this.value; updatePreviewToken('--footer-text', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-footer-text"
                                        name="tokens[--footer-text]"
                                        :value="$tokens['--footer-text'] ?? '#94a3b8'"
                                        placeholder="#94a3b8"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--footer-text', this.value);"
                                    />
                                </div>
                            </x-admin::form.control-group>

                            <!-- Button Radius -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.btn-radius')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="tokens[--btn-radius]"
                                    :value="$tokens['--btn-radius'] ?? '0.5rem'"
                                    class="cursor-pointer text-xs"
                                    onchange="updatePreviewToken('--btn-radius', this.value);"
                                >
                                    <option value="0rem" {{ ($tokens['--btn-radius'] ?? '') === '0rem' ? 'selected' : '' }}>مستطيل حاد (Square - 0px)</option>
                                    <option value="0.375rem" {{ ($tokens['--btn-radius'] ?? '') === '0.375rem' ? 'selected' : '' }}>انحناء خفيف (Rounded - 6px)</option>
                                    <option value="0.5rem" {{ ($tokens['--btn-radius'] ?? '') === '0.5rem' || empty($tokens['--btn-radius']) ? 'selected' : '' }}>انحناء قياسي (Standard - 8px)</option>
                                    <option value="0.75rem" {{ ($tokens['--btn-radius'] ?? '') === '0.75rem' ? 'selected' : '' }}>انحناء ناعم (Smooth - 12px)</option>
                                    <option value="9999px" {{ ($tokens['--btn-radius'] ?? '') === '9999px' ? 'selected' : '' }}>كبسولة دائرية (Pill - 9999px)</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <!-- Card Radius -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.card-radius')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="tokens[--card-radius]"
                                    :value="$tokens['--card-radius'] ?? '1rem'"
                                    class="cursor-pointer text-xs"
                                    onchange="updatePreviewToken('--card-radius', this.value);"
                                >
                                    <option value="0rem" {{ ($tokens['--card-radius'] ?? '') === '0rem' ? 'selected' : '' }}>حواف حادة (Square - 0px)</option>
                                    <option value="0.5rem" {{ ($tokens['--card-radius'] ?? '') === '0.5rem' ? 'selected' : '' }}>انحناء خفيف (8px)</option>
                                    <option value="0.75rem" {{ ($tokens['--card-radius'] ?? '') === '0.75rem' ? 'selected' : '' }}>انحناء متوسط (12px)</option>
                                    <option value="1rem" {{ ($tokens['--card-radius'] ?? '') === '1rem' || empty($tokens['--card-radius']) ? 'selected' : '' }}>انحناء بطاقات قياسي (16px)</option>
                                    <option value="1.5rem" {{ ($tokens['--card-radius'] ?? '') === '1.5rem' ? 'selected' : '' }}>انحناء عريض بارز (24px)</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>
                        </div>
                    </x-slot>
                </x-admin::accordion>

                <!-- 8. Page Layout & Containers Accordion -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header>
                        <div class="flex items-center gap-2 p-1.5">
                            <span class="icon-dashboard text-xl text-indigo-600"></span>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('portal::app.admin.tokens.tab-layout')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1 pt-1">
                            <!-- Container Max Width -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.container-max-width')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="tokens[--container-max-width]"
                                    :value="$tokens['--container-max-width'] ?? '1280px'"
                                    class="cursor-pointer text-xs"
                                    onchange="updatePreviewToken('--container-max-width', this.value);"
                                >
                                    <option value="1200px" {{ ($tokens['--container-max-width'] ?? '') === '1200px' ? 'selected' : '' }}>مدمج (Compact - 1200px)</option>
                                    <option value="1280px" {{ ($tokens['--container-max-width'] ?? '') === '1280px' || empty($tokens['--container-max-width']) ? 'selected' : '' }}>قياسي (Standard - 1280px)</option>
                                    <option value="1440px" {{ ($tokens['--container-max-width'] ?? '') === '1440px' ? 'selected' : '' }}>عريض (Wide - 1440px)</option>
                                    <option value="1600px" {{ ($tokens['--container-max-width'] ?? '') === '1600px' ? 'selected' : '' }}>فائق العرض (Ultra Wide - 1600px)</option>
                                    <option value="100%" {{ ($tokens['--container-max-width'] ?? '') === '100%' ? 'selected' : '' }}>كامل الشاشة (Full Width - 100%)</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>

                            <!-- Page Horizontal Padding -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('portal::app.admin.tokens.page-padding-x')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="tokens[--page-padding-x]"
                                    :value="$tokens['--page-padding-x'] ?? '1rem'"
                                    class="cursor-pointer text-xs"
                                    onchange="updatePreviewToken('--page-padding-x', this.value);"
                                >
                                    <option value="0.75rem" {{ ($tokens['--page-padding-x'] ?? '') === '0.75rem' ? 'selected' : '' }}>ضيق (Narrow - 12px)</option>
                                    <option value="1rem" {{ ($tokens['--page-padding-x'] ?? '') === '1rem' || empty($tokens['--page-padding-x']) ? 'selected' : '' }}>قياسي (Standard - 16px)</option>
                                    <option value="1.5rem" {{ ($tokens['--page-padding-x'] ?? '') === '1.5rem' ? 'selected' : '' }}>مريح (Comfortable - 24px)</option>
                                    <option value="2rem" {{ ($tokens['--page-padding-x'] ?? '') === '2rem' ? 'selected' : '' }}>رحب (Spacious - 32px)</option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>
                        </div>
                    </x-slot>
                </x-admin::accordion>

                <!-- 9. Additional Custom CSS Accordion -->
                <x-admin::accordion :is-active="false">
                    <x-slot:header>
                        <div class="flex items-center gap-2 p-1.5">
                            <span class="icon-attribute text-xl text-cyan-600"></span>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('portal::app.admin.tokens.tab-custom-css')
                            </p>
                        </div>
                    </x-slot>

                    <x-slot:content>
                        <div class="space-y-3 pt-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('portal::app.admin.tokens.custom-css-desc')
                            </p>

                            <textarea
                                id="input-custom-css"
                                name="custom_css"
                                rows="8"
                                dir="ltr"
                                class="w-full rounded border border-gray-300 bg-gray-50 p-3 font-mono text-xs leading-relaxed text-gray-800 placeholder-gray-400 focus:border-blue-600 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200"
                                placeholder="@lang('portal::app.admin.tokens.custom-css-placeholder')"
                                oninput="updatePreviewCustomCss(this.value);"
                            >{{ $customCss ?? '' }}</textarea>
                        </div>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>

                    <!-- Preview Pane (Matching Bagisto Appearance Editor) -->
                    <div
                        class="box-shadow flex min-w-0 flex-1 flex-col overflow-hidden rounded bg-white dark:bg-gray-900"
                        id="portal-preview-pane"
                    >
                        <!-- Top Header with Preview Title & Device Switcher -->
                        <div class="flex shrink-0 items-center justify-between gap-2 border-b p-4 dark:border-gray-800">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                @lang('admin::app.appearance.sections.index.preview-btn')
                            </p>

                            <!-- Device Widths -->
                            <div class="flex items-center gap-1" id="portal-device-switcher">
                                <button
                                    type="button"
                                    id="btn-desktop"
                                    class="portal-device-btn rounded px-2.5 py-1 text-xs font-medium transition-all bg-blue-50 text-blue-700 dark:bg-gray-950 dark:text-blue-400"
                                    onclick="setPreviewDevice('desktop', this);"
                                >
                                    @lang('admin::app.appearance.sections.index.desktop')
                                </button>

                                <button
                                    type="button"
                                    id="btn-tablet"
                                    class="portal-device-btn rounded px-2.5 py-1 text-xs font-medium transition-all text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-950"
                                    onclick="setPreviewDevice('tablet', this);"
                                >
                                    @lang('admin::app.appearance.sections.index.tablet')
                                </button>

                                <button
                                    type="button"
                                    id="btn-mobile"
                                    class="portal-device-btn rounded px-2.5 py-1 text-xs font-medium transition-all text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-950"
                                    onclick="setPreviewDevice('mobile', this);"
                                >
                                    @lang('admin::app.appearance.sections.index.mobile')
                                </button>

                                <button
                                    type="button"
                                    id="btn-preview-dark-toggle"
                                    class="portal-device-btn flex items-center gap-1 rounded px-2 py-1 text-xs font-medium transition-all text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-950"
                                    onclick="togglePreviewDarkMode(this);"
                                    title="تبديل معاينة الوضع الداكن"
                                >
                                    <span class="icon-dark text-base"></span>
                                    <span id="preview-dark-text">الداكن</span>
                                </button>

                                <button
                                    type="button"
                                    class="icon-repeat flex h-6 w-6 items-center justify-center rounded text-base text-gray-500 transition-all hover:bg-gray-100 dark:hover:bg-gray-950"
                                    title="@lang('admin::app.appearance.sections.edit.preview')"
                                    onclick="document.getElementById('portal-preview-frame').contentWindow.location.reload();"
                                ></button>
                            </div>
                        </div>

                        <!-- Stage -->
                        <div
                            class="relative min-h-0 flex-1 overflow-hidden bg-gray-100 p-4 dark:bg-gray-950"
                            id="portal-stage"
                        >
                            <div
                                id="portal-frame-box"
                                dir="ltr"
                                class="absolute overflow-hidden rounded bg-white transition-all duration-300 ease-in-out shadow-sm"
                            >
                                <iframe
                                    id="portal-preview-frame"
                                    class="border-0 bg-white"
                                    src="{{ $previewUrl }}"
                                    onload="injectTokensIntoFrame();"
                                ></iframe>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </x-admin::form>

    <!-- Hidden Preset Apply Form -->
    <form id="portal-apply-preset-form" action="{{ route('admin.portal.themes.tokens.apply-preset') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="channel_id" value="{{ $currentChannel->id }}" />
        <input type="hidden" name="theme_code" value="{{ $themeCode }}" />
        <input type="hidden" name="preset" id="portal-apply-preset-val" value="" />
    </form>

    <!-- Hidden Reset Tokens Form -->
    <form id="portal-reset-tokens-form" action="{{ route('admin.portal.themes.tokens.reset') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="channel_id" value="{{ $currentChannel->id }}" />
        <input type="hidden" name="theme_code" value="{{ $themeCode }}" />
    </form>

    @pushOnce('scripts')
        <script>
            let currentDevice = 'desktop';
            let isPreviewDark = false;
            const deviceWidths = { desktop: 1440, tablet: 768, mobile: 390 };
            const stagePadding = 16;

            function layoutPreviewFrame() {
                const stage = document.getElementById('portal-stage');
                const box = document.getElementById('portal-frame-box');
                const frame = document.getElementById('portal-preview-frame');
                if (! stage || ! box || ! frame) return;

                const stageWidth = Math.max(0, stage.clientWidth - (stagePadding * 2));
                const stageHeight = Math.max(0, stage.clientHeight - (stagePadding * 2));
                const targetWidth = deviceWidths[currentDevice] || 1440;

                const scale = Math.min(1, stageWidth / targetWidth);
                const boxWidth = Math.round(targetWidth * scale);

                box.style.width = boxWidth + 'px';
                box.style.top = stagePadding + 'px';
                box.style.bottom = stagePadding + 'px';
                box.style.left = '50%';
                box.style.transform = 'translateX(-50%)';

                frame.style.width = targetWidth + 'px';
                frame.style.height = (stageHeight / scale) + 'px';
                frame.style.transform = `scale(${scale})`;
                frame.style.transformOrigin = 'top left';
            }

            function setPreviewDevice(device, btn) {
                currentDevice = device;

                if (btn) {
                    document.querySelectorAll('.portal-device-btn:not(#btn-preview-dark-toggle)').forEach(b => {
                        b.className = 'portal-device-btn rounded px-2.5 py-1 text-xs font-medium transition-all text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-950';
                    });
                    btn.className = 'portal-device-btn rounded px-2.5 py-1 text-xs font-medium transition-all bg-blue-50 text-blue-700 dark:bg-gray-950 dark:text-blue-400';
                }

                layoutPreviewFrame();
            }

            function togglePreviewDarkMode(btn) {
                isPreviewDark = ! isPreviewDark;
                const frame = document.getElementById('portal-preview-frame');

                if (btn) {
                    if (isPreviewDark) {
                        btn.classList.add('bg-gray-900', 'text-white', 'dark:bg-white', 'dark:text-gray-900');
                        btn.classList.remove('text-gray-500');
                        const text = document.getElementById('preview-dark-text');
                        if (text) text.textContent = 'الفاتح';
                    } else {
                        btn.classList.remove('bg-gray-900', 'text-white', 'dark:bg-white', 'dark:text-gray-900');
                        btn.classList.add('text-gray-500');
                        const text = document.getElementById('preview-dark-text');
                        if (text) text.textContent = 'الداكن';
                    }
                }

                if (frame && frame.contentWindow) {
                    try {
                        frame.contentWindow.postMessage({
                            type: 'toggle-dark',
                            dark: isPreviewDark,
                        }, window.location.origin);
                    } catch (e) {}

                    try {
                        const doc = frame.contentDocument || frame.contentWindow?.document;
                        if (doc) {
                            doc.documentElement.classList.toggle('dark', isPreviewDark);
                            doc.documentElement.setAttribute('data-theme', isPreviewDark ? 'dark' : 'light');
                        }
                    } catch (e) {}
                }

                updateContrastBadge();
            }

            function onFontSelected(tokenName, fontValue) {
                updatePreviewToken(tokenName, fontValue);

                const match = fontValue.match(/['"]([^'"]+)['"]/);
                const fontName = match ? match[1].trim() : fontValue.split(',')[0].trim();
                const systemFonts = ['system-ui', '-apple-system', 'sans-serif', 'serif', 'monospace', 'inherit', 'initial'];

                if (! fontName || systemFonts.includes(fontName.toLowerCase())) {
                    return;
                }

                const fontUrl = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(fontName.replace(/ /g, '+'))}:wght@300;400;500;600;700;800&display=swap`;

                const frame = document.getElementById('portal-preview-frame');
                if (frame && frame.contentWindow) {
                    try {
                        frame.contentWindow.postMessage({
                            type: 'load-font',
                            url: fontUrl,
                        }, window.location.origin);
                    } catch (e) {}

                    try {
                        const doc = frame.contentDocument || frame.contentWindow?.document;
                        if (doc && ! doc.querySelector(`link[href="${fontUrl}"]`)) {
                            const link = doc.createElement('link');
                            link.rel = 'stylesheet';
                            link.href = fontUrl;
                            doc.head.appendChild(link);
                        }
                    } catch (e) {}
                }
            }

            function calculateLuminance(hex) {
                if (! hex || typeof hex !== 'string') return 1;
                hex = hex.replace('#', '').trim();
                if (hex.length === 3) {
                    hex = hex.split('').map(c => c + c).join('');
                }
                if (hex.length !== 6) return 1;

                const r = parseInt(hex.substring(0, 2), 16) / 255;
                const g = parseInt(hex.substring(2, 4), 16) / 255;
                const b = parseInt(hex.substring(4, 6), 16) / 255;

                const sRgb = [r, g, b].map(val => {
                    return val <= 0.03928 ? val / 12.92 : Math.pow((val + 0.055) / 1.055, 2.4);
                });

                return (0.2126 * sRgb[0]) + (0.7152 * sRgb[1]) + (0.0722 * sRgb[2]);
            }

            function calculateContrastRatio(colorA, colorB) {
                const lumA = calculateLuminance(colorA);
                const lumB = calculateLuminance(colorB);
                const brightest = Math.max(lumA, lumB);
                const darkest = Math.min(lumA, lumB);
                return (brightest + 0.05) / (darkest + 0.05);
            }

            function updateContrastBadge() {
                const badge = document.getElementById('brand-contrast-badge');
                if (! badge) return;

                const primaryInput = document.getElementById('input-brand-primary');
                const primaryColor = primaryInput ? primaryInput.value : '#1c66f6';

                const ratio = calculateContrastRatio(primaryColor, '#ffffff');
                const formatted = ratio.toFixed(1) + ':1';

                badge.className = 'rounded px-2 py-0.5 text-[10px] font-bold transition-all';

                if (ratio >= 7.0) {
                    badge.textContent = `AAA ${formatted}`;
                    badge.classList.add('bg-green-100', 'text-green-700', 'dark:bg-green-950', 'dark:text-green-300');
                    badge.title = `توافق ممتاز مع معايير الوصول (WCAG AAA - ${formatted}) عند الكتابة باللون الأبيض`;
                } else if (ratio >= 4.5) {
                    badge.textContent = `AA ${formatted}`;
                    badge.classList.add('bg-blue-100', 'text-blue-700', 'dark:bg-blue-950', 'dark:text-blue-300');
                    badge.title = `توافق قياسي مع معايير الوصول (WCAG AA - ${formatted}) عند الكتابة باللون الأبيض`;
                } else if (ratio >= 3.0) {
                    badge.textContent = `AA Large ${formatted}`;
                    badge.classList.add('bg-amber-100', 'text-amber-700', 'dark:bg-amber-950', 'dark:text-amber-300');
                    badge.title = `مقبول للعناوين الكبيرة فقط (WCAG AA Large - ${formatted}). قد يصعب قراءة النصوص الصغيرة`;
                } else {
                    badge.textContent = `ضعيف ${formatted}`;
                    badge.classList.add('bg-red-100', 'text-red-700', 'dark:bg-red-950', 'dark:text-red-300');
                    badge.title = `مخالف لمعايير الوصول (WCAG Fail - ${formatted}). التباين منخفض جداً`;
                }
            }

            async function applyPresetLive(selectEl) {
                const preset = selectEl.value;
                if (! preset) return;

                const form = document.getElementById('portal-apply-preset-form');
                if (! form) return;

                const formData = new FormData(form);
                formData.set('preset', preset);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.tokens) {
                            if (data.tokens.custom_css) {
                                const customCssInput = document.getElementById('input-custom-css');
                                if (customCssInput) {
                                    customCssInput.value = data.tokens.custom_css;
                                    updatePreviewCustomCss(data.tokens.custom_css);
                                }
                            }

                            for (const [key, value] of Object.entries(data.tokens)) {
                                if (key === 'custom_css') continue;
                                const inputs = document.querySelectorAll(`input[name="tokens[${key}]"], select[name="tokens[${key}]"]`);
                                inputs.forEach(input => {
                                    input.value = value;
                                    const companion = input.parentElement?.querySelector('input[type="color"], input[type="text"]');
                                    if (companion && companion !== input) {
                                        companion.value = value;
                                    }
                                });
                                updatePreviewToken(key, value);
                            }

                            const frame = document.getElementById('portal-preview-frame');
                            if (frame && frame.contentWindow) {
                                try {
                                    frame.contentWindow.postMessage({
                                        type: 'update-tokens',
                                        tokens: data.tokens,
                                    }, window.location.origin);
                                } catch (e) {}
                            }

                            updateContrastBadge();
                        }
                    } else {
                        document.getElementById('portal-apply-preset-val').value = preset;
                        form.submit();
                    }
                } catch (e) {
                    document.getElementById('portal-apply-preset-val').value = preset;
                    form.submit();
                }
            }

            function updatePreviewToken(key, value) {
                const frame = document.getElementById('portal-preview-frame');
                if (! frame) return;

                // 1. PostMessage communication (safe cross-window)
                if (frame.contentWindow) {
                    try {
                        frame.contentWindow.postMessage({
                            type: 'update-token',
                            name: key,
                            value: value,
                        }, window.location.origin);
                    } catch (e) {}
                }

                // 2. Direct DOM fallback
                try {
                    const doc = frame.contentDocument || frame.contentWindow?.document;
                    if (! doc) return;

                    let styleTag = doc.getElementById('portal-tokens-live-preview');
                    if (! styleTag) {
                        styleTag = doc.createElement('style');
                        styleTag.id = 'portal-tokens-live-preview';
                        doc.head.appendChild(styleTag);
                    }

                    doc.documentElement.style.setProperty(key, value);

                    if (key === '--header-bg') {
                        doc.querySelectorAll('header, .portal-header, [data-portal-header]').forEach(el => el.style.backgroundColor = value);
                    } else if (key === '--header-text') {
                        doc.querySelectorAll('header, .portal-header, [data-portal-header]').forEach(el => el.style.color = value);
                    } else if (key === '--footer-bg') {
                        doc.querySelectorAll('footer, .portal-footer, [data-portal-footer]').forEach(el => el.style.backgroundColor = value);
                    } else if (key === '--footer-text') {
                        doc.querySelectorAll('footer, .portal-footer, [data-portal-footer]').forEach(el => el.style.color = value);
                    } else if (key === '--card-radius') {
                        doc.querySelectorAll('.portal-card, [data-portal-card], .glass-card, [data-spotlight], .rounded-card').forEach(el => el.style.borderRadius = value);
                    } else if (key === '--btn-radius') {
                        doc.querySelectorAll('.btn-primary, .btn-secondary, .btn-accent, .primary-button, .secondary-button, input, select, textarea').forEach(el => el.style.borderRadius = value);
                    }
                } catch (e) {}
            }

            function updatePreviewCustomCss(css) {
                const frame = document.getElementById('portal-preview-frame');
                if (! frame) return;

                if (frame.contentWindow) {
                    try {
                        frame.contentWindow.postMessage({
                            type: 'custom-css',
                            css: css,
                        }, window.location.origin);
                    } catch (e) {}
                }

                try {
                    const doc = frame.contentDocument || frame.contentWindow?.document;
                    if (! doc) return;

                    let styleTag = doc.getElementById('portal-custom-css-live');
                    if (! styleTag) {
                        styleTag = doc.createElement('style');
                        styleTag.id = 'portal-custom-css-live';
                        doc.head.appendChild(styleTag);
                    }

                    styleTag.textContent = css || '';
                } catch (e) {}
            }

            function injectTokensIntoFrame() {
                const frame = document.getElementById('portal-preview-frame');
                if (! frame) return;

                const tokensMap = {};
                const inputs = document.querySelectorAll('input[name^="tokens["], select[name^="tokens["]');
                inputs.forEach(function (input) {
                    const match = input.name.match(/tokens\[(.*?)\]/);
                    if (match && match[1] && input.value) {
                        tokensMap[match[1]] = input.value;
                    }
                });

                if (frame.contentWindow) {
                    try {
                        frame.contentWindow.postMessage({
                            type: 'update-tokens',
                            tokens: tokensMap,
                        }, window.location.origin);
                    } catch (e) {}
                }

                try {
                    const doc = frame.contentDocument || frame.contentWindow?.document;
                    if (doc) {
                        for (const [k, v] of Object.entries(tokensMap)) {
                            doc.documentElement.style.setProperty(k, v);
                        }
                        if (tokensMap['--header-bg']) {
                            doc.querySelectorAll('header, .portal-header, [data-portal-header]').forEach(el => el.style.backgroundColor = tokensMap['--header-bg']);
                        }
                        if (tokensMap['--header-text']) {
                            doc.querySelectorAll('header, .portal-header, [data-portal-header]').forEach(el => el.style.color = tokensMap['--header-text']);
                        }
                        if (tokensMap['--footer-bg']) {
                            doc.querySelectorAll('footer, .portal-footer, [data-portal-footer]').forEach(el => el.style.backgroundColor = tokensMap['--footer-bg']);
                        }
                        if (tokensMap['--footer-text']) {
                            doc.querySelectorAll('footer, .portal-footer, [data-portal-footer]').forEach(el => el.style.color = tokensMap['--footer-text']);
                        }
                        if (tokensMap['--card-radius']) {
                            doc.querySelectorAll('.portal-card, [data-portal-card], .glass-card, [data-spotlight], .rounded-card').forEach(el => el.style.borderRadius = tokensMap['--card-radius']);
                        }
                        if (tokensMap['--btn-radius']) {
                            doc.querySelectorAll('.btn-primary, .btn-secondary, .btn-accent, .primary-button, .secondary-button, input, select, textarea').forEach(el => el.style.borderRadius = tokensMap['--btn-radius']);
                        }
                    }
                } catch (e) {}

                const customCssInput = document.getElementById('input-custom-css');
                if (customCssInput) {
                    updatePreviewCustomCss(customCssInput.value);
                }

                updateContrastBadge();
                layoutPreviewFrame();
            }

            document.addEventListener('DOMContentLoaded', () => {
                const stage = document.getElementById('portal-stage');
                if (stage && window.ResizeObserver) {
                    new ResizeObserver(() => layoutPreviewFrame()).observe(stage);
                }
                layoutPreviewFrame();
                updateContrastBadge();
            });

            window.addEventListener('resize', layoutPreviewFrame);
            setTimeout(layoutPreviewFrame, 150);
        </script>
    @endPushOnce
</x-admin::layouts>
