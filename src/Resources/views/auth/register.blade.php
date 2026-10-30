<x-portal::layouts :has-feature="false">
    <x-slot:title>
        @lang('portal::app.auth.register.title')
    </x-slot>

    <div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-[var(--bg-surface)] p-8 sm:p-10 rounded-2xl shadow-[var(--shadow-card)] border border-[var(--border-subtle)]">
            <!-- Header -->
            <div class="text-center">
                <a href="{{ portal_home_url() }}" class="inline-block mb-4">
                    @if ($logo = core()->getCurrentChannel()->logo_url)
                        <img src="{{ $logo }}" class="h-10 mx-auto" alt="{{ config('app.name') }}" />
                    @else
                        <span class="flex items-center justify-center gap-2">
                            <svg class="h-8 w-8 rounded-lg shrink-0 shadow-sm" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="32" height="32" rx="8" fill="#1c66f6"/>
                                <path d="M10 8H20C21.6569 8 23 9.34315 23 11C23 12.6569 21.6569 14 20 14H10V8Z" fill="#fe8312"/>
                                <path d="M10 14H19L23 24H18L15 16.5H10V24H7V8H10V14Z" fill="white"/>
                            </svg>
                            <span class="text-2xl font-bold tracking-tight text-[var(--text-primary)] font-cairo">
                                {{ core()->getCurrentChannel()->name ?? config('app.name') }}
                            </span>
                        </span>
                    @endif
                </a>

                <h1 class="text-2xl font-bold tracking-tight text-[var(--text-primary)] font-cairo">
                    @lang('portal::app.auth.register.heading')
                </h1>

                <p class="mt-2 text-sm text-[var(--text-secondary)]">
                    @lang('portal::app.auth.register.subheading')
                </p>
            </div>

            <!-- Registration Form -->
            <x-portal::form :action="route('portal.auth.register.store')" class="mt-8 space-y-5">
                <!-- Name -->
                <x-portal::form.control-group>
                    <x-portal::form.control-group.label class="required">
                        @lang('portal::app.auth.register.name')
                    </x-portal::form.control-group.label>

                    <x-portal::form.control-group.control
                        type="text"
                        name="name"
                        rules="required"
                        value="{{ old('name') }}"
                        :label="trans('portal::app.auth.register.name')"
                        placeholder="John Doe"
                        aria-required="true"
                    />

                    <x-portal::form.control-group.error control-name="name" />
                </x-portal::form.control-group>

                <!-- Email -->
                <x-portal::form.control-group>
                    <x-portal::form.control-group.label class="required">
                        @lang('portal::app.auth.register.email')
                    </x-portal::form.control-group.label>

                    <x-portal::form.control-group.control
                        type="email"
                        name="email"
                        rules="required|email"
                        value="{{ old('email') }}"
                        :label="trans('portal::app.auth.register.email')"
                        placeholder="user@example.com"
                        aria-required="true"
                    />

                    <x-portal::form.control-group.error control-name="email" />
                </x-portal::form.control-group>

                <!-- Password -->
                <x-portal::form.control-group>
                    <x-portal::form.control-group.label class="required">
                        @lang('portal::app.auth.register.password')
                    </x-portal::form.control-group.label>

                    <x-portal::form.control-group.control
                        type="password"
                        name="password"
                        rules="required|min:6"
                        :label="trans('portal::app.auth.register.password')"
                        placeholder="••••••••"
                        ref="password"
                        aria-required="true"
                    />

                    <x-portal::form.control-group.error control-name="password" />
                </x-portal::form.control-group>

                <!-- Password Confirmation -->
                <x-portal::form.control-group>
                    <x-portal::form.control-group.label class="required">
                        @lang('portal::app.auth.register.password-confirmation')
                    </x-portal::form.control-group.label>

                    <x-portal::form.control-group.control
                        type="password"
                        name="password_confirmation"
                        rules="confirmed:@password"
                        :label="trans('portal::app.auth.register.password-confirmation')"
                        placeholder="••••••••"
                        aria-required="true"
                    />

                    <x-portal::form.control-group.error control-name="password_confirmation" />
                </x-portal::form.control-group>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 rounded-xl shadow-sm text-sm font-semibold text-white bg-[var(--brand-primary)] hover:bg-[var(--brand-primary-hover)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-primary)] transition-all"
                    >
                        @lang('portal::app.auth.register.submit')
                    </button>
                </div>
            </x-portal::form>

            <!-- Login Footer -->
            <div class="text-center text-sm text-[var(--text-secondary)] pt-4 border-t border-[var(--border-subtle)]">
                <span>@lang('portal::app.auth.register.already-have-account')</span>
                <a
                    href="{{ route('portal.auth.login') }}"
                    class="font-semibold text-[var(--brand-primary)] hover:text-[var(--brand-primary-hover)] ltr:ml-1 rtl:mr-1 hover:underline"
                >
                    @lang('portal::app.auth.register.login-link')
                </a>
            </div>
        </div>
    </div>
</x-portal::layouts>

