@props(['options' => []])

@php
    $badgeText = $options['badge_text'] ?? trans('portal::app.sections.hero_scaffold.default_badge');
    $badgeVersion = $options['badge_version'] ?? trans('portal::app.sections.hero_scaffold.default_version');
    $headlinePrefix = $options['headline_prefix'] ?? trans('portal::app.sections.hero_scaffold.default_prefix');
    $headlineHighlight = $options['headline_highlight'] ?? trans('portal::app.sections.hero_scaffold.default_highlight');
    $headlineSuffix = $options['headline_suffix'] ?? trans('portal::app.sections.hero_scaffold.default_suffix');
    $description = $options['description'] ?? trans('portal::app.sections.hero_scaffold.default_description');
    $primaryCtaText = $options['primary_cta_text'] ?? trans('portal::app.sections.hero_scaffold.default_primary_cta');
    $primaryCtaUrl = $options['primary_cta_url'] ?? '#contact';
    $secondaryCtaText = $options['secondary_cta_text'] ?? trans('portal::app.sections.hero_scaffold.default_secondary_cta');
    $secondaryCtaUrl = $options['secondary_cta_url'] ?? '#services';
    $metrics = $options['metrics'] ?? [
        [
            'code' => trans('portal::app.sections.hero_scaffold.metric_1_code'),
            'value' => trans('portal::app.sections.hero_scaffold.metric_1_value'),
            'label' => trans('portal::app.sections.hero_scaffold.metric_1_label'),
            'status_color' => 'bg-[var(--brand-primary)]',
        ],
        [
            'code' => trans('portal::app.sections.hero_scaffold.metric_2_code'),
            'value' => trans('portal::app.sections.hero_scaffold.metric_2_value'),
            'label' => trans('portal::app.sections.hero_scaffold.metric_2_label'),
            'status_color' => 'bg-[var(--brand-accent)]',
        ],
        [
            'code' => trans('portal::app.sections.hero_scaffold.metric_3_code'),
            'value' => trans('portal::app.sections.hero_scaffold.metric_3_value'),
            'label' => trans('portal::app.sections.hero_scaffold.metric_3_label'),
            'status_color' => 'bg-emerald-500',
        ],
    ];
@endphp

<section class="relative min-h-[580px] lg:min-h-[660px] flex items-center overflow-hidden border-b border-[var(--border-subtle)] bg-[var(--bg-app)] text-[var(--text-primary)] transition-colors duration-200" v-pre>
    <!-- Visual Effect Background Layer: Zero-CPU Blueprint Grid & Radial Glows -->
    <x-portal::shader-fallback />

    <!-- Content Container -->
    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
        <div class="max-w-4xl mx-auto text-center flex flex-col items-center">
            <!-- Technical System Coordinates Badge -->
            <div class="mb-6 flex items-center justify-center gap-3">
                @if ($badgeText)
                    <x-portal::badge variant="accent" :dot="true">
                        {{ $badgeText }}
                    </x-portal::badge>
                @endif

                @if ($badgeVersion)
                    <span class="text-xs font-mono text-[var(--text-muted)] tracking-wider hidden sm:inline">
                        {{ $badgeVersion }}
                    </span>
                @endif
            </div>

            <!-- Primary Display Headline: High-contrast architectural typography -->
            <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-extrabold tracking-tight mb-6 leading-tight text-[var(--text-primary)] font-cairo text-center">
                @if ($headlinePrefix)
                    <span>{{ $headlinePrefix }} </span>
                @endif

                @if ($headlineHighlight)
                    <span class="text-[var(--brand-primary)]">
                        {{ $headlineHighlight }}
                    </span>
                @endif

                <br class="hidden sm:inline" />

                @if ($headlineSuffix)
                    <span> {{ $headlineSuffix }}</span>
                @endif
            </h1>

            <!-- Subtitle & Value Proposition -->
            @if ($description)
                <p class="text-base sm:text-lg lg:text-xl text-[var(--text-secondary)] mb-8 max-w-2xl mx-auto leading-relaxed text-center">
                    {{ $description }}
                </p>
            @endif

            <!-- Action CTAs -->
            <div class="flex flex-wrap items-center justify-center gap-4 mb-12 w-full max-w-md sm:max-w-none">
                @if ($primaryCtaText)
                    <a href="{{ portal_sanitize_url($primaryCtaUrl) }}" class="btn-primary !px-7 !py-3.5 text-base flex items-center justify-center gap-2 group w-full sm:w-auto">
                        <span>{{ $primaryCtaText }}</span>
                        <span class="icon-arrow-right rtl:rotate-180 group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform" aria-hidden="true"></span>
                    </a>
                @endif

                @if ($secondaryCtaText)
                    <a href="{{ portal_sanitize_url($secondaryCtaUrl) }}" class="btn-outline !px-7 !py-3.5 text-base flex items-center justify-center w-full sm:w-auto">
                        <span>{{ $secondaryCtaText }}</span>
                    </a>
                @endif
            </div>

            <!-- Structural Architecture Metrics Strip -->
            @if (! empty($metrics))
                @php
                    $allowedStatusColors = [
                        'bg-[var(--brand-primary)]' => 'bg-[var(--brand-primary)]',
                        'bg-[var(--brand-accent)]' => 'bg-[var(--brand-accent)]',
                        'bg-emerald-500' => 'bg-emerald-500',
                        'bg-blue-500' => 'bg-blue-500',
                        'bg-amber-500' => 'bg-amber-500',
                        'bg-red-500' => 'bg-red-500',
                        'primary' => 'bg-[var(--brand-primary)]',
                        'accent' => 'bg-[var(--brand-accent)]',
                        'emerald' => 'bg-emerald-500',
                        'green' => 'bg-emerald-500',
                        'blue' => 'bg-blue-500',
                        'red' => 'bg-red-500',
                    ];
                @endphp
                <div class="w-full grid grid-cols-1 sm:grid-cols-3 gap-4 pt-8 border-t border-[var(--border-subtle)]">
                    @foreach ($metrics as $metric)
                        @php
                            $statusColor = $allowedStatusColors[$metric['status_color'] ?? ''] ?? 'bg-[var(--brand-primary)]';
                        @endphp
                        <div class="p-4 rounded-xl bg-[var(--bg-surface)] border border-[var(--border-subtle)] shadow-xs transition-all hover:border-[var(--brand-primary)]/40 hover:-translate-y-0.5 text-center" data-spotlight>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-mono tracking-wider uppercase text-[var(--text-muted)]">
                                    {{ $metric['code'] ?? '// METRIC' }}
                                </span>
                                <span class="w-2 h-2 rounded-full {{ $statusColor }}"></span>
                            </div>
                            <div class="text-2xl lg:text-3xl font-bold font-mono text-[var(--text-primary)]">
                                {{ $metric['value'] ?? '' }}
                            </div>
                            <div class="text-xs text-[var(--text-secondary)] mt-1 font-medium">
                                {{ $metric['label'] ?? '' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

