<?php

namespace Platform\Portal\Tests\Unit\Security;

use Illuminate\Http\Request;
use Platform\Portal\Helpers\PortalStorefront;
use Platform\Portal\Http\Middleware\CheckStorefrontStatus;
use Tests\TestCase;

class CheckStorefrontStatusTest extends TestCase
{
    /**
     * Unauthenticated visitor passing ?preview=1 must NOT bypass disabled storefront check.
     */
    public function test_unauthenticated_visitor_cannot_bypass_disabled_storefront_via_preview_query(): void
    {
        PortalStorefront::setOverride(false);

        try {
            $middleware = new CheckStorefrontStatus;

            $request = Request::create('/?preview=1', 'GET');

            $response = $middleware->handle($request, function () {
                return response('Storefront Access Granted', 200);
            });

            // Must be redirected to admin login, NOT 200 OK
            $this->assertTrue($response->isRedirection(), 'Unauthenticated user with ?preview=1 must be redirected.');
        } finally {
            PortalStorefront::clearMemo();
        }
    }
}
