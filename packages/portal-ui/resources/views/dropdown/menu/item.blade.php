<li {{ $attributes->merge(['class' => 'cursor-pointer px-4 py-2 text-sm text-[var(--text-secondary)] hover:text-[var(--text-primary)] hover:bg-[var(--bg-surface-muted)] transition-colors max-sm:text-xs']) }}>
    {{ $slot }}
</li>