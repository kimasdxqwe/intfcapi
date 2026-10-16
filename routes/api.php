<?php

use App\Http\Controllers\OAuthContextController;
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
            'scope:wms:interfacing'
        ]
    ], function(){

        Route::get('/oauth-context', OAuthContextController::class)->name('oauth-context');
    });
});


