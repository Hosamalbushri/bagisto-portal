<?php

return [
    /*
     * Portal Authentication Configuration.
     *
     * Authentication in the portal is completely optional and non-blocking.
     * By default it is disabled so the platform functions purely as a CMS/content engine.
     */
    'auth' => [
        'enabled' => env('PORTAL_AUTH_ENABLED', false),
        'guard' => env('PORTAL_AUTH_GUARD', 'customer'),
    ],
];
