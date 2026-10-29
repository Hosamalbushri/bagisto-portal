<x-portal::layouts :has-feature="false">
    <x-slot:title>
        @lang('portal::app.auth.login.title')
    </x-slot>

    <div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-900 p-8 sm:p-10 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-800">
            <!-- Header -->
            <div class="text-center">
                <a href="{{ route('shop.home.index') }}" class="inline-block mb-4">
                    @if ($logo = core()->getCurrentChannel()->logo_url)
                        <img src="{{ $logo }}" class="h-10 mx-auto" alt="{{ config('app.name') }}" />
                    @else
                        <span class="text-2xl font-extrabold text-blue-600 dark:text-blue-400">
                            {{ core()->getCurrentChannel()->name ?? config('app.name') }}
                        </span>
                    @endif
                </a>

                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    @lang('portal::app.auth.login.heading')
                </h1>

                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    @lang('portal::app.auth.login.subheading')
                </p>
            </div>

            <!-- Login Form -->
            <x-portal::form :action="route('portal.auth.login.store')" class="mt-8 space-y-6">
                <!-- Email -->
                <x-portal::form.control-group>
                    <x-portal::form.control-group.label class="required">
                        @lang('portal::app.auth.login.email')
                    </x-portal::form.control-group.label>

                    <x-portal::form.control-group.control
                        type="email"
                        name="email"
                        rules="required|email"
                        value="{{ old('email') }}"
                        :label="trans('portal::app.auth.login.email')"
                        placeholder="user@example.com"
                        aria-required="true"
                    />

                    <x-portal::form.control-group.error control-name="email" />
                </x-portal::form.control-group>

                <!-- Password -->
                <x-portal::form.control-group>
                    <x-portal::form.control-group.label class="required">
                        @lang('portal::app.auth.login.password')
                    </x-portal::form.control-group.label>

                    <x-portal::form.control-group.control
                        type="password"
                        name="password"
                        rules="required|min:6"
                        :label="trans('portal::app.auth.login.password')"
                        placeholder="••••••••"
                        aria-required="true"
                    />

                    <x-portal::form.control-group.error control-name="password" />
                </x-portal::form.control-group>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-gray-600 dark:text-gray-400 cursor-pointer">
                        <input
                            type="checkbox"
                            name="remember"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                        />
                        <span>@lang('portal::app.auth.login.remember-me')</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    >
                        @lang('portal::app.auth.login.submit')
                    </button>
                </div>
            </x-portal::form>

            <!-- Register Footer -->
            <div class="text-center text-sm text-gray-600 dark:text-gray-400 pt-4 border-t border-gray-100 dark:border-gray-800">
                <span>@lang('portal::app.auth.login.no-account')</span>
                <a
                    href="{{ route('portal.auth.register') }}"
                    class="font-semibold text-blue-600 hover:text-blue-500 dark:text-blue-400 ltr:ml-1 rtl:mr-1 hover:underline"
                >
                    @lang('portal::app.auth.login.register-link')
                </a>
            </div>
        </div>
    </div>
</x-portal::layouts>

