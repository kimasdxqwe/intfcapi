<?php

use App\Http\Controllers\IntrospectionController;
use Illuminate\Support\Facades\Route;

Route::post('/oauth/introspect', IntrospectionController::class)
    ->middleware('throttle:3,1');
