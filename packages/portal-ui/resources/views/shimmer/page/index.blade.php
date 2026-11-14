@props([
    'count' => 1,
])

@for ($i = 0; $i < $count; $i++)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 animate-pulse">
        <!-- Breadcrumb Shimmer -->
        <div class="flex items-center gap-2 mb-6">
            <div class="h-4 w-16 bg-[var(--bg-surface-muted)] rounded"></div>
            <div class="h-4 w-4 bg-[var(--bg-surface-muted)] rounded"></div>
            <div class="h-4 w-24 bg-[var(--bg-surface-muted)] rounded"></div>
        </div>

        <!-- Title Shimmer -->
        <div class="h-10 w-3/4 max-w-xl bg-[var(--bg-surface-muted)] rounded-lg mb-4"></div>

        <!-- Meta Shimmer (Date / Author) -->
        <div class="flex items-center gap-4 mb-8">
            <div class="h-4 w-28 bg-[var(--bg-surface-muted)] rounded"></div>
            <div class="h-4 w-20 bg-[var(--bg-surface-muted)] rounded"></div>
        </div>

        <!-- Hero Media Shimmer -->
        <div class="w-full h-72 sm:h-96 bg-[var(--bg-surface-muted)] rounded-2xl mb-8"></div>

        <!-- Content Paragraph Shimmers -->
        <div class="space-y-4">
            <div class="h-4 w-full bg-[var(--bg-surface-muted)] rounded"></div>
            <div class="h-4 w-11/12 bg-[var(--bg-surface-muted)] rounded"></div>
            <div class="h-4 w-4/5 bg-[var(--bg-surface-muted)] rounded"></div>
            <div class="h-4 w-full bg-[var(--bg-surface-muted)] rounded"></div>
            <div class="h-4 w-2/3 bg-[var(--bg-surface-muted)] rounded"></div>
        </div>
    </div>
@endfor

