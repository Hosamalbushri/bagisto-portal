<x-admin::layouts>
    <x-slot:title>
        تخصيص مكونات الصفحة: {{ $page->title }}
    </x-slot>

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-4">
            <a
                href="{{ route('admin.portal.pages.index') }}"
                class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors"
                title="العودة لقائمة الصفحات"
            >
                <span class="icon-arrow-left text-xl"></span>
            </a>

            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ $page->title }}
                    </h1>
                    <span class="badge badge-sm badge-info">/pages/{{ $page->slug }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">
                    اسحب ورتب الأقسام، وعدل خيارات المحتوى وشاهد التغييرات مباشرة في نافذة المعاينة.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('portal.page.show', $page->slug) }}"
                target="_blank"
                class="secondary-button text-xs py-2 px-3"
            >
                <span class="icon-view text-base mr-1"></span>
                فتح في المتجر
            </a>

            <form action="{{ route('admin.portal.pages.discard', $page->id) }}" method="POST">
                @csrf
                <button
                    type="submit"
                    onclick="return confirm('هل أنت متأكد من رغبتك في التراجع عن جميع التعديلات غير المنشورة؟')"
                    class="transparent-button text-xs py-2 px-3 text-red-600 hover:bg-red-50 dark:hover:bg-gray-800"
                >
                    تراجع عن المسودة
                </button>
            </form>

            <form action="{{ route('admin.portal.pages.publish', $page->id) }}" method="POST">
                @csrf
                <button type="submit" class="primary-button text-xs py-2 px-4 shadow-sm">
                    <span class="icon-done text-base mr-1"></span>
                    نشر التغييرات
                </button>
            </form>
        </div>
    </div>

    <!-- Workspace: Sidebar (Sections) + Preview (Iframe) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[calc(100vh-190px)] min-h-[600px]">
        
        <!-- Left Column: Sections List & Controls -->
        <div class="lg:col-span-4 flex flex-col bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50">
                <span class="font-bold text-sm text-gray-800 dark:text-white">
                    أقسام الصفحة ({{ $sections->count() }})
                </span>

                <button
                    type="button"
                    onclick="document.getElementById('add-section-modal').classList.remove('hidden')"
                    class="secondary-button text-xs py-1.5 px-3"
                >
                    + إضافة قسم جديد
                </button>
            </div>

            <!-- Sections List -->
            <div class="flex-1 overflow-y-auto p-3 space-y-2.5">
                @forelse ($sections as $index => $section)
                    <div class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/80 rounded-lg hover:border-blue-400 transition-all flex items-center justify-between group shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-slate-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs font-bold flex items-center justify-center">
                                {{ $index + 1 }}
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $section->name }}
                                </h3>
                                <p class="text-xs text-gray-400">
                                    {{ $section->getTypeInstance()?->getTitle() ?? $section->type }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                onclick="openEditModal({{ $section->id }}, '{{ addslashes($section->name) }}', {{ json_encode($section->options ?? []) }})"
                                class="p-1.5 text-gray-500 hover:text-blue-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700"
                                title="تعديل خيارات القسم"
                            >
                                <span class="icon-edit text-base"></span>
                            </button>

                            <form action="{{ route('admin.portal.pages.sections.delete', [$page->id, $section->id]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    onclick="return confirm('هل أنت متأكد من حذف هذا القسم من الصفحة؟')"
                                    class="p-1.5 text-gray-400 hover:text-red-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700"
                                    title="حذف القسم"
                                >
                                    <span class="icon-delete text-base"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400">
                        <span class="icon-cms text-4xl block mb-2 opacity-50"></span>
                        <p class="text-sm font-medium">لم يتم إضافة أي مكونات بعد.</p>
                        <p class="text-xs text-gray-500 mt-1">انقر على "إضافة قسم جديد" للبدء في تشكيل الصفحة.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Column: Interactive Live Preview Iframe -->
        <div class="lg:col-span-8 flex flex-col bg-slate-100 dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="p-2.5 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="font-medium text-gray-700 dark:text-gray-300">معاينة مباشرة للصفحة</span>
                </div>

                <div class="flex items-center gap-1.5 bg-gray-100 dark:bg-gray-800 p-1 rounded-lg">
                    <button
                        type="button"
                        onclick="setDevice('desktop')"
                        id="btn-desktop"
                        class="px-2.5 py-1 rounded bg-white dark:bg-gray-700 text-gray-800 dark:text-white font-semibold shadow-xs"
                    >
                        شاشة حاسوب
                    </button>
                    <button
                        type="button"
                        onclick="setDevice('tablet')"
                        id="btn-tablet"
                        class="px-2.5 py-1 rounded hover:bg-white dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400"
                    >
                        جهاز لوحي
                    </button>
                    <button
                        type="button"
                        onclick="setDevice('mobile')"
                        id="btn-mobile"
                        class="px-2.5 py-1 rounded hover:bg-white dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400"
                    >
                        هاتف ذكي
                    </button>
                </div>
            </div>

            <div class="flex-1 flex items-center justify-center p-4 overflow-hidden bg-slate-200/50 dark:bg-black/20">
                <div id="preview-wrapper" class="w-full h-full transition-all duration-300 rounded-lg overflow-hidden shadow-lg border border-slate-300 dark:border-gray-800 bg-white">
                    <iframe
                        id="preview-frame"
                        src="{{ $previewUrl }}"
                        class="w-full h-full border-0"
                    ></iframe>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal: Add New Section -->
    <div id="add-section-modal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-800 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800 mb-5">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    اختر مكوّناً لإضافته للصفحة
                </h2>
                <button
                    type="button"
                    onclick="document.getElementById('add-section-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl font-bold"
                >
                    &times;
                </button>
            </div>

            <form action="{{ route('admin.portal.pages.sections.store', $page->id) }}" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            اسم القسم التعريفي
                        </label>
                        <input
                            type="text"
                            name="name"
                            required
                            placeholder="مثال: البانر الرئيسي، مميزاتنا، أسئلة وأجوبة..."
                            class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-800 dark:text-white text-sm focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            اختر نوع المكون من المكتبة
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-1">
                            @foreach ($sectionTypes as $type)
                                <label class="border border-gray-200 dark:border-gray-700 rounded-xl p-3.5 flex items-start gap-3 cursor-pointer hover:border-blue-500 transition-colors bg-white dark:bg-gray-800">
                                    <input
                                        type="radio"
                                        name="type"
                                        value="{{ $type->getCode() }}"
                                        required
                                        class="mt-1 text-blue-600"
                                    />
                                    <div>
                                        <div class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span class="{{ $type->getIcon() }} text-blue-600"></span>
                                            {{ $type->getTitle() }}
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ $type->getDescription() }}
                                        </p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <button
                        type="button"
                        onclick="document.getElementById('add-section-modal').classList.add('hidden')"
                        class="transparent-button text-xs py-2 px-4"
                    >
                        إلغاء
                    </button>
                    <button type="submit" class="primary-button text-xs py-2 px-5">
                        إضافة المكون
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Section Options -->
    <div id="edit-section-modal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-800 mb-5">
                <h2 id="edit-modal-title" class="text-lg font-bold text-gray-900 dark:text-white">
                    تعديل بيانات القسم
                </h2>
                <button
                    type="button"
                    onclick="document.getElementById('edit-section-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 text-xl font-bold"
                >
                    &times;
                </button>
            </div>

            <form id="edit-section-form" method="POST">
                @csrf
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            اسم القسم
                        </label>
                        <input
                            type="text"
                            id="edit-section-name"
                            name="name"
                            required
                            class="w-full px-3.5 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-800 dark:text-white text-sm"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            خيارات المحتوى (JSON Options)
                        </label>
                        <textarea
                            id="edit-section-options"
                            name="options_json"
                            rows="8"
                            dir="ltr"
                            class="w-full font-mono text-xs p-3 border border-gray-300 dark:border-gray-700 rounded-lg dark:bg-gray-800 dark:text-gray-200"
                        ></textarea>
                        <p class="text-xs text-gray-400 mt-1">
                            يمكنك تعديل نصوص وعناوين وروابط المكون من هنا بصيغة JSON.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <button
                        type="button"
                        onclick="document.getElementById('edit-section-modal').classList.add('hidden')"
                        class="transparent-button text-xs py-2 px-4"
                    >
                        إلغاء
                    </button>
                    <button type="button" onclick="submitEditSection()" class="primary-button text-xs py-2 px-5">
                        حفظ في المسودة
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentSectionId = null;

        function setDevice(device) {
            const wrapper = document.getElementById('preview-wrapper');
            const btns = ['desktop', 'tablet', 'mobile'];
            
            btns.forEach(b => {
                const btn = document.getElementById('btn-' + b);
                if (b === device) {
                    btn.classList.add('bg-white', 'dark:bg-gray-700', 'text-gray-800', 'dark:text-white', 'shadow-xs');
                    btn.classList.remove('text-gray-600', 'dark:text-gray-400');
                } else {
                    btn.classList.remove('bg-white', 'dark:bg-gray-700', 'text-gray-800', 'dark:text-white', 'shadow-xs');
                    btn.classList.add('text-gray-600', 'dark:text-gray-400');
                }
            });

            if (device === 'mobile') {
                wrapper.style.maxWidth = '375px';
            } else if (device === 'tablet') {
                wrapper.style.maxWidth = '768px';
            } else {
                wrapper.style.maxWidth = '100%';
            }
        }

        function openEditModal(sectionId, sectionName, options) {
            currentSectionId = sectionId;
            document.getElementById('edit-section-name').value = sectionName;
            document.getElementById('edit-section-options').value = JSON.stringify(options, null, 2);
            document.getElementById('edit-section-modal').classList.remove('hidden');
        }

        function submitEditSection() {
            if (!currentSectionId) return;

            let parsedOptions = {};
            try {
                parsedOptions = JSON.parse(document.getElementById('edit-section-options').value);
            } catch (e) {
                alert('خطأ في صيغة الـ JSON المدخلة: ' + e.message);
                return;
            }

            const name = document.getElementById('edit-section-name').value;
            const url = "{{ route('admin.portal.pages.sections.update', [$page->id, ':id']) }}".replace(':id', currentSectionId);

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    options: parsedOptions
                })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('edit-section-modal').classList.add('hidden');
                document.getElementById('preview-frame').contentWindow.location.reload();
            })
            .catch(err => {
                alert('حدث خطأ أثناء حفظ التعديلات.');
            });
        }
    </script>
</x-admin::layouts>
