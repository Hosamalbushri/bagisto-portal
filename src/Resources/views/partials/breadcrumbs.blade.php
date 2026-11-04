@unless ($breadcrumbs->isEmpty())
    <nav aria-label="breadcrumbs">
        <ol class="flex items-center flex-wrap" v-pre>
            @foreach ($breadcrumbs as $breadcrumb)
                @if (
                    $breadcrumb->url 
                    && ! $loop->last
                )
                    <li class="flex items-center gap-x-2 text-sm sm:text-base font-medium text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors">
                        <a href="{{ $breadcrumb->url }}">
                            {{ $breadcrumb->title }}
                        </a>

                        <span class="text-xl icon-arrow-right rtl:icon-arrow-left text-[var(--text-muted)] mx-1"></span>
                    </li>
                @else
                    <li 
                        class="flex items-center gap-x-2 break-all text-sm sm:text-base text-[var(--text-muted)] font-normal" 
                        aria-current="page"
                    >
                        {{ $breadcrumb->title }}
                    </li>
                @endif
            @endforeach
        </ol>

        @php
            $schemaBreadcrumbs = [
                '@context'        => 'https://schema.org',
                '@type'           => 'BreadcrumbList',
                'itemListElement' => $breadcrumbs->values()->map(function ($b, $i) {
                    return [
                        '@type'    => 'ListItem',
                        'position' => $i + 1,
                        'name'     => $b->title,
                        'item'     => $b->url ?: url()->current(),
                    ];
                })->all(),
            ];
        @endphp

        <!-- Schema.org BreadcrumbList Microdata -->
        <script type="application/ld+json">
        {!! json_encode($schemaBreadcrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    </nav>
@endunless
