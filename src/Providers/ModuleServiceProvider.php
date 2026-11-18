<?php

namespace Platform\Portal\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;
use Platform\Portal\Models\PortalLayoutSetting;
use Platform\Portal\Models\PortalMenu;
use Platform\Portal\Models\PortalMenuItem;
use Platform\Portal\Models\PortalMenuItemTranslation;
use Platform\Portal\Models\PortalMenuSlotAssignment;
use Platform\Portal\Models\PortalPage;
use Platform\Portal\Models\PortalPageSection;
use Platform\Portal\Models\PortalPageSectionTranslation;
use Platform\Portal\Models\PortalPageTranslation;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        PortalPage::class,
        PortalPageTranslation::class,
        PortalPageSection::class,
        PortalPageSectionTranslation::class,
        PortalMenu::class,
        PortalMenuItem::class,
        PortalMenuItemTranslation::class,
        PortalMenuSlotAssignment::class,
        PortalLayoutSetting::class,
    ];
}
