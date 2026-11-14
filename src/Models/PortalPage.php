<?php

namespace Platform\Portal\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Platform\Portal\Contracts\PortalPage as PortalPageContract;
use Platform\Portal\Services\Tokens\TokenSanitizer;
use Webkul\Core\Eloquent\TranslatableModel;
use Webkul\Core\Models\ChannelProxy;

class PortalPage extends TranslatableModel implements PortalPageContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'portal_pages';

    /**
     * Translation model foreign key column.
     *
     * @var string
     */
    protected $translationForeignKey = 'portal_page_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'template',
        'theme_code',
        'tokens',
        'status',
        'is_home_page',
        'sort_order',
        'channel_id',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatedAttributes = [
        'title',
        'meta_title',
        'meta_description',
        'meta_keywords',
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
        'is_home_page' => 'boolean',
        'sort_order' => 'integer',
        'tokens' => 'array',
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
            $this->attributes['tokens'] = empty($normalized) ? null : json_encode($normalized);
        }
    }

    /**
     * Get the channel that owns the page.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChannelProxy::modelClass(), 'channel_id');
    }

    /**
     * Get the sections for the page.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(PortalPageSectionProxy::modelClass(), 'portal_page_id')
            ->orderBy('sort_order');
    }
}
