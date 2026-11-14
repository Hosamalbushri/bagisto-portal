<?php

namespace Platform\Portal\Sections;

use Platform\PortalSectionsApi\SectionTypeRegistry as BaseSectionTypeRegistry;

class SectionTypeRegistry extends BaseSectionTypeRegistry
{
    /**
     * Create registry and load defaults.
     */
    public function __construct()
    {
        parent::__construct();
        $this->registerDefaults();
    }

    /**
     * Register default built-in section types.
     */
    public function registerDefaults(): void
    {
        $this->register(new HeroScaffold);
        $this->register(new BentoGrid);
        $this->register(new HeroSlider);
        $this->register(new FeaturesGrid);
        $this->register(new FaqAccordion);
        $this->register(new CtaBanner);
        $this->register(new Testimonials);
        $this->register(new RichContent);
        $this->register(new ContactSection);

        if (function_exists('portal_shop_enabled') && portal_shop_enabled()) {
            $this->registerShopSections();
        }
    }

    /**
     * Register e-commerce shop sections when shop mode is enabled.
     */
    public function registerShopSections(): self
    {
        if (! $this->has('product_carousel')) {
            $this->register(new ProductCarouselSection);
        }

        if (! $this->has('category_carousel')) {
            $this->register(new CategoryCarouselSection);
        }

        return $this;
    }
}

