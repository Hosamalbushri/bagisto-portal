<x-portal::layouts :has-feature="false">
    <!-- Page Title -->
    <x-slot:title>
        {{ $title ?? trans('portal::app.auth.login.title') }}
    </x-slot>

    <!-- Page Content -->
    <div class="portal-container mx-auto py-8" style="max-width: var(--container-max-width, 1280px); padding-inline: var(--page-padding-x, 1rem);">
        <x-portal::layouts.account.breadcrumb />

        <div class="mt-8 flex items-start gap-8 max-md:flex-col">
            <x-portal::layouts.account.navigation />

            <div class="flex-1 min-w-0 w-full rounded-2xl border border-[var(--border-subtle)] bg-[var(--bg-surface)] p-6 sm:p-8 shadow-sm">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-portal::layouts>
