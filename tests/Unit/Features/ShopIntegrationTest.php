<?php

namespace Platform\Portal\Tests\Unit\Features;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Platform\Portal\Sections\CategoryCarouselSection;
use Platform\Portal\Sections\ProductCarouselSection;
use Platform\Portal\Sections\SectionTypeRegistry;
use Tests\TestCase;

class ShopIntegrationTest extends TestCase
{
    /**
     * Test portal_shop_enabled helper reflects configuration.
     */
    public function test_portal_shop_enabled_helper(): void
    {
        config(['portal.shop.enabled' => false]);
        $this->assertFalse(portal_shop_enabled());

        config(['portal.shop.enabled' => true]);
        // portal_shop_enabled() requires Shop or Product service provider class to be present
        $expected = class_exists(\Webkul\Shop\Providers\ShopServiceProvider::class)
            || class_exists(\Webkul\Product\Providers\ProductServiceProvider::class);
        $this->assertSame($expected, portal_shop_enabled());
    }

    /**
     * Test ProductCarouselSection structure and schema.
     */
    public function test_product_carousel_section_schema(): void
    {
        $section = new ProductCarouselSection;

        $this->assertEquals('product_carousel', $section->getCode());
        $this->assertNotEmpty($section->getTitle());
        $this->assertEquals('icon-product', $section->getIcon());

        $fields = $section->getFields();
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);

        $keys = collect($fields)->pluck('key')->all();
        $this->assertContains('title', $keys);
        $this->assertContains('filters', $keys);

        $defaults = $section->getDefaultOptions();
        $this->assertArrayHasKey('title', $defaults);
        $this->assertArrayHasKey('filters', $defaults);
    }

    /**
     * Test CategoryCarouselSection structure and schema.
     */
    public function test_category_carousel_section_schema(): void
    {
        $section = new CategoryCarouselSection;

        $this->assertEquals('category_carousel', $section->getCode());
        $this->assertNotEmpty($section->getTitle());
        $this->assertEquals('icon-folder', $section->getIcon());

        $fields = $section->getFields();
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);

        $keys = collect($fields)->pluck('key')->all();
        $this->assertContains('title', $keys);
        $this->assertContains('filters', $keys);

        $defaults = $section->getDefaultOptions();
        $this->assertArrayHasKey('title', $defaults);
        $this->assertArrayHasKey('filters', $defaults);
    }

    /**
     * Test registerShopSections registers both sections into registry.
     */
    public function test_register_shop_sections_into_registry(): void
    {
        $registry = new SectionTypeRegistry;

        $this->assertFalse($registry->has('product_carousel'));
        $this->assertFalse($registry->has('category_carousel'));

        $registry->registerShopSections();

        $this->assertTrue($registry->has('product_carousel'));
        $this->assertTrue($registry->has('category_carousel'));
        $this->assertInstanceOf(ProductCarouselSection::class, $registry->get('product_carousel'));
        $this->assertInstanceOf(CategoryCarouselSection::class, $registry->get('category_carousel'));
    }

    /**
     * Test header renders cleanly with shop mode disabled.
     */
    public function test_header_renders_without_shop_mode(): void
    {
        config(['portal.shop.enabled' => false]);

        $html = Blade::render('<x-portal::layouts.header />');

        $this->assertNotEmpty($html);
        $this->assertStringNotContainsString('Shopping Cart', $html);
    }
}
