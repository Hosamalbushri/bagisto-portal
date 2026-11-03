<?php

namespace Platform\Portal\Tests\Unit\Sections;

use Platform\Portal\Facades\PortalSection;
use Platform\Portal\Sections\SectionType;
use Platform\Portal\Sections\SectionTypeRegistry;
use Tests\TestCase;

class SectionTypeRegistryTest extends TestCase
{
    /**
     * Verify that default section types are properly registered in SectionTypeRegistry.
     */
    public function test_default_section_types_are_registered(): void
    {
        $registry = app(SectionTypeRegistry::class);

        $this->assertNotEmpty($registry->all(), 'Section registry must contain registered section types.');

        // Common default sections
        $expectedSections = ['hero_slider', 'features_grid', 'testimonials', 'rich_content', 'contact_section'];

        foreach ($expectedSections as $key) {
            $section = $registry->get($key);
            $this->assertNotNull($section, "Section type {$key} should be registered in the registry.");
            $this->assertEquals($key, $section->getCode());
            $this->assertNotEmpty($section->getTitle());
            $this->assertIsArray($section->getFields());
            $this->assertIsArray($section->getDefaultOptions());
        }
    }

    /**
     * Verify that external developers can register a custom section via class name or instance.
     */
    public function test_custom_section_registration_via_facade(): void
    {
        $dummyClass = new class extends SectionType
        {
            protected string $code = 'pricing_custom';

            protected ?string $title = 'Custom Pricing';

            protected ?string $view = 'sections.custom-pricing';

            public function getFields(): array
            {
                return [];
            }

            public function getDefaultOptions(): array
            {
                return ['price' => 99];
            }
        };

        // 1. Register instance
        PortalSection::register($dummyClass);

        $this->assertTrue(PortalSection::has('pricing_custom'));
        $retrieved = PortalSection::get('pricing_custom');
        $this->assertNotNull($retrieved);
        $this->assertEquals('pricing_custom', $retrieved->getCode());
        $this->assertEquals('Custom Pricing', $retrieved->getTitle());
        $this->assertEquals('sections.custom-pricing', $retrieved->getView());

        // 2. Unregister
        PortalSection::unregister('pricing_custom');
        $this->assertFalse(PortalSection::has('pricing_custom'));

        // 3. Registering invalid class throws InvalidArgumentException
        $this->expectException(\InvalidArgumentException::class);
        PortalSection::register(\stdClass::class);
    }
}
