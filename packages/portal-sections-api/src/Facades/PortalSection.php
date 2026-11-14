<?php

namespace Platform\PortalSectionsApi\Facades;

use Illuminate\Support\Facades\Facade;
use Platform\PortalSectionsApi\SectionTypeRegistry;

/**
 * @method static \Platform\PortalSectionsApi\SectionTypeRegistry register(string|\Platform\PortalSectionsApi\SectionType $type)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Platform\PortalSectionsApi\SectionType|null get(string $code)
 * @method static bool has(string $code)
 * @method static array codes()
 * @method static \Platform\PortalSectionsApi\SectionTypeRegistry unregister(string $code)
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
