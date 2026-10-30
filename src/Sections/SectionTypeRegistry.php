<?php

namespace Platform\Portal\Sections;

use Illuminate\Support\Collection;

class SectionTypeRegistry
{
    /**
     * Registered section types.
     *
     * @var array<string, SectionType>
     */
    protected array $types = [];

    /**
     * Create registry and load defaults.
     */
    public function __construct()
    {
        $this->registerDefaults();
    }

    /**
     * Register default built-in section types.
     */
    protected function registerDefaults(): void
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

    /**
     * Register a new section type.
     */
    public function register(SectionType $type): self
    {
        $this->types[$type->getCode()] = $type;

        return $this;
    }

    /**
     * Get all registered section types.
     *
     * @return Collection<string, SectionType>
     */
    public function all(): Collection
    {
        return collect($this->types);
    }

    /**
     * Get a section type by its code.
     */
    public function get(string $code): ?SectionType
    {
        return $this->types[$code] ?? null;
    }

    /**
     * Check if a section type exists.
     */
    public function has(string $code): bool
    {
        return isset($this->types[$code]);
    }
}
