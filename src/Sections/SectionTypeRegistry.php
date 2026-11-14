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
    }
}

