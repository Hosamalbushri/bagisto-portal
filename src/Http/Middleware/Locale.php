<?php

namespace Platform\Portal\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Webkul\Core\Repositories\LocaleRepository;

class Locale
{
    /**
     * Create a middleware instance.
     */
    public function __construct(protected LocaleRepository $localeRepository) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $channel = core()->getCurrentChannel();

        $locales = $channel ? $channel->locales->pluck('code')->toArray() : [];
        $localeCode = core()->getRequestedLocaleCode('locale', false);

        if (! $localeCode || ! in_array($localeCode, $locales)) {
            $localeCode = session()->get('locale');
        }

        if (! $localeCode || ! in_array($localeCode, $locales)) {
            $localeCode = $channel?->default_locale?->code ?? config('app.locale');
        }

        app()->setLocale($localeCode);
        session()->put('locale', $localeCode);
        unset($request['locale']);

        return $next($request);
    }
}
