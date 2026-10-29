<?php

namespace Platform\Portal\Providers;

use Konekt\Concord\BaseModuleServiceProvider;
use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageSection;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        PortalPage::class,
        PortalPageSection::class,
    ];
}
