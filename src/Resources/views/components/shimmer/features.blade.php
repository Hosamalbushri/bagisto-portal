<div class="py-16 sm:py-24 animate-pulse">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-16 flex flex-col items-center">
            <div class="h-6 w-28 bg-gray-200 dark:bg-gray-800 rounded-full mb-4"></div>
            <div class="h-10 w-2/3 bg-gray-200 dark:bg-gray-800 rounded-xl mb-4"></div>
            <div class="h-4 w-4/5 bg-gray-200 dark:bg-gray-800 rounded"></div>
        </div>

        <!-- 3-Column Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @for ($i = 0; $i < 3; $i++)
                <div class="p-8 rounded-2xl bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800">
                    <div class="h-12 w-12 rounded-xl bg-gray-200 dark:bg-gray-700 mb-6"></div>
                    <div class="h-6 w-3/4 bg-gray-200 dark:bg-gray-700 rounded mb-3"></div>
                    <div class="h-4 w-full bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
                    <div class="h-4 w-4/5 bg-gray-200 dark:bg-gray-700 rounded"></div>
                </div>
            @endfor
        </div>
    </div>
</div>
