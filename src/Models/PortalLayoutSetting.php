<?php

namespace Platform\Portal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Platform\Portal\Contracts\PortalLayoutSetting as PortalLayoutSettingContract;
use Webkul\Core\Models\ChannelProxy;

class PortalLayoutSetting extends Model implements PortalLayoutSettingContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'portal_layout_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'channel_id',
        'theme_code',
        'locale_code',
        'group',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the associated channel.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChannelProxy::modelClass());
    }
}
