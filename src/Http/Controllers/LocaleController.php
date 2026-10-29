<?php

namespace Platform\Portal\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    /**
     * Switch current storefront locale in session.
     */
    public function switch(string $code): RedirectResponse
    {
        $channel = core()->getCurrentChannel();

        $locales = $channel ? $channel->locales->pluck('code')->toArray() : [];

        if (in_array($code, $locales)) {
            session()->put('locale', $code);
            app()->setLocale($code);
        }

        return redirect()->back();
    }
}
