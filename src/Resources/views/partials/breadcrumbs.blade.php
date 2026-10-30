@unless ($breadcrumbs->isEmpty())
    <nav aria-label="breadcrumbs">
        <ol class="flex" v-pre>
            @foreach ($breadcrumbs as $breadcrumb)
                @if (
                    $breadcrumb->url 
                    && ! $loop->last
                )
                    <li class="flex items-center gap-x-2.5 text-base font-medium text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors">
                        <a href="{{ $breadcrumb->url }}">
                            {{ $breadcrumb->title }}
                        </a>

                        <span class="text-2xl icon-arrow-right rtl:icon-arrow-left text-[var(--text-muted)]"></span>
                    </li>
                @else
                    <li 
                        class="flex items-center gap-x-2.5 break-all text-base text-[var(--text-muted)] after:content-['/'] after:last:hidden ltr:ml-2.5 rtl:mr-0 font-normal" 
                        aria-current="page"
                    >
                        {{ $breadcrumb->title }}
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endunless
