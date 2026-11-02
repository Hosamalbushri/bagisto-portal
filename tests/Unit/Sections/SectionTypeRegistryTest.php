<?php

namespace Platform\Portal\Tests\Unit\Sections;

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
}
