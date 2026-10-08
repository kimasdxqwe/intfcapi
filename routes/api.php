<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::group([
    'middleware' => [
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


