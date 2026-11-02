<?php

namespace Platform\Portal\Helpers;

use Webkul\Core\Repositories\CoreConfigRepository;

class PortalStorefront
{
    /**
     * In-memory cache for current request lifecycle.
     */
    protected static array $memo = [];

    /**
     * Testing override for storefront status.
     */
    protected static ?bool $override = null;

    /**
     * Set testing override.
     */
    public static function setOverride(?bool $status): void
    {
        self::$override = $status;
    }

    /**
     * Clear runtime cache and override.
     */
    public static function clearMemo(): void
    {
        self::$memo = [];
        self::$override = null;
    }

    /**
     * Check if the storefront is enabled for the current channel.
     *
     * Evaluates channel database configuration first, then portal.php / env.
     */
    public static function isEnabled(): bool
    {
        if (self::$override !== null) {
            return self::$override;
        }

        $channelCode = core()->getCurrentChannel()?->code ?? 'default';

        if (array_key_exists($channelCode, self::$memo)) {
            return self::$memo[$channelCode];
        }

        $savedRecord = self::getSavedConfig('portal.general.storefront.enabled');

        if ($savedRecord !== null) {
            return self::$memo[$channelCode] = (bool) $savedRecord->value;
        }

        return self::$memo[$channelCode] = (bool) config('portal.storefront.enabled', true);
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
