<?php

namespace Platform\Portal\Helpers;

use Webkul\Core\Repositories\CoreConfigRepository;

class PortalStorefront
{
    /**
     * Check if the storefront is enabled for the current channel.
     *
     * Evaluates channel database configuration first, then portal.php / env.
     */
    public static function isEnabled(): bool
    {
        $savedRecord = self::getSavedConfig('portal.general.storefront.enabled');

        if ($savedRecord !== null) {
            return (bool) $savedRecord->value;
        }

        return (bool) config('portal.storefront.enabled', true);
    }

    /**
     * Retrieve explicitly persisted config from core_config table if present.
     */
    protected static function getSavedConfig(string $code): mixed
    {
        try {
            $channel = core()->getCurrentChannel();

            $query = ['code' => $code];

            if ($channel) {
                $query['channel_code'] = $channel->code;
            }

            return app(CoreConfigRepository::class)->findOneWhere($query);
        } catch (\Throwable) {
            return null;
        }
    }
}
