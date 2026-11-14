@if (core()->getConfigData('general.content.custom_scripts.cookie_consent') ?? true)
    <x-portal::layouts.cookie.consent />
@endif
