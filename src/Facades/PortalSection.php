<?php

namespace Platform\Portal\Facades;

use Illuminate\Support\Facades\Facade;
use Platform\Portal\Sections\SectionTypeRegistry;

/**
 * @method static \Platform\Portal\Sections\SectionTypeRegistry register(string|\Platform\Portal\Sections\SectionType $type)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Platform\Portal\Sections\SectionType|null get(string $code)
 * @method static bool has(string $code)
 * @method static array codes()
 * @method static \Platform\Portal\Sections\SectionTypeRegistry unregister(string $code)
 *
 * @see SectionTypeRegistry
 */
class PortalSection extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return SectionTypeRegistry::class;
    }
}
