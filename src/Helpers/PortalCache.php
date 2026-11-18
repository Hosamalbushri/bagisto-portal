<?php

namespace Platform\Portal\Helpers;

use Closure;
use Illuminate\Support\Facades\Cache;

class PortalCache
{
    /**
     * Cache key that stores the current portal content version.
     */
    public const VERSION_KEY = 'portal_api_version';

    /**
     * Lifetime (in seconds) of the portal version counter.
     */
    public const VERSION_TTL = 31536000;

    /**
     * Time (in seconds) a cached portal API response is kept.
     */
    public const TTL = 3600;

    /**
     * Current portal content version, which increments whenever pages, menus or tokens change.
     */
    public function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    /**
     * Bump the portal version so every cached response is invalidated atomically.
     */
    public function flush(): void
    {
        Cache::add(self::VERSION_KEY, 1, self::VERSION_TTL);

        Cache::increment(self::VERSION_KEY);
    }

    /**
     * Whether a response may be cached.
     */
    public function shouldCache(): bool
    {
        // Don't cache in debug mode or if explicitly disabled
        return (bool) config('portal.cache.enabled', true);
    }

    /**
     * Resolve a portal response, caching it using version-scoped keys.
     */
    public function remember(string $segment, array $params, Closure $callback): mixed
    {
        if (! $this->shouldCache()) {
            return $callback();
        }

        return Cache::remember($this->key($segment, $params), self::TTL, $callback);
    }

    /**
     * Build a version-aware cache key scoped to channel, theme, and locale.
     */
    public function key(string $segment, array $params = []): string
    {
        $channelId = 'default';
        $theme = 'default';
        $locale = app()->getLocale();

        if (function_exists('core')) {
            try {
                $channel = core()->getCurrentChannel();
                if ($channel) {
                    $channelId = $channel->id ?? $channel->code ?? 'default';
                    $theme = $channel->theme ?? 'default';
                }
                $currentLocale = core()->getCurrentLocale();
                if ($currentLocale) {
                    $locale = $currentLocale->code ?? $locale;
                }
            } catch (\Throwable) {
                // Graceful fallback to default values
            }
        }

        return implode(':', [
            'portal_api',
            $this->version(),
            $segment,
            $channelId,
            $theme,
            $locale,
            md5(json_encode($params)),
        ]);
    }
}
