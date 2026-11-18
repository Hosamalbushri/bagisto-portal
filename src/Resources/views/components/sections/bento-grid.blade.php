@props(['options' => []])

@php
    $badge = $options['badge'] ?? trans('portal::app.sections.bento_grid.default_badge');
    $heading = $options['heading'] ?? trans('portal::app.sections.bento_grid.default_heading');
    $subheading = $options['subheading'] ?? trans('portal::app.sections.bento_grid.default_subheading');

    $card1Title = $options['card_1_title'] ?? trans('portal::app.sections.bento_grid.card_1_title');
    $card1Desc = $options['card_1_desc'] ?? trans('portal::app.sections.bento_grid.card_1_desc');
    $card1Tag = $options['card_1_tag'] ?? trans('portal::app.sections.bento_grid.card_1_tag');

    $card2Title = $options['card_2_title'] ?? trans('portal::app.sections.bento_grid.card_2_title');
    $card2Desc = $options['card_2_desc'] ?? trans('portal::app.sections.bento_grid.card_2_desc');
    $card2Tag = $options['card_2_tag'] ?? trans('portal::app.sections.bento_grid.card_2_tag');

    $card3Title = $options['card_3_title'] ?? trans('portal::app.sections.bento_grid.card_3_title');
    $card3Desc = $options['card_3_desc'] ?? trans('portal::app.sections.bento_grid.card_3_desc');
    $card3Tag = $options['card_3_tag'] ?? trans('portal::app.sections.bento_grid.card_3_tag');

    $card4Title = $options['card_4_title'] ?? trans('portal::app.sections.bento_grid.card_4_title');
    $card4Desc = $options['card_4_desc'] ?? trans('portal::app.sections.bento_grid.card_4_desc');
    $card4Tag = $options['card_4_tag'] ?? trans('portal::app.sections.bento_grid.card_4_tag');
@endphp

<section class="py-20 lg:py-28 bg-[var(--bg-app)] border-b border-[var(--border-subtle)] transition-colors duration-200" v-pre>
    <div class="portal-container mx-auto">
        <!-- Section Header -->
        <div class="max-w-3xl mb-14">
            @if ($badge)
                <x-portal::badge variant="primary" class="mb-4">
                    {{ $badge }}
                </x-portal::badge>
            @endif

            @if ($heading)
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[var(--text-primary)] font-cairo mb-4 leading-snug">
                    {{ $heading }}
                </h2>
            @endif

            @if ($subheading)
                <p class="text-base sm:text-lg text-[var(--text-secondary)] leading-relaxed">
                    {{ $subheading }}
                </p>
            @endif
        </div>

        <!-- Bento Box Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Bento Tile 1: Strategic Planning & Execution (Wide 2-col span on LG) -->
            <x-portal::card :interactive="true" :spotlight="true" class="lg:col-span-2 flex flex-col justify-between group !p-6 sm:!p-8">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[var(--brand-primary)]"></span>
                            <span class="text-xs font-mono font-bold tracking-wider text-[var(--brand-primary)] uppercase">
                                {{ $card1Tag }}
                            </span>
                        </div>
                        <x-portal::badge variant="primary">
                            @lang('portal::app.sections.bento_grid.card_1_badge')
                        </x-portal::badge>
                    </div>

                    <!-- Milestone Tracking Preview Specimen -->
                    <div class="p-4 mb-6 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] space-y-2.5">
                        <div class="flex items-center justify-between text-xs pb-2 border-b border-[var(--border-subtle)]">
                            <span class="font-medium text-[var(--text-primary)]">@lang('portal::app.sections.bento_grid.card_1_specimen_title')</span>
                            <span class="text-[10px] font-mono text-[var(--brand-primary)] font-semibold">100% READY</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-[var(--text-secondary)]">
                            <div class="flex items-center gap-2 p-2 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)]">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>@lang('portal::app.sections.bento_grid.card_1_item_1')</span>
                            </div>
                            <div class="flex items-center gap-2 p-2 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)]">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>@lang('portal::app.sections.bento_grid.card_1_item_2')</span>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-xl sm:text-2xl font-bold font-cairo text-[var(--text-primary)] mb-3 group-hover:text-[var(--brand-primary)] transition-colors duration-200">
                        {{ $card1Title }}
                    </h3>
                    <p class="text-sm sm:text-base text-[var(--text-secondary)] leading-relaxed max-w-xl mb-6">
                        {{ $card1Desc }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-[var(--border-subtle)]">
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-medium">@lang('portal::app.sections.bento_grid.tag_reliable')</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-medium">@lang('portal::app.sections.bento_grid.tag_scalable')</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-medium">@lang('portal::app.sections.bento_grid.tag_secure')</span>
                </div>
            </x-portal::card>

            <!-- Bento Tile 2: Operational Agility & Performance -->
            <x-portal::card :interactive="true" :spotlight="true" class="flex flex-col justify-between group !p-6 sm:!p-8">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                            <span class="text-xs font-mono font-bold tracking-wider text-sky-500 uppercase">
                                {{ $card2Tag }}
                            </span>
                        </div>
                        <x-portal::badge variant="muted">
                            @lang('portal::app.sections.bento_grid.card_2_badge')
                        </x-portal::badge>
                    </div>

                    <!-- Operational Performance Schematic -->
                    <div class="p-3.5 mb-6 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] text-xs space-y-2">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)]">
                            <span class="text-[var(--text-primary)] font-medium">@lang('portal::app.sections.bento_grid.card_2_stage_1')</span>
                            <span class="text-emerald-500 font-bold font-mono text-[11px]">PASS</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)]">
                            <span class="text-[var(--text-primary)] font-medium">@lang('portal::app.sections.bento_grid.card_2_stage_2')</span>
                            <span class="text-[var(--brand-primary)] font-bold font-mono text-[11px]">&lt;2ms</span>
                        </div>
                    </div>

                    <h3 class="text-xl font-bold font-cairo text-[var(--text-primary)] mb-3 group-hover:text-[var(--brand-primary)] transition-colors duration-200">
                        {{ $card2Title }}
                    </h3>
                    <p class="text-sm text-[var(--text-secondary)] leading-relaxed mb-6">
                        {{ $card2Desc }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-[var(--border-subtle)]">
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-medium">@lang('portal::app.sections.bento_grid.tag_automation')</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-medium">@lang('portal::app.sections.bento_grid.tag_performance')</span>
                </div>
            </x-portal::card>

            <!-- Bento Tile 3: Actionable Insights & Data Intelligence -->
            <x-portal::card :interactive="true" :spotlight="true" class="flex flex-col justify-between group !p-6 sm:!p-8">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[var(--brand-accent)]"></span>
                            <span class="text-xs font-mono font-bold tracking-wider text-[var(--brand-accent)] uppercase">
                                {{ $card3Tag }}
                            </span>
                        </div>
                        <x-portal::badge variant="accent">
                            @lang('portal::app.sections.bento_grid.card_3_badge')
                        </x-portal::badge>
                    </div>

                    <!-- Growth & Intelligence Progress Visual -->
                    <div class="p-3.5 mb-6 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] space-y-2">
                        <div class="flex items-center justify-between text-xs text-[var(--text-secondary)]">
                            <span class="font-medium text-[var(--text-primary)]">@lang('portal::app.sections.bento_grid.card_3_metric_label')</span>
                            <span class="text-[var(--brand-accent)] font-bold font-mono">+45%</span>
                        </div>
                        <div class="w-full bg-[var(--bg-surface)] rounded-full h-2 overflow-hidden border border-[var(--border-subtle)]">
                            <div class="bg-[var(--brand-accent)] h-full rounded-full" style="width: 85%"></div>
                        </div>
                        <div class="text-[11px] text-[var(--text-muted)] text-center pt-1">
                            @lang('portal::app.sections.bento_grid.card_3_subtext')
                        </div>
                    </div>

                    <h3 class="text-xl font-bold font-cairo text-[var(--text-primary)] mb-3 group-hover:text-[var(--brand-accent)] transition-colors duration-200">
                        {{ $card3Title }}
                    </h3>
                    <p class="text-sm text-[var(--text-secondary)] leading-relaxed mb-6">
                        {{ $card3Desc }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-[var(--border-subtle)]">
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-medium">@lang('portal::app.sections.bento_grid.tag_analytics')</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-medium">@lang('portal::app.sections.bento_grid.tag_accuracy')</span>
                </div>
            </x-portal::card>

            <!-- Bento Tile 4: Inclusive Experience & Design Harmony (Wide 2-col span on LG) -->
            <x-portal::card :interactive="true" :spotlight="true" class="lg:col-span-2 flex flex-col justify-between group !p-6 sm:!p-8">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            <span class="text-xs font-mono font-bold tracking-wider text-purple-500 uppercase">
                                {{ $card4Tag }}
                            </span>
                        </div>
                        <x-portal::badge variant="muted">
                            AAA CONTRAST
                        </x-portal::badge>
                    </div>

                    <!-- Bilingual Typography & Accessibility Harmony -->
                    <div class="p-3.5 mb-6 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-3 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)]">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-mono text-slate-400">LATIN // INTER</span>
                                <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">WCAG AAA</span>
                            </div>
                            <div class="text-base font-bold text-[var(--text-primary)]">@lang('portal::app.sections.bento_grid.typography_latin_sample')</div>
                            <div class="text-xs text-[var(--text-secondary)] font-mono">tracking-tight • fluid scaling</div>
                        </div>

                        <div class="p-3 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)]">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-mono text-slate-400">ARABIC // CAIRO</span>
                                <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">WCAG AAA</span>
                            </div>
                            <div class="text-base font-bold font-cairo text-[var(--text-primary)]">@lang('portal::app.sections.bento_grid.typography_arabic_sample')</div>
                            <div class="text-xs text-[var(--text-secondary)] font-mono">تناغم بصري أصيل • قراءة مريحة</div>
                        </div>
                    </div>

                    <h3 class="text-xl sm:text-2xl font-bold font-cairo text-[var(--text-primary)] mb-3 group-hover:text-[var(--brand-primary)] transition-colors duration-200">
                        {{ $card4Title }}
                    </h3>
                    <p class="text-sm sm:text-base text-[var(--text-secondary)] leading-relaxed max-w-xl mb-6">
                        {{ $card4Desc }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-[var(--border-subtle)]">
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-medium">@lang('portal::app.sections.bento_grid.tag_responsive')</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-medium">@lang('portal::app.sections.bento_grid.tag_accessible')</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-medium">@lang('portal::app.sections.bento_grid.tag_bilingual')</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-medium">@lang('portal::app.sections.bento_grid.tag_fast')</span>
                </div>
            </x-portal::card>
        </div>
    </div>
</section>
