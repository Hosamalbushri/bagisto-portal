@props(['options' => []])

@php
    $heading = $options['heading'] ?? '';
    $subheading = $options['subheading'] ?? '';
    $email = $options['email'] ?? '';
    $phone = $options['phone'] ?? '';
    $address = $options['address'] ?? '';
@endphp

<div class="container mt-20 max-1180:px-5 max-md:mt-10 max-md:px-4">
    <div class="m-auto w-full max-w-[1000px] rounded-xl border border-zinc-200 dark:border-zinc-800 p-12 max-md:p-6 bg-white dark:bg-zinc-900">
        <div class="grid grid-cols-2 gap-12 max-md:grid-cols-1">
            <!-- Left Info -->
            <div v-pre>
                @if ($heading)
                    <h2 class="font-dmserif text-3xl max-md:text-2xl text-zinc-900 dark:text-white">
                        {{ $heading }}
                    </h2>
                @endif

                @if ($subheading)
                    <p class="mt-4 text-base text-zinc-500 dark:text-zinc-400 leading-relaxed">
                        {{ $subheading }}
                    </p>
                @endif

                <div class="mt-8 space-y-4">
                    @if ($email)
                        <div class="flex items-center gap-3 text-zinc-700 dark:text-zinc-300">
                            <span class="icon-mail text-xl text-zinc-500 dark:text-zinc-400"></span>
                            <span class="text-sm font-medium">{{ $email }}</span>
                        </div>
                    @endif

                    @if ($phone)
                        <div class="flex items-center gap-3 text-zinc-700 dark:text-zinc-300">
                            <span class="icon-phone text-xl text-zinc-500 dark:text-zinc-400"></span>
                            <span class="text-sm font-medium" dir="ltr">{{ $phone }}</span>
                        </div>
                    @endif

                    @if ($address)
                        <div class="flex items-center gap-3 text-zinc-700 dark:text-zinc-300">
                            <span class="icon-location text-xl text-zinc-500 dark:text-zinc-400"></span>
                            <span class="text-sm font-medium">{{ $address }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Form using standard Bagisto Form Components -->
            <div>
                <x-portal::form :action="portal_route('portal.contact_us.send_mail')">
                    <!-- Name -->
                    <x-portal::form.control-group>
                        <x-portal::form.control-group.label class="required">
                            @lang('portal::app.home.contact-us.name')
                        </x-portal::form.control-group.label>

                        <x-portal::form.control-group.control
                            type="text"
                            name="name"
                            rules="required"
                            :value="old('name')"
                            :label="trans('portal::app.home.contact-us.name')"
                            :placeholder="trans('portal::app.home.contact-us.name')"
                        />

                        <x-portal::form.control-group.error control-name="name" />
                    </x-portal::form.control-group>

                    <!-- Email -->
                    <x-portal::form.control-group>
                        <x-portal::form.control-group.label class="required">
                            @lang('portal::app.home.contact-us.email')
                        </x-portal::form.control-group.label>

                        <x-portal::form.control-group.control
                            type="email"
                            name="email"
                            rules="required|email"
                            :value="old('email')"
                            :label="trans('portal::app.home.contact-us.email')"
                            :placeholder="trans('portal::app.home.contact-us.email')"
                        />

                        <x-portal::form.control-group.error control-name="email" />
                    </x-portal::form.control-group>

                    <!-- Message -->
                    <x-portal::form.control-group>
                        <x-portal::form.control-group.label class="required">
                            @lang('portal::app.home.contact-us.message')
                        </x-portal::form.control-group.label>

                        <x-portal::form.control-group.control
                            type="textarea"
                            name="message"
                            rules="required"
                            rows="4"
                            :value="old('message')"
                            :label="trans('portal::app.home.contact-us.message')"
                        />

                        <x-portal::form.control-group.error control-name="message" />
                    </x-portal::form.control-group>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="primary-button w-full justify-center"
                    >
                        @lang('portal::app.home.contact-us.submit')
                    </button>
                </x-portal::form>
            </div>
        </div>
    </div>
</div>

