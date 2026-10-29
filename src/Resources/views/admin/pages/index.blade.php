<x-admin::layouts>
    <x-slot:title>
        إدارة الصفحات والمكونات
    </x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                الصفحات المخصصة
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                أنشئ وخصص صفحات موقعك وأضف إليها المكونات المودولية بالسحب والإفلات.
            </p>
        </div>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.portal.pages.create') }}"
                class="primary-button"
            >
                إنشاء صفحة جديدة
            </a>
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.portal.pages.index')" />
</x-admin::layouts>
