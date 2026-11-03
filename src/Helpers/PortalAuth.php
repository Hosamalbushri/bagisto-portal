<?php

namespace Platform\Portal\Helpers;

use Webkul\Core\Repositories\CoreConfigRepository;

class PortalAuth
{
    /**
     * In-memory cache for current request lifecycle.
     */
    protected static array $memo = [];

    /**
     * Check if authentication is enabled for the portal.
     *
     * Evaluates channel-based admin configuration if saved in DB, or portal.php / env.
     * Validates that the targeted guard is actually registered in Laravel.
     */
    public static function isEnabled(): bool
    {
        $channelCode = core()->getCurrentChannel()?->code ?? 'default';
        $memoKey = "enabled_{$channelCode}";

        if (array_key_exists($memoKey, self::$memo)) {
            return self::$memo[$memoKey];
        }

        // 1. Check if configured in DB for current channel
        $savedRecord = self::getSavedConfig('portal.auth.settings.enabled');

        if ($savedRecord !== null) {
            if (! (bool) $savedRecord->value) {
                return self::$memo[$memoKey] = false;
            }

            $guard = self::guard();

            return self::$memo[$memoKey] = (bool) config()->has("auth.guards.{$guard}");
        }

        // 2. Fall back to portal.php / env
        if (config('portal.auth.enabled')) {
            $guard = self::guard();

            return self::$memo[$memoKey] = (bool) config()->has("auth.guards.{$guard}");
        }

        return self::$memo[$memoKey] = false;
    }

    /**
     * Get the active authentication guard for the portal.
     */
    public static function guard(): string
    {
        $channelCode = core()->getCurrentChannel()?->code ?? 'default';
        $memoKey = "guard_{$channelCode}";

        if (array_key_exists($memoKey, self::$memo)) {
            return self::$memo[$memoKey];
        }

        // 1. Check if configured in DB
        $savedRecord = self::getSavedConfig('portal.auth.settings.guard');

        if ($savedRecord && ! empty($savedRecord->value) && config()->has("auth.guards.{$savedRecord->value}")) {
            return self::$memo[$memoKey] = $savedRecord->value;
        }

        // 2. Check portal.php / env
        $configGuard = config('portal.auth.guard', 'web');

        if (! empty($configGuard) && config()->has("auth.guards.{$configGuard}")) {
            return self::$memo[$memoKey] = $configGuard;
        }

        return self::$memo[$memoKey] = config('auth.defaults.guard', 'web');
    }

    /**
     * Get the currently authenticated portal user, if any.
     */
    public static function user(): mixed
    {
        if (! self::isEnabled()) {
            return null;
        }

        $guard = self::guard();

        return auth()->guard($guard)->user();
    }

    /**
     * Check if a portal user is currently authenticated.
     */
    public static function check(): bool
    {
        if (! self::isEnabled()) {
            return false;
        }

        $guard = self::guard();

        return auth()->guard($guard)->check();
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
