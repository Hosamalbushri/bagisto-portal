<x-portal::layouts>
    <x-slot:title>
        {{ ! empty($query) ? trans('portal::app.search.title-query', ['query' => $query]) : trans('portal::app.search.title') }}
    </x-slot>

    <x-portal::seo
        :title="! empty($query) ? trans('portal::app.search.title-query', ['query' => $query]) : trans('portal::app.search.title')"
        :description="trans('portal::app.search.description')"
    />

    <div class="portal-container mx-auto py-12">
        <!-- Search Header & Search Form -->
        <div class="text-center max-w-2xl mx-auto mb-10">
            <h1 class="text-3xl font-extrabold text-[var(--text-primary)] font-cairo sm:text-4xl">
                @lang('portal::app.search.heading')
            </h1>

            <p class="mt-2 text-sm text-[var(--text-secondary)]">
                @lang('portal::app.search.subheading')
            </p>

            <form action="{{ route('portal.search.index') }}" method="GET" class="mt-6 flex items-center gap-2">
                <div class="relative flex-1">
                    <span class="icon-search absolute start-4 top-1/2 -translate-y-1/2 text-lg text-[var(--text-muted)] pointer-events-none"></span>
                    <input
                        type="search"
                        name="query"
                        value="{{ $query }}"
                        placeholder="@lang('portal::app.search.placeholder')"
                        class="w-full ps-11 pe-4 py-3 rounded-[var(--btn-radius,0.75rem)] border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-[var(--text-primary)] placeholder-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-primary)]/40 focus:border-[var(--brand-primary)] transition-all text-sm"
                        required
                        minlength="2"
                    />
                </div>

                <x-portal::button
                    type="submit"
                    class="!px-6 !py-3 bg-[var(--brand-primary)] hover:bg-[var(--brand-primary-hover)] text-[var(--on-brand-primary,#ffffff)] font-medium rounded-[var(--btn-radius,0.75rem)] shadow-xs transition-colors shrink-0"
                >
                    @lang('portal::app.search.submit')
                </x-portal::button>
            </form>
        </div>

        <!-- Search Results Count Banner -->
        @if (! empty($query))
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-[var(--border-subtle)] text-xs sm:text-sm text-[var(--text-muted)]">
                <span>
                    @lang('portal::app.search.results-count', ['count' => $total, 'query' => $query])
                </span>

                @if ($total > 0)
                    <span class="font-mono text-xs text-[var(--brand-primary)]">
                        @lang('portal::app.search.showing-results', ['count' => $results->count(), 'total' => $total])
                    </span>
                @endif
            </div>
        @endif

        <!-- Results List -->
        @if ($results->isNotEmpty())
            <div class="space-y-4">
                @foreach ($results as $item)
                    <article
                        class="portal-card p-6 border border-[var(--border-subtle)] bg-[var(--bg-surface)] hover:bg-[var(--bg-surface-elevated)] hover:border-[var(--brand-primary)]/40 shadow-xs hover:shadow-md transition-all group"
                        data-portal-card
                        style="border-radius: var(--card-radius, 1rem);"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <a href="{{ $item['url'] }}" class="text-lg font-bold text-[var(--text-primary)] group-hover:text-[var(--brand-primary)] transition-colors line-clamp-1">
                                    {{ $item['title'] }}
                                </a>

                                <div class="mt-1 text-xs text-[var(--brand-primary)] font-mono dir-ltr truncate">
                                    {{ $item['url'] }}
                                </div>

                                @if (! empty($item['description']))
                                    <p class="mt-2.5 text-sm text-[var(--text-secondary)] line-clamp-2 leading-relaxed">
                                        {{ $item['description'] }}
                                    </p>
                                @endif
                            </div>

                            <a
                                href="{{ $item['url'] }}"
                                class="hidden sm:flex items-center justify-center w-9 h-9 rounded-[var(--btn-radius,0.5rem)] border border-[var(--border-subtle)] bg-[var(--bg-surface-muted)] text-[var(--text-secondary)] group-hover:text-white group-hover:bg-[var(--brand-primary)] group-hover:border-[var(--brand-primary)] transition-all shrink-0"
                                aria-label="{{ $item['title'] }}"
                            >
                                <span class="icon-arrow-right rtl:rotate-180 text-sm"></span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            @if ($results->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $results->links() }}
                </div>
            @endif
        @elseif (! empty($query))
            <!-- Empty Results State -->
            <div class="portal-card text-center py-16 px-4 border border-dashed border-[var(--border-subtle)] bg-[var(--bg-surface)]/50" data-portal-card style="border-radius: var(--card-radius, 1rem);">
                <div class="portal-card w-14 h-14 mx-auto bg-[var(--bg-surface-muted)] flex items-center justify-center text-[var(--text-muted)] text-2xl mb-4" data-portal-card style="border-radius: var(--card-radius, 1rem);">
                    <span class="icon-search"></span>
                </div>

                <h3 class="text-lg font-bold text-[var(--text-primary)]">
                    @lang('portal::app.search.no-results-found')
                </h3>

                <p class="mt-2 text-sm text-[var(--text-secondary)] max-w-md mx-auto">
                    @lang('portal::app.search.no-results-hint')
                </p>

                <div class="mt-6">
                    <a
                        href="{{ portal_home_url() }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[var(--btn-radius,0.5rem)] border border-[var(--border-subtle)] bg-[var(--bg-surface)] hover:bg-[var(--bg-surface-elevated)] text-sm font-medium text-[var(--text-primary)] transition-colors shadow-2xs"
                    >
                        <span class="icon-arrow-left rtl:rotate-180 text-xs"></span>
                        <span>@lang('portal::app.search.back-home')</span>
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-portal::layouts>

