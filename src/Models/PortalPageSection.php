<?php

namespace Platform\Portal\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Platform\Portal\Contracts\PortalPageSection as PortalPageSectionContract;
use Platform\Portal\Sections\SectionType;
use Platform\Portal\Sections\SectionTypeRegistry;
use Webkul\Core\Eloquent\TranslatableModel;

class PortalPageSection extends TranslatableModel implements PortalPageSectionContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'portal_page_sections';

    /**
     * Translation model foreign key column.
     *
     * @var string
     */
    protected $translationForeignKey = 'portal_page_section_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'name',
        'sort_order',
        'status',
        'draft_status',
        'draft_sort_order',
        'portal_page_id',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatedAttributes = [
        'options',
        'draft_options',
    ];

    /**
     * With the translations given attributes.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * Cast attributes.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'draft_status' => 'boolean',
        'sort_order' => 'integer',
        'draft_sort_order' => 'integer',
        'options' => 'array',
        'draft_options' => 'array',
    ];

    /**
     * Get the page that owns the section.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(PortalPage::class, 'portal_page_id');
    }

    /**
     * Get the section type instance.
     */
    public function getTypeInstance(): ?SectionType
    {
        return app(SectionTypeRegistry::class)->get($this->type);
    }
}
