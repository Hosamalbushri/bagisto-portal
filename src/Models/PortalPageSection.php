<?php

namespace Platform\Portal\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Platform\Portal\Contracts\PortalPageSection as PortalPageSectionContract;
use Platform\Portal\Models\Traits\HasTokensAttribute;
use Platform\Portal\Sections\SectionType;
use Platform\Portal\Sections\SectionTypeRegistry;
use Platform\Portal\Services\Tokens\TokenSanitizer;
use Webkul\Core\Eloquent\TranslatableModel;

class PortalPageSection extends TranslatableModel implements PortalPageSectionContract
{
    use HasTokensAttribute;
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
        'tokens',
        'draft_tokens',
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
     * Whether to use translation fallback.
     *
     * @var bool
     */
    protected $useTranslationFallback = true;

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
        'tokens' => 'array',
        'draft_tokens' => 'array',
    ];

    /**
     * Set the tokens attribute with normalization.
     */
    public function setTokensAttribute($value): void
    {
        $this->mutateTokenAttribute('tokens', $value, emptyAsNull: false);
    }

    /**
     * Set the draft_tokens attribute with normalization.
     */
    public function setDraftTokensAttribute($value): void
    {
        $this->mutateTokenAttribute('draft_tokens', $value, emptyAsNull: false);
    }

    /**
     * Get the page that owns the section.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(PortalPageProxy::modelClass(), 'portal_page_id');
    }

    /**
     * Get the section type instance.
     */
    public function getTypeInstance(): ?SectionType
    {
        return app(SectionTypeRegistry::class)->get($this->type);
    }
}
