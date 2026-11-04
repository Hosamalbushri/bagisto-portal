@props([
    'errorCode' => 404,
])

<x-portal::layouts
    :has-header="true"
    :has-feature="false"
    :has-footer="true"
>
    <x-slot:title>
        @lang("portal::app.errors.{$errorCode}.title")
    </x-slot>

    <div class="min-h-[55vh] flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl w-full text-center">
            <!-- Glow Error Code Typography -->
            <div class="relative select-none my-4">
                <span class="text-8xl sm:text-9xl font-black tracking-widest text-[var(--brand-primary)]/15 select-none font-mono">
                    {{ $errorCode }}
                </span>
                <span class="absolute inset-0 flex items-center justify-center text-7xl sm:text-8xl font-black text-[var(--text-primary)] font-mono drop-shadow-xs">
                    {{ $errorCode }}
                </span>
            </div>

            <!-- Error Heading -->
            <h1 class="mt-6 text-2xl sm:text-3xl font-extrabold text-[var(--text-primary)] font-cairo">
                @lang("portal::app.errors.{$errorCode}.title")
            </h1>

            <!-- Error Description -->
            <p class="mt-3 text-sm sm:text-base text-[var(--text-secondary)] leading-relaxed max-w-md mx-auto">
                @if ($errorCode == 503 && ! empty(core()->getCurrentChannel()->maintenance_mode_text))
                    {{ core()->getCurrentChannel()->maintenance_mode_text }}
                @else
                    @lang("portal::app.errors.{$errorCode}.description")
                @endif
            </p>

            <!-- Navigation Controls -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a
                    href="{{ portal_home_url() }}"
                    class="btn-primary w-full sm:w-auto text-sm"
                >
                    <span class="icon-arrow-left rtl:rotate-180 text-xs"></span>
                    <span>@lang('portal::app.errors.go-to-home')</span>
                </a>

                @if ($errorCode == 404)
                    <a
                        href="{{ route('portal.search.index') }}"
                        class="btn-secondary w-full sm:w-auto text-sm"
                    >
                        <span class="icon-search text-xs"></span>
                        <span>@lang('portal::app.search.title')</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-portal::layouts>

