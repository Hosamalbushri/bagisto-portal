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
}
