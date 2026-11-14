<?php

namespace Platform\PortalSectionsApi\Tests;

use PHPUnit\Framework\TestCase;
use Platform\PortalSectionsApi\SectionType;
use Platform\PortalSectionsApi\SectionTypeRegistry;

class SectionTypeRegistryTest extends TestCase
{
    protected SectionTypeRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new SectionTypeRegistry;
    }

    public function test_fresh_registry_starts_empty(): void
    {
        $this->assertEmpty($this->registry->all());
        $this->assertEmpty($this->registry->codes());
    }

    public function test_register_and_retrieve_section_instance(): void
    {
        $dummy = new class extends SectionType
        {
            protected string $code = 'banner';
            protected ?string $title = 'Promotional Banner';
            protected string $icon = 'icon-banner';

            public function getFields(): array
            {
                return ['title' => ['type' => 'text']];
            }

            public function getDefaultOptions(): array
            {
                return ['title' => 'Sale'];
            }
        };

        $this->registry->register($dummy);

        $this->assertTrue($this->registry->has('banner'));
        $this->assertEquals(['banner'], $this->registry->codes());

        $retrieved = $this->registry->get('banner');
        $this->assertNotNull($retrieved);
        $this->assertEquals('banner', $retrieved->getCode());
        $this->assertEquals('Promotional Banner', $retrieved->getTitle());
        $this->assertEquals('icon-banner', $retrieved->getIcon());
        $this->assertEquals(['title' => 'Sale'], $retrieved->getDefaultOptions());

        $serialized = $retrieved->toArray();
        $this->assertEquals('banner', $serialized['code']);
        $this->assertEquals('Promotional Banner', $serialized['title']);
        $this->assertEquals('icon-banner', $serialized['icon']);
        $this->assertFalse($serialized['is_singleton']);
        $this->assertFalse($serialized['is_pinned']);
    }

    public function test_duplicate_registration_overwrites_cleanly(): void
    {
        $v1 = new class extends SectionType
        {
            protected string $code = 'slider';
            protected ?string $title = 'Version 1';
            public function getFields(): array { return []; }
            public function getDefaultOptions(): array { return []; }
        };

        $v2 = new class extends SectionType
        {
            protected string $code = 'slider';
            protected ?string $title = 'Version 2';
            public function getFields(): array { return []; }
            public function getDefaultOptions(): array { return []; }
        };

        $this->registry->register($v1);
        $this->assertEquals('Version 1', $this->registry->get('slider')->getTitle());

        $this->registry->register($v2);
        $this->assertEquals('Version 2', $this->registry->get('slider')->getTitle());
        $this->assertCount(1, $this->registry->all());
    }

    public function test_unregister_removes_section(): void
    {
        $section = new class extends SectionType
        {
            protected string $code = 'faq';
            public function getFields(): array { return []; }
            public function getDefaultOptions(): array { return []; }
        };

        $this->registry->register($section);
        $this->assertTrue($this->registry->has('faq'));

        $this->registry->unregister('faq');
        $this->assertFalse($this->registry->has('faq'));
        $this->assertNull($this->registry->get('faq'));
    }

    public function test_invalid_class_registration_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->registry->register(\stdClass::class);
    }
}
