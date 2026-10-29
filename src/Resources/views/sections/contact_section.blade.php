@php
    $heading = $options['heading'] ?? '';
    $subheading = $options['subheading'] ?? '';
    $email = $options['email'] ?? '';
    $phone = $options['phone'] ?? '';
    $address = $options['address'] ?? '';
@endphp

<section class="py-16 lg:py-24 bg-slate-50">
    <div class="container mx-auto px-4 max-w-7xl">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
            <div class="space-y-6">
                @if ($heading)
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 tracking-tight">
                        {{ $heading }}
                    </h2>
                @endif

                @if ($subheading)
                    <p class="text-lg text-gray-600 leading-relaxed">
                        {{ $subheading }}
                    </p>
                @endif

                <div class="space-y-4 pt-4">
                    @if ($email)
                        <div class="flex items-center gap-3 text-gray-700">
                            <span class="icon-mail text-xl text-blue-600"></span>
                            <span>{{ $email }}</span>
                        </div>
                    @endif

                    @if ($phone)
                        <div class="flex items-center gap-3 text-gray-700">
                            <span class="icon-phone text-xl text-blue-600"></span>
                            <span dir="ltr">{{ $phone }}</span>
                        </div>
                    @endif

                    @if ($address)
                        <div class="flex items-center gap-3 text-gray-700">
                            <span class="icon-location text-xl text-blue-600"></span>
                            <span>{{ $address }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm">
                <form action="{{ route('shop.home.contact_us.send_mail') }}" method="POST">
                    @csrf
                    <x-portal::form.control-group>
                        <x-portal::form.control-group.label class="required">
                            @lang('portal::app.home.contact-us.name')
                        </x-portal::form.control-group.label>

                        <x-portal::form.control-group.control
                            type="text"
                            name="name"
                            rules="required"
                            :label="trans('portal::app.home.contact-us.name')"
                            :placeholder="trans('portal::app.home.contact-us.name')"
                        />

                        <x-portal::form.control-group.error control-name="name" />
                    </x-portal::form.control-group>

                    <x-portal::form.control-group>
                        <x-portal::form.control-group.label class="required">
                            @lang('portal::app.home.contact-us.email')
                        </x-portal::form.control-group.label>

                        <x-portal::form.control-group.control
                            type="email"
                            name="email"
                            rules="required|email"
                            :label="trans('portal::app.home.contact-us.email')"
                            :placeholder="trans('portal::app.home.contact-us.email')"
                        />

                        <x-portal::form.control-group.error control-name="email" />
                    </x-portal::form.control-group>

                    <x-portal::form.control-group>
                        <x-portal::form.control-group.label class="required">
                            @lang('portal::app.home.contact-us.message')
                        </x-portal::form.control-group.label>

                        <x-portal::form.control-group.control
                            type="textarea"
                            name="message"
                            rules="required"
                            rows="4"
                            :label="trans('portal::app.home.contact-us.message')"
                            :placeholder="trans('portal::app.home.contact-us.message')"
                        />

                        <x-portal::form.control-group.error control-name="message" />
                    </x-portal::form.control-group>

                    <button
                        type="submit"
                        class="w-full primary-button py-3 text-base font-semibold"
                    >
                        @lang('portal::app.home.contact-us.submit')
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
