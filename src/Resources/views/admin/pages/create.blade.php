<x-admin::layouts>
    <x-slot:title>
        إنشاء صفحة مخصصة جديدة
    </x-slot>

    <form method="POST" action="{{ route('admin.portal.pages.store') }}">
        @csrf

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                إنشاء صفحة مخصصة جديدة
            </h1>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.portal.pages.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800"
                >
                    إلغاء
                </a>

                <button type="submit" class="primary-button">
                    حفظ ومتابعة إلى التخصيص
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="p-6 bg-white dark:bg-gray-900 rounded-lg box-shadow space-y-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-2">
                        المعلومات الأساسية
                    </h2>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            عنوان الصفحة
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            :name="core()->getRequestedLocaleCode() . '[title]'"
                            rules="required"
                            value="{{ old(core()->getRequestedLocaleCode() . '.title') }}"
                            placeholder="مثال: من نحن، خدماتنا، حلول الأعمال"
                        />

                        <x-admin::form.control-group.error :control-name="core()->getRequestedLocaleCode() . '[title]'" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            الرابط الدائم (Slug)
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="slug"
                            rules="required"
                            value="{{ old('slug') }}"
                            placeholder="مثال: about-us, our-services"
                        />

                        <x-admin::form.control-group.error control-name="slug" />
                    </x-admin::form.control-group>
                </div>

                <div class="p-6 bg-white dark:bg-gray-900 rounded-lg box-shadow space-y-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-2">
                        تهيئة محركات البحث (SEO)
                    </h2>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            عنوان Meta
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            :name="core()->getRequestedLocaleCode() . '[meta_title]'"
                            value="{{ old(core()->getRequestedLocaleCode() . '.meta_title') }}"
                        />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            وصف Meta
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            :name="core()->getRequestedLocaleCode() . '[meta_description]'"
                            value="{{ old(core()->getRequestedLocaleCode() . '.meta_description') }}"
                            rows="3"
                        />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            الكلمات المفتاحية
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            :name="core()->getRequestedLocaleCode() . '[meta_keywords]'"
                            value="{{ old(core()->getRequestedLocaleCode() . '.meta_keywords') }}"
                        />
                    </x-admin::form.control-group>
                </div>
            </div>

            <div class="space-y-6">
                <div class="p-6 bg-white dark:bg-gray-900 rounded-lg box-shadow space-y-4">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-2">
                        إعدادات النشر
                    </h2>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            القناة
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="channel_id"
                            rules="required"
                        >
                            @foreach ($channels as $channel)
                                <option value="{{ $channel->id }}">
                                    {{ $channel->name }}
                                </option>
                            @endforeach
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="channel_id" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            حالة الصفحة
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="switch"
                            name="status"
                            value="1"
                            :checked="true"
                        />
                    </x-admin::form.control-group>
                </div>
            </div>
        </div>
    </form>
</x-admin::layouts>
