<?php

namespace Platform\PortalSectionsApi;

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
     * Create registry instance without hardcoded defaults.
     */
    public function __construct() {}

    /**
     * Register a new section type (by class name or instance).
     */
    public function register(string|SectionType $type): self
    {
        if (is_string($type)) {
            if (! is_subclass_of($type, SectionType::class)) {
                throw new \InvalidArgumentException("Class [{$type}] must extend ".SectionType::class);
            }

            $type = app($type);
        }

        $this->types[$type->getCode()] = $type;

        if (function_exists('app') && app()->bound('events')) {
            event('portal.section.registered', [$type]);
        }

        return $this;
    }

    /**
     * Unregister a section type by its code.
     */
    public function unregister(string $code): self
    {
        unset($this->types[$code]);

        return $this;
    }

    /**
     * Get all registered section type codes.
     *
     * @return array<int, string>
     */
    public function codes(): array
    {
        return array_keys($this->types);
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
