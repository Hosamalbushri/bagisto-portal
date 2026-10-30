<?php

namespace Platform\Portal\Models;

use Illuminate\Database\Eloquent\Model;
use Platform\Portal\Contracts\PortalMenuItemTranslation as PortalMenuItemTranslationContract;

class PortalMenuItemTranslation extends Model implements PortalMenuItemTranslationContract
{
    /**
     * Disable timestamps.
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'portal_menu_item_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'locale',
        'title',
        'portal_menu_item_id',
    ];
}
