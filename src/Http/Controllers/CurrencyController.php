<?php

namespace Platform\Portal\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class CurrencyController extends Controller
{
    /**
     * Switch current storefront currency in session.
     */
    public function switch(string $code): RedirectResponse
    {
        $channel = core()->getCurrentChannel();

        $currencies = $channel ? $channel->currencies->pluck('code')->toArray() : [];

        if (in_array($code, $currencies)) {
            core()->setCurrentCurrency($code);
            session()->put('currency', $code);
        }

        return redirect()->back();
    }
}
