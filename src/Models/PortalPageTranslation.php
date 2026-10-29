<?php

namespace Platform\Portal\Models;

use Illuminate\Database\Eloquent\Model;
use Platform\Portal\Contracts\PortalPageTranslation as PortalPageTranslationContract;

class PortalPageTranslation extends Model implements PortalPageTranslationContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'portal_page_translations';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'locale',
        'portal_page_id',
    ];
}
