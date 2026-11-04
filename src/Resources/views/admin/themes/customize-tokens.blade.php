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
                            <!-- Primary Color -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('portal::app.admin.tokens.brand-primary')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded border border-gray-300 bg-transparent p-0.5 dark:border-gray-700"
                                        value="{{ $tokens['--brand-primary'] ?? '#1c66f6' }}"
                                        oninput="document.getElementById('input-brand-primary').value = this.value; updatePreviewToken('--brand-primary', this.value);"
                                    />
                                    <x-admin::form.control-group.control
                                        type="text"
                                        id="input-brand-primary"
                                        name="tokens[--brand-primary]"
                                        :value="$tokens['--brand-primary'] ?? '#1c66f6'"
                                        placeholder="#1c66f6"
                                        class="font-mono text-xs"
                                        oninput="updatePreviewToken('--brand-primary', this.value);"
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
                                    onchange="updatePreviewToken('--font-arabic', this.value);"
                                >
                                    <option value="'Cairo', system-ui, sans-serif" {{ ($tokens['--font-arabic'] ?? '') === "'Cairo', system-ui, sans-serif" ? 'selected' : '' }}>Cairo (كايرو - الافتراضي)</option>
                                    <option value="'Tajawal', system-ui, sans-serif" {{ ($tokens['--font-arabic'] ?? '') === "'Tajawal', system-ui, sans-serif" ? 'selected' : '' }}>Tajawal (تجوال)</option>
                                    <option value="'Almarai', system-ui, sans-serif" {{ ($tokens['--font-arabic'] ?? '') === "'Almarai', system-ui, sans-serif" ? 'selected' : '' }}>Almarai (المراعي)</option>
                                    <option value="'IBM Plex Sans Arabic', system-ui, sans-serif" {{ ($tokens['--font-arabic'] ?? '') === "'IBM Plex Sans Arabic', system-ui, sans-serif" ? 'selected' : '' }}>IBM Plex Sans Arabic</option>
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
                                    onchange="updatePreviewToken('--font-latin', this.value);"
                                >
                                    <option value="'Inter', system-ui, sans-serif" {{ ($tokens['--font-latin'] ?? '') === "'Inter', system-ui, sans-serif" ? 'selected' : '' }}>Inter (الافتراضي)</option>
                                    <option value="'Plus Jakarta Sans', system-ui, sans-serif" {{ ($tokens['--font-latin'] ?? '') === "'Plus Jakarta Sans', system-ui, sans-serif" ? 'selected' : '' }}>Plus Jakarta Sans</option>
                                    <option value="'Roboto', system-ui, sans-serif" {{ ($tokens['--font-latin'] ?? '') === "'Roboto', system-ui, sans-serif" ? 'selected' : '' }}>Roboto</option>
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
                </x-admin::accordion>                        </div>
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
                    document.querySelectorAll('.portal-device-btn').forEach(b => {
                        b.className = 'portal-device-btn rounded px-2.5 py-1 text-xs font-medium transition-all text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-950';
                    });
                    btn.className = 'portal-device-btn rounded px-2.5 py-1 text-xs font-medium transition-all bg-blue-50 text-blue-700 dark:bg-gray-950 dark:text-blue-400';
                }

                layoutPreviewFrame();
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
                            for (const [key, value] of Object.entries(data.tokens)) {
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
                } catch (e) {}
            }

            function injectTokensIntoFrame() {
                const frame = document.getElementById('portal-preview-frame');
                if (! frame) return;

                try {
                    const doc = frame.contentDocument || frame.contentWindow?.document;
                    if (! doc) return;

                    const inputs = document.querySelectorAll('input[name^="tokens["], select[name^="tokens["]');
                    inputs.forEach(function (input) {
                        const match = input.name.match(/tokens\[(.*?)\]/);
                        if (match && match[1] && input.value) {
                            doc.documentElement.style.setProperty(match[1], input.value);
                        }
                    });
                } catch (e) {}

                layoutPreviewFrame();
            }

            document.addEventListener('DOMContentLoaded', () => {
                const stage = document.getElementById('portal-stage');
                if (stage && window.ResizeObserver) {
                    new ResizeObserver(() => layoutPreviewFrame()).observe(stage);
                }
                layoutPreviewFrame();
            });

            window.addEventListener('resize', layoutPreviewFrame);
            setTimeout(layoutPreviewFrame, 150);
        </script>
    @endPushOnce
</x-admin::layouts>
