<?php

namespace Platform\Portal\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Platform\Portal\Contracts\PortalPageSection as PortalPageSectionContract;
use Platform\Portal\Sections\SectionType;
use Platform\Portal\Sections\SectionTypeRegistry;
use Platform\Portal\Services\Tokens\TokenSanitizer;
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
        if (is_null($value)) {
            $this->attributes['tokens'] = null;

            return;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return;
            }
            $value = $decoded;
        }

        if (is_array($value)) {
            $sanitizer = function_exists('app') && app()->bound(TokenSanitizer::class)
                ? app(TokenSanitizer::class)
                : null;

            $normalized = $sanitizer ? $sanitizer->normalizeTokens($value) : $value;
            $this->attributes['tokens'] = json_encode($normalized);
        }
    }

    /**
     * Set the draft_tokens attribute with normalization.
     */
    public function setDraftTokensAttribute($value): void
    {
        if (is_null($value)) {
            $this->attributes['draft_tokens'] = null;

            return;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return;
            }
            $value = $decoded;
        }

        if (is_array($value)) {
            $sanitizer = function_exists('app') && app()->bound(TokenSanitizer::class)
                ? app(TokenSanitizer::class)
                : null;

            $normalized = $sanitizer ? $sanitizer->normalizeTokens($value) : $value;
            $this->attributes['draft_tokens'] = json_encode($normalized);
        }
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
