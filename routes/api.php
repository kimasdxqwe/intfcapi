<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::group([
    'middleware' => [
//        EnsureClientIsResourceOwner::class
//        'ensure_client_is_resource_owner',
        'ensure_user_or_client'
    ],
    'prefix' => 'v1'
], function(){

    Route::group([
        'middleware' => [
            'scope:wms:xread,wms:zread,wms:interfacing'
        ]
    ], function(){

        // Access token is valid and the client is resource owner...
        Route::get('/client-auth', [\App\Http\Controllers\DebugController::class, 'index'])
            ->name('client-auth');
            //->middleware('scopes:wms:xread,wms:zread');
    });
});


