<?php

namespace Platform\Portal\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Platform\Portal\Contracts\PortalMenu as PortalMenuContract;
use Webkul\Core\Models\ChannelProxy;

class PortalMenu extends Model implements PortalMenuContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'portal_menus';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'status',
        'channel_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the channel associated with the menu.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChannelProxy::modelClass(), 'channel_id');
    }

    /**
     * Get all items belonging to the menu.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PortalMenuItemProxy::modelClass(), 'portal_menu_id')
            ->orderBy('sort_order', 'asc');
    }

    /**
     * Get top-level (root) items belonging to the menu.
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(PortalMenuItemProxy::modelClass(), 'portal_menu_id')
            ->whereNull('parent_id')
            ->orderBy('sort_order', 'asc');
    }

    /**
     * Scope a query to only include active menus.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include menus for a specific channel (or global).
     */
    public function scopeForChannel(Builder $query, ?int $channelId = null): Builder
    {
        return $query->where(function ($q) use ($channelId) {
            if ($channelId) {
                $q->where('channel_id', $channelId)
                    ->orWhereNull('channel_id');
            } else {
                $q->whereNull('channel_id');
            }
        });
    }

    /**
     * Scope a query to filter by slug.
     */
    public function scopeBySlug(Builder $query, string $slug): Builder
    {
        return $query->where('slug', $slug);
    }
}
