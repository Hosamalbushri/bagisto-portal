<?php

namespace Platform\Portal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Platform\Portal\Contracts\PortalMenuSlotAssignment as PortalMenuSlotAssignmentContract;
use Webkul\Core\Models\ChannelProxy;

class PortalMenuSlotAssignment extends Model implements PortalMenuSlotAssignmentContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'portal_menu_slot_assignments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'slot_id',
        'location',
        'portal_menu_id',
        'channel_id',
        'theme_code',
        'title',
        'options',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'status' => 'boolean',
    ];

    /**
     * Get the associated portal menu.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(PortalMenuProxy::modelClass(), 'portal_menu_id');
    }

    /**
     * Get the channel.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChannelProxy::modelClass(), 'channel_id');
    }
}
