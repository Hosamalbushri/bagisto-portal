<?php

namespace Platform\Portal\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Webkul\Core\Repositories\CurrencyRepository;

class Currency
{
    /**
     * Create a middleware instance.
     */
    public function __construct(protected CurrencyRepository $currencyRepository) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $channel = core()->getCurrentChannel();

        $currencies = $channel ? $channel->currencies->pluck('code')->toArray() : [];
        $currencyCode = core()->getRequestedLocaleCode('currency', false);

        if (! $currencyCode || ! in_array($currencyCode, $currencies)) {
            $currencyCode = session()->get('currency');
        }

        if (! $currencyCode || ! in_array($currencyCode, $currencies)) {
            $currencyCode = $channel?->base_currency?->code ?? config('app.currency');
        }

        if ($currencyCode) {
            core()->setCurrentCurrency($currencyCode);
            session()->put('currency', $currencyCode);
        }

        unset($request['currency']);

        return $next($request);
    }
}
