<?php

namespace Platform\Portal\Models;

use Illuminate\Database\Eloquent\Model;
use Platform\Portal\Contracts\PortalPageSectionTranslation as PortalPageSectionTranslationContract;

class PortalPageSectionTranslation extends Model implements PortalPageSectionTranslationContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'portal_page_section_translations';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'options',
        'draft_options',
        'locale',
        'portal_page_section_id',
    ];

    /**
     * Cast attributes.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
        'draft_options' => 'array',
    ];
}
