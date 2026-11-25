<?php

namespace Platform\Portal\Tests\Unit\Components;

use Illuminate\Support\Facades\Blade;
use Platform\Portal\Tests\TestCase;

class PortalComponentsTest extends TestCase
{
    public function test_shimmer_hero_renders_successfully(): void
    {
        $rendered = Blade::render('<x-portal::shimmer.hero />');

        $this->assertNotEmpty($rendered);
        $this->assertStringContainsString('animate-pulse', $rendered);
    }

    public function test_shimmer_bento_renders_successfully(): void
    {
        $rendered = Blade::render('<x-portal::shimmer.bento />');

        $this->assertNotEmpty($rendered);
        $this->assertStringContainsString('animate-pulse', $rendered);
        $this->assertStringContainsString('md:col-span-2', $rendered);
    }

    public function test_shimmer_features_renders_successfully(): void
    {
        $rendered = Blade::render('<x-portal::shimmer.features />');

        $this->assertNotEmpty($rendered);
        $this->assertStringContainsString('animate-pulse', $rendered);
    }

    public function test_shimmer_page_renders_successfully(): void
    {
        $rendered = Blade::render('<x-portal::shimmer.page />');

        $this->assertNotEmpty($rendered);
        $this->assertStringContainsString('animate-pulse', $rendered);
    }

    public function test_flash_group_renders_successfully(): void
    {
        $rendered = Blade::render('<x-portal::flash-group />');

        $this->assertNotEmpty($rendered);
        $this->assertStringContainsString('v-flash-group', $rendered);
    }

    public function test_header_renders_successfully(): void
    {
        $rendered = Blade::render('<x-portal::layouts.header />');

        $this->assertNotEmpty($rendered);
        $this->assertStringContainsString('<header', $rendered);
        $this->assertStringContainsString('portal-theme-icon', $rendered);
        $this->assertStringContainsString('icon-search', $rendered);
    }

    public function test_header_renders_cta_button_when_configured(): void
    {
        $channel = core()->getCurrentChannel();
        $theme = portal_active_theme($channel->id);
        $locale = app()->getLocale();

        app(\Platform\Portal\Repositories\PortalLayoutSettingRepository::class)->saveSettings(
            'header',
            [
                'cta' => [
                    'enabled' => 1,
                    'text' => 'Get Started Now',
                    'url' => '/start',
                    'target' => '_blank',
                ],
            ],
            $channel->id,
            $theme,
            $locale
        );

        $rendered = Blade::render('<x-portal::layouts.header />');

        $this->assertStringContainsString('Get Started Now', $rendered);
        $this->assertStringContainsString('/start', $rendered);
    }

    public function test_system_config_includes_light_and_dark_logo_options(): void
    {
        $coreConfig = collect(config('core'));
        $designSection = $coreConfig->firstWhere('key', 'portal.general.design');

        $this->assertNotNull($designSection);

        $fields = collect($designSection['fields'] ?? []);
        $lightField = $fields->firstWhere('name', 'logo_light');
        $darkField = $fields->firstWhere('name', 'logo_dark');

        $this->assertSame('image', $lightField['type'] ?? null);
        $this->assertSame('image', $darkField['type'] ?? null);
        $this->assertTrue($lightField['channel_based'] ?? false);
        $this->assertTrue($darkField['channel_based'] ?? false);
        $this->assertTrue($lightField['locale_based'] ?? false);
        $this->assertTrue($darkField['locale_based'] ?? false);
    }

    public function test_logo_component_resolves_locale_specific_logos_with_default_locale_fallback(): void
    {
        $originalCore = app(\Webkul\Core\Core::class);

        $mockCore = new class extends \Webkul\Core\Core {
            public function getCurrentChannel()
            {
                return (object) [
                    'id' => 1,
                    'code' => 'default',
                    'name' => 'Portal',
                    'logo_url' => null,
                    'default_locale' => (object) ['code' => 'en'],
                    'currencies' => collect([(object) ['code' => 'USD', 'symbol' => '$']]),
                    'locales' => collect([(object) ['code' => 'en', 'name' => 'English'], (object) ['code' => 'ar', 'name' => 'Arabic']]),
                ];
            }

            public function getConfigData(string $field, ?string $channel = null, ?string $locale = null)
            {
                $locale = $locale ?? app()->getLocale();

                return match ("{$locale}:{$field}") {
                    'ar:portal.general.design.logo_light' => 'configuration/ar-light.png',
                    'en:portal.general.design.logo_light' => 'configuration/en-light.png',
                    'en:portal.general.design.logo_dark' => 'configuration/en-dark.png',
                    default => null,
                };
            }
        };

        app()->instance(\Webkul\Core\Core::class, $mockCore);

        try {
            $this->assertStringContainsString('configuration/ar-light.png', portal_logo('light', 'ar'));
            // Arabic dark logo unset -> falls back to default locale (en) dark logo
            $this->assertStringContainsString('configuration/en-dark.png', portal_logo('dark', 'ar'));
            $this->assertStringContainsString('configuration/en-light.png', portal_logo('light', 'en'));
        } finally {
            app()->instance(\Webkul\Core\Core::class, $originalCore);
        }
    }

    public function test_logo_component_renders_both_mode_logos_when_configured(): void
    {
        $originalCore = app(\Webkul\Core\Core::class);

        $mockCore = new class extends \Webkul\Core\Core {
            public function getConfigData(string $field)
            {
                return match ($field) {
                    'portal.general.design.logo_light' => 'configuration/light-logo.png',
                    'portal.general.design.logo_dark' => 'configuration/dark-logo.png',
                    default => null,
                };
            }
        };

        app()->instance(\Webkul\Core\Core::class, $mockCore);

        try {
            $lightResolved = portal_logo('light');
            $darkResolved = portal_logo('dark');

            $this->assertStringContainsString('configuration/light-logo.png', $lightResolved);
            $this->assertStringContainsString('configuration/dark-logo.png', $darkResolved);

            $rendered = Blade::render('<x-portal::logo class="h-7 w-auto" />');

            $this->assertStringContainsString('portal-logo-light', $rendered);
            $this->assertStringContainsString('portal-logo-dark', $rendered);
            $this->assertStringContainsString('configuration/light-logo.png', $rendered);
            $this->assertStringContainsString('configuration/dark-logo.png', $rendered);
        } finally {
            app()->instance(\Webkul\Core\Core::class, $originalCore);
        }
    }

    public function test_logo_component_falls_back_to_light_logo_in_dark_mode_when_dark_logo_is_unset(): void
    {
        $originalCore = app(\Webkul\Core\Core::class);

        $mockCore = new class extends \Webkul\Core\Core {
            public function getConfigData(string $field)
            {
                return match ($field) {
                    'portal.general.design.logo_light' => 'configuration/only-light.png',
                    default => null,
                };
            }
        };

        app()->instance(\Webkul\Core\Core::class, $mockCore);

        try {
            $this->assertStringContainsString('configuration/only-light.png', portal_logo('light'));
            $this->assertStringContainsString('configuration/only-light.png', portal_logo('dark'));

            $rendered = Blade::render('<x-portal::logo class="h-7 w-auto" />');

            $this->assertStringNotContainsString('portal-logo-dark', $rendered);
            $this->assertStringContainsString('configuration/only-light.png', $rendered);
        } finally {
            app()->instance(\Webkul\Core\Core::class, $originalCore);
        }
    }

    public function test_system_config_registers_header_and_footer_settings_with_correct_scoping(): void
    {
        $coreConfig = collect(config('core'));

        $headerControls = collect($coreConfig->firstWhere('key', 'portal.header.controls')['fields'] ?? []);
        $headerAnnouncement = collect($coreConfig->firstWhere('key', 'portal.header.announcement')['fields'] ?? []);
        $headerCta = collect($coreConfig->firstWhere('key', 'portal.header.cta')['fields'] ?? []);

        $this->assertFalse($headerControls->firstWhere('name', 'sticky')['locale_based'] ?? true);
        $this->assertTrue($headerAnnouncement->firstWhere('name', 'text')['locale_based'] ?? false);
        $this->assertFalse($headerAnnouncement->firstWhere('name', 'link')['locale_based'] ?? true);
        $this->assertTrue($headerCta->firstWhere('name', 'text')['locale_based'] ?? false);
        $this->assertFalse($headerCta->firstWhere('name', 'url')['locale_based'] ?? true);

        $footerContact = collect($coreConfig->firstWhere('key', 'portal.footer.contact')['fields'] ?? []);
        $footerSocial = collect($coreConfig->firstWhere('key', 'portal.footer.social')['fields'] ?? []);
        $footerCopyright = collect($coreConfig->firstWhere('key', 'portal.footer.copyright')['fields'] ?? []);

        $this->assertFalse($footerContact->firstWhere('name', 'phone')['locale_based'] ?? true);
        $this->assertFalse($footerContact->firstWhere('name', 'email')['locale_based'] ?? true);
        $this->assertTrue($footerContact->firstWhere('name', 'bio')['locale_based'] ?? false);
        $this->assertTrue($footerContact->firstWhere('name', 'address')['locale_based'] ?? false);
        $this->assertFalse($footerSocial->firstWhere('name', 'twitter')['locale_based'] ?? true);
        $this->assertTrue($footerCopyright->firstWhere('name', 'text')['locale_based'] ?? false);
    }

    public function test_header_and_footer_settings_resolve_from_system_config(): void
    {
        $originalCore = app(\Webkul\Core\Core::class);

        $mockCore = new class extends \Webkul\Core\Core {
            public function getConfigData(string $field, ?string $channel = null, ?string $locale = null)
            {
                $locale = $locale ?? app()->getLocale();

                return match ("{$locale}:{$field}") {
                    'en:portal.header.controls.sticky' => 0,
                    'en:portal.header.cta.enabled' => 1,
                    'en:portal.header.cta.text' => 'Book Demo',
                    'en:portal.header.cta.url' => '/demo',
                    'en:portal.footer.contact.phone' => '+971500000000',
                    'en:portal.footer.social.twitter' => 'https://x.com/portal',
                    'ar:portal.footer.copyright.text' => 'جميع الحقوق محفوظة {year}',
                    default => null,
                };
            }
        };

        app()->instance(\Webkul\Core\Core::class, $mockCore);

        try {
            $header = portal_layout_settings('header', null, null, 'en');
            $this->assertSame(0, data_get($header, 'controls.sticky'));
            $this->assertSame(1, data_get($header, 'cta.enabled'));
            $this->assertSame('Book Demo', data_get($header, 'cta.text'));
            $this->assertSame('/demo', data_get($header, 'cta.url'));

            $footerAr = portal_layout_settings('footer', null, null, 'ar');
            $this->assertSame('+971500000000', data_get($footerAr, 'contact.phone'));
            $this->assertSame('https://x.com/portal', data_get($footerAr, 'social.platforms.twitter'));
            $this->assertSame('جميع الحقوق محفوظة {year}', data_get($footerAr, 'copyright.text'));
        } finally {
            app()->instance(\Webkul\Core\Core::class, $originalCore);
        }
    }

    public function test_system_config_registers_design_tokens_with_channel_scoping(): void
    {
        $coreConfig = collect(config('core'));

        foreach (['portal.tokens.brand', 'portal.tokens.light', 'portal.tokens.dark', 'portal.tokens.layout'] as $key) {
            $section = $coreConfig->firstWhere('key', $key);
            $this->assertNotNull($section, "Missing config section: {$key}");

            foreach ($section['fields'] ?? [] as $field) {
                $this->assertTrue($field['channel_based'] ?? false, "{$key}.{$field['name']} must be channel_based");
                $this->assertFalse($field['locale_based'] ?? true, "{$key}.{$field['name']} must not be locale_based");
            }
        }
    }

    public function test_portal_theme_css_compiles_system_tokens_and_invalidates_on_config_save(): void
    {
        $originalCore = app(\Webkul\Core\Core::class);

        $state = (object) ['primary' => '#2563eb'];

        $mockCore = new class($state) extends \Webkul\Core\Core {
            public function __construct(private object $state) {}

            public function getConfigData(string $field, ?string $channel = null, ?string $locale = null)
            {
                return match ($field) {
                    'portal.tokens.brand.brand_primary' => $this->state->primary,
                    'portal.tokens.light.header_bg' => '#ffffff',
                    'portal.tokens.dark.dark_header_bg' => '#0b0f19',
                    'portal.tokens.layout.btn_radius' => '0.75rem',
                    'portal.tokens.layout.custom_css' => '.custom-btn { opacity: 0.9; }<script>alert(1)</script>',
                    default => null,
                };
            }
        };

        app()->instance(\Webkul\Core\Core::class, $mockCore);
        \Platform\Portal\Services\ThemeManager::clearTokenCache();

        try {
            $css = portal_theme_css('portal', 1);

            $this->assertNotNull($css);
            $this->assertStringContainsString('--brand-primary: #2563eb;', $css);
            $this->assertStringContainsString('--header-bg: #ffffff;', $css);
            $this->assertStringContainsString('--btn-radius: 0.75rem;', $css);
            $this->assertStringContainsString('--header-bg: #0b0f19;', $css);
            $this->assertStringContainsString('.custom-btn { opacity: 0.9; }', $css);
            $this->assertStringNotContainsString('<script>', $css);

            // Mutate value & verify cache invalidation via core.configuration.save.after
            $state->primary = '#10b981';
            event('core.configuration.save.after');

            $updatedCss = portal_theme_css('portal', 1);
            $this->assertStringContainsString('--brand-primary: #10b981;', $updatedCss);
        } finally {
            app()->instance(\Webkul\Core\Core::class, $originalCore);
            \Platform\Portal\Services\ThemeManager::clearTokenCache();
        }
    }
}
