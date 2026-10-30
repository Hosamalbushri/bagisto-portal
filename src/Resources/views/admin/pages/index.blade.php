<x-admin::layouts>
    <x-slot:title>
        @lang('portal::app.admin.pages.index.title')
    </x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('portal::app.admin.pages.index.title')
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                @lang('portal::app.admin.pages.index.description')
            </p>
        </div>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('appearance.sections') || bouncer()->hasPermission('portal.pages.customize') || bouncer()->hasPermission('pages.customize'))
                <a
                    href="{{ route('admin.appearance.sections.index', core()->getCurrentChannel()->theme ?? 'portal') }}"
                    class="secondary-button"
                >
                    @lang('portal::app.admin.pages.index.customize-home-btn')
                </a>
            @endif

            @if (bouncer()->hasPermission('portal.pages.create') || bouncer()->hasPermission('pages.create'))
                <a
                    href="{{ route('admin.portal.pages.create') }}"
                    class="primary-button"
                >
                    @lang('portal::app.admin.pages.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.portal.pages.index')" />
</x-admin::layouts>
