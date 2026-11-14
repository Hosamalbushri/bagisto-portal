@props([
    'count' => 3,
])

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @for ($i = 0; $i < $count; $i++)
        <div class="border border-[var(--border-subtle)] rounded-2xl p-5 bg-[var(--bg-surface)] animate-pulse shadow-[var(--shadow-card)]">
            <!-- Thumbnail Shimmer -->
            <div class="w-full h-48 bg-[var(--bg-surface-muted)] rounded-xl mb-4"></div>

            <!-- Category / Tag Shimmer -->
            <div class="h-4 w-20 bg-[var(--bg-surface-muted)] rounded-full mb-3"></div>

            <!-- Title Shimmer -->
            <div class="h-6 w-5/6 bg-[var(--bg-surface-muted)] rounded mb-2"></div>

            <!-- Excerpt Shimmers -->
            <div class="space-y-2 mt-3">
                <div class="h-3.5 w-full bg-[var(--bg-surface-muted)] rounded"></div>
                <div class="h-3.5 w-4/5 bg-[var(--bg-surface-muted)] rounded"></div>
            </div>

            <!-- Footer Meta Shimmer -->
            <div class="mt-5 pt-4 border-t border-[var(--border-subtle)] flex justify-between items-center">
                <div class="h-3 w-16 bg-[var(--bg-surface-muted)] rounded"></div>
                <div class="h-4 w-12 bg-[var(--bg-surface-muted)] rounded"></div>
            </div>
        </div>
    @endfor
</div>

