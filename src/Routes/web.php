<?php

use Illuminate\Support\Facades\Route;
use Webkul\Core\Http\Middleware\PreventRequestsDuringMaintenance;

$prefix = config('portal.routing.mode') === 'prefix' ? config('portal.routing.prefix', 'portal') : '';

Route::middleware(['web', 'portal', PreventRequestsDuringMaintenance::class])
    ->prefix($prefix)
    ->group(__DIR__.'/store-front-routes.php');

require __DIR__.'/admin-routes.php';
