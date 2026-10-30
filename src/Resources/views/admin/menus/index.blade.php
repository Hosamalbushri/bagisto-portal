<x-admin::layouts>
    <x-slot:title>
        @lang('portal::app.admin.menus.index.title')
    </x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('portal::app.admin.menus.index.title')
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                @lang('portal::app.admin.menus.index.description')
            </p>
        </div>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('portal.customize.navigation') || bouncer()->hasPermission('portal.navigation') || bouncer()->hasPermission('portal_menus.navigation'))
                <a
                    href="{{ route('admin.portal.navigation.index') }}"
                    class="secondary-button flex items-center gap-1.5"
                >
                    <span class="icon-attribute text-lg"></span>
                    <span>@lang('portal::app.admin.navigation.title')</span>
                </a>
            @endif

            @if (bouncer()->hasPermission('portal.menus.create') || bouncer()->hasPermission('portal_menus.create'))
                <a
                    href="{{ route('admin.portal.menus.create') }}"
                    class="primary-button"
                >
                    @lang('portal::app.admin.menus.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.portal.menus.index')" />
</x-admin::layouts>
