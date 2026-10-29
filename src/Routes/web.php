<?php

use Illuminate\Support\Facades\Route;
use Webkul\Core\Http\Middleware\PreventRequestsDuringMaintenance;

Route::middleware(['web', 'portal', PreventRequestsDuringMaintenance::class])->group(__DIR__.'/store-front-routes.php');

require __DIR__.'/admin-routes.php';
