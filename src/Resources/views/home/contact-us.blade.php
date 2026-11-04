<x-portal::layouts>
    <x-slot:title>
        @lang('portal::app.home.contact-us.title')
    </x-slot>

    <div class="portal-container mx-auto py-16">
        <div
            class="portal-card m-auto w-full border border-[var(--border-subtle)] bg-[var(--bg-surface)] p-8 sm:p-14 shadow-[var(--shadow-card)]"
            data-portal-card
            style="border-radius: var(--card-radius, 1rem);"
        >
            <h1 class="text-3xl font-extrabold text-[var(--text-primary)] font-cairo sm:text-4xl">
                @lang('portal::app.home.contact-us.heading')
            </h1>

            <p class="mt-4 text-base text-[var(--text-secondary)]">
                @lang('portal::app.home.contact-us.subheading')
            </p>

            <div id="contact-us-form" class="mt-10">
                <x-portal::form :action="portal_route('portal.contact_us.send_mail')">
                    <!-- Honeypot anti-spam protection -->
                    <div class="hidden" aria-hidden="true" style="display:none !important;">
                        <input type="text" name="website" tabindex="-1" autocomplete="off" />
                    </div>

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
                            :aria-label="trans('portal::app.home.contact-us.name')"
                            aria-required="true"
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
                            :aria-label="trans('portal::app.home.contact-us.email')"
                            aria-required="true"
                        />

                        <x-portal::form.control-group.error control-name="email" />
                    </x-portal::form.control-group>

                    <!-- Phone -->
                    <x-portal::form.control-group>
                        <x-portal::form.control-group.label>
                            @lang('portal::app.home.contact-us.phone')
                        </x-portal::form.control-group.label>

                        <x-portal::form.control-group.control
                            type="text"
                            name="contact"
                            :value="old('contact')"
                            :label="trans('portal::app.home.contact-us.phone')"
                            :placeholder="trans('portal::app.home.contact-us.phone')"
                            :aria-label="trans('portal::app.home.contact-us.phone')"
                        />

                        <x-portal::form.control-group.error control-name="contact" />
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
                            :label="trans('portal::app.home.contact-us.message')"
                            :placeholder="trans('portal::app.home.contact-us.message')"
                            :aria-label="trans('portal::app.home.contact-us.message')"
                            aria-required="true"
                            rows="6"
                        />

                        <x-portal::form.control-group.error control-name="message" />
                    </x-portal::form.control-group>

                    <!-- Submit Button -->
                    <div class="mt-8">
                        <x-portal::button
                            class="btn-primary w-full sm:w-auto !px-8 !py-3 shadow-sm hover:shadow-[var(--shadow-glow)]"
                            type="submit"
                        >
                            @lang('portal::app.home.contact-us.submit')
                        </x-portal::button>
                    </div>
                </x-portal::form>
            </div>
        </div>
    </div>
</x-portal::layouts>
