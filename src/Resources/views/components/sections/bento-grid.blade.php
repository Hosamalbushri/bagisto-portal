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
            <!-- Bento Tile 1: Full-Stack Runtime Platforms (Wide 2-col span on LG) -->
            <x-portal::card :interactive="true" :spotlight="true" class="lg:col-span-2 flex flex-col justify-between group !p-6 sm:!p-8">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-mono font-bold tracking-wider text-[var(--brand-primary)]">
                            // CONTRACT_INTERFACE.TS
                        </span>
                        <x-portal::badge variant="muted">
                            {{ $card1Tag }}
                        </x-portal::badge>
                    </div>

                    <!-- Architectural TypeScript Contract Specimen -->
                    <div class="p-3.5 mb-6 rounded-xl bg-[var(--code-bg)] text-[var(--code-text)] font-mono text-xs border border-[var(--border-subtle)] overflow-x-auto shadow-inner">
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-white/10 text-[10px] text-slate-400">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500/80"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></span>
                            <span class="ms-1 font-sans">core/distributed/stream_engine.ts</span>
                        </div>
                        <div class="text-slate-400">// Compile-time validated runtime RPC contract</div>
                        <div class="mt-1"><span class="text-sky-400 font-semibold">export interface</span> <span class="text-amber-300">DistributedRuntime</span>&lt;<span class="text-emerald-400">TPayload</span>&gt; {</div>
                        <div class="ps-4 text-slate-300"><span class="text-slate-400">readonly</span> <span class="text-blue-300">clusterEpoch</span>: <span class="text-emerald-400">number</span>;</div>
                        <div class="ps-4 text-slate-300"><span class="text-blue-300">dispatch</span>(message: <span class="text-emerald-400">TPayload</span>): <span class="text-purple-300">Promise</span>&lt;<span class="text-amber-300">SLAState</span>&gt;;</div>
                        <div>}</div>
                    </div>

                    <h3 class="text-xl sm:text-2xl font-bold font-cairo text-[var(--text-primary)] mb-3 group-hover:text-[var(--brand-primary)] transition-colors duration-200">
                        {{ $card1Title }}
                    </h3>
                    <p class="text-sm sm:text-base text-[var(--text-secondary)] leading-relaxed max-w-xl mb-6">
                        {{ $card1Desc }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-[var(--border-subtle)]">
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">Vue 3</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">TypeScript</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">Node.js</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">PostgreSQL</span>
                </div>
            </x-portal::card>

            <!-- Bento Tile 2: Cloud Infrastructure & SRE -->
            <x-portal::card :interactive="true" :spotlight="true" class="flex flex-col justify-between group !p-6 sm:!p-8">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-mono font-bold tracking-wider text-sky-500">
                            // TOPOLOGY_FLOW
                        </span>
                        <x-portal::badge variant="muted">
                            {{ $card2Tag }}
                        </x-portal::badge>
                    </div>

                    <!-- Architectural Container Pipeline Schematic -->
                    <div class="p-3.5 mb-6 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] font-mono text-[11px] space-y-2">
                        <div class="flex items-center justify-between text-slate-400 text-[10px]">
                            <span>STAGE</span>
                            <span>METRIC</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)]">
                            <span class="text-[var(--text-primary)] font-semibold">1. OCI Immutable Build</span>
                            <span class="text-emerald-500 font-bold">PASS</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)]">
                            <span class="text-[var(--text-primary)] font-semibold">2. Gateway Mesh TLS</span>
                            <span class="text-[var(--brand-primary)] font-bold">&lt;2ms</span>
                        </div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)]">
                            <span class="text-[var(--text-primary)] font-semibold">3. Multi-Region Replica</span>
                            <span class="text-[var(--brand-accent)] font-semibold">TOPOLOGY SPEC</span>
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
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">Docker</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">AWS</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">Kubernetes</span>
                </div>
            </x-portal::card>

            <!-- Bento Tile 3: Vector Retrieval & Data Pipelines -->
            <x-portal::card :interactive="true" :spotlight="true" class="flex flex-col justify-between group !p-6 sm:!p-8">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-mono font-bold tracking-wider text-[var(--brand-accent)]">
                            // VECTOR_INDEX
                        </span>
                        <x-portal::badge variant="accent">
                            {{ $card3Tag }}
                        </x-portal::badge>
                    </div>

                    <!-- Architectural Data Flow Diagram -->
                    <div class="p-3.5 mb-6 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] font-mono text-[11px] space-y-1.5">
                        <div class="text-[10px] text-slate-400 mb-1">// Vector Retrieval Pipeline</div>
                        <div class="flex items-center gap-2">
                            <span class="px-1.5 py-0.5 rounded bg-[var(--brand-accent-light)] text-[var(--brand-accent)] font-bold text-[10px]">INGEST</span>
                            <span class="text-[var(--text-secondary)] text-[10px]">Raw Chunk Stream</span>
                        </div>
                        <div class="text-center text-slate-400 text-[10px]">↓ HNSW Index (Cosine)</div>
                        <div class="flex items-center justify-between p-1.5 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)]">
                            <span class="text-[var(--text-primary)] text-[10px]">Synthetic Benchmark P99</span>
                            <span class="text-[var(--brand-accent)] font-bold">11.8ms</span>
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
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">PyTorch</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">HNSW</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">Vector DB</span>
                </div>
            </x-portal::card>

            <!-- Bento Tile 4: Bilingual Engineering Design Systems (Wide 2-col span on LG) -->
            <x-portal::card :interactive="true" :spotlight="true" class="lg:col-span-2 flex flex-col justify-between group !p-6 sm:!p-8">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-mono font-bold tracking-wider text-purple-500">
                            // TYPOGRAPHY_CONTRAST_SPEC
                        </span>
                        <x-portal::badge variant="muted">
                            {{ $card4Tag }}
                        </x-portal::badge>
                    </div>

                    <!-- Architectural Typography & Bi-Directional Specimen -->
                    <div class="p-3.5 mb-6 rounded-xl bg-[var(--bg-surface-elevated)] border border-[var(--border-subtle)] grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-3 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)]">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-mono text-slate-400">LATIN // INTER</span>
                                <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">AAA CONTRAST</span>
                            </div>
                            <div class="text-base font-bold text-[var(--text-primary)]">Geometric Precision</div>
                            <div class="text-xs text-[var(--text-secondary)] font-mono">tracking-tight • 600 weight</div>
                        </div>

                        <div class="p-3 rounded-lg bg-[var(--bg-surface)] border border-[var(--border-subtle)]">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-mono text-slate-400">ARABIC // CAIRO</span>
                                <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">AAA CONTRAST</span>
                            </div>
                            <div class="text-base font-bold font-cairo text-[var(--text-primary)]">تناسق طباعي أصيل</div>
                            <div class="text-xs text-[var(--text-secondary)] font-mono">normal tracking • 1.35 leading</div>
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
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">WCAG 2.2 AAA</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">RTL Symmetry</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">WebGPU</span>
                    <span class="text-xs px-2.5 py-1 rounded-md bg-[var(--bg-surface-elevated)] text-[var(--text-secondary)] font-mono">Design Tokens</span>
                </div>
            </x-portal::card>
        </div>
    </div>
</section>

