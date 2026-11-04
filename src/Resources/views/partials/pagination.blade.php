@if ($paginator->hasPages())
    <nav class="flex items-center justify-between border-t border-[var(--border-subtle)] px-4 py-3 sm:px-6">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center rounded-xl border border-[var(--border-subtle)] px-4 py-2 text-sm font-medium text-[var(--text-muted)] opacity-50 cursor-not-allowed">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] px-4 py-2 text-sm font-medium text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)] hover:text-[var(--text-primary)] transition-colors">Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative ml-3 inline-flex items-center rounded-xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] px-4 py-2 text-sm font-medium text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)] hover:text-[var(--text-primary)] transition-colors">Next</a>
            @else
                <span class="relative ml-3 inline-flex items-center rounded-xl border border-[var(--border-subtle)] px-4 py-2 text-sm font-medium text-[var(--text-muted)] opacity-50 cursor-not-allowed">Next</span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[var(--text-secondary)]">
                    Showing <span class="font-medium text-[var(--text-primary)]">{{ $paginator->firstItem() }}</span> to <span class="font-medium text-[var(--text-primary)]">{{ $paginator->lastItem() }}</span> of <span class="font-medium text-[var(--text-primary)]">{{ $paginator->total() }}</span> results
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-xl shadow-xs -space-x-px">
                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="relative inline-flex items-center px-4 py-2 border border-[var(--border-subtle)] bg-[var(--bg-surface)] text-sm font-medium text-[var(--text-muted)]">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="z-10 bg-[var(--brand-primary)] border-[var(--brand-primary)] text-white relative inline-flex items-center px-4 py-2 border text-sm font-medium first:rounded-s-xl last:rounded-e-xl">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="bg-[var(--bg-surface)] border-[var(--border-subtle)] text-[var(--text-secondary)] hover:bg-[var(--bg-surface-elevated)] hover:text-[var(--text-primary)] relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors first:rounded-s-xl last:rounded-e-xl">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </span>
            </div>
        </div>
    </nav>
@endif

